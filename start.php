<?php
//           DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
//                   Version 2, December 2004
//
// Copyright (C) 2022 Matthias Gatto <uso.cosmo.ray@gmail.com>
//
// Everyone is permitted to copy and distribute verbatim or modified
// copies of this license document, and changing it is allowed as long
// as the name is changed.
//
//            DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
//   TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION
//
//  0. You just DO WHAT THE FUCK YOU WANT TO.

$MAP_W = 128;
$MAP_H = 128;

$MAX_ROOM = 16; // $MAP_W / 8;

$TOT_ROOM = 8 * 8;

function action($cwid, $eves) {
    $mwid = $cwid;

    echo "ACTION !" . PHP_EOL;
    if (yevIsKeyDown($eves, $Y_ESC_KEY) || yevIsKeyDown($eves, $Y_Q_KEY)) {
        if (yeGet($cwid, "quit"))
            yesCall(yeGet($cwid, "quit")); // Untested
        else
            yesCall(ygGet('FinishGame'));
    }

    if (yevIsKeyDown($eves, $Y_LEFT_KEY)) {
        ywMapCamAddX($mwid, -1);
    } else if (yevIsKeyDown($eves, $Y_RIGHT_KEY)) {
        ywMapCamAddX($mwid, +1);
    }

    if (yevIsKeyDown($eves, $Y_UP_KEY)) {
        ywMapCamAddY($mwid, -1);
    } else if (yevIsKeyDown($eves, $Y_DOWN_KEY)) {
        ywMapCamAddY($mwid, +1);
    }

}

function draw_room($mwid, $room, $nb) {
    $max_room = $GLOBALS['MAX_ROOM'];
    $x = $max_room * ($nb & 7) + $max_room / 2;
    $y = $max_room * floor($nb / 8) + $max_room / 2;
    $w = ywSizeW($room);
    $h = ywSizeH($room);

    echo 'draw :x ', $x, ' - y: ',$y, ' w: ' ,
        $w, ' h: ', $h, ' | ',$nb, PHP_EOL;

    // Walls
    // up
    $start = ywPosCreate(floor($x - $w / 2), floor($y - $h / 2));
    $end = ywPosCreate(floor($x + $w / 2), floor($y - $h / 2));
    ywMapDrawSegment($mwid, $start, $end, yeCreateInt(1), 0);
    // right
    $start = ywPosCreate(floor($x + $w / 2), floor($y + $h / 2));
    ywMapDrawSegment($mwid, $start, $end, yeCreateInt(1), 0);
    // bottom
    $start = ywPosCreate(floor($x - $w / 2), floor($y + $h / 2));
    $end = ywPosCreate(floor($x + $w / 2), floor($y + $h / 2));
    ywMapDrawSegment($mwid, $start, $end, yeCreateInt(1), 0);
    // left
    $start = ywPosCreate(floor($x - $w / 2), floor($y - $h / 2));
    $end = ywPosCreate(floor($x - $w / 2), floor($y + $h / 2));
    ywMapDrawSegment($mwid, $start, $end, yeCreateInt(1), 0); 
}

function mk_corridor($mwid, $rooms, $i)
{
    $room = yeGet($rooms, $i);
    $target = $i;
    $x_target = 0;
    $y_target = 0;
    $x = $i & 7;
    $y = floor($i / 8);
    $max_room = $GLOBALS['MAX_ROOM'];
    $start_x = $x * $max_room + $max_room / 2;
    $start_y = $y * $max_room + $max_room / 2;
    $id = null;

    again:
    $r = yuiRand() & 3;
    if ($r == 0) {
        if ($x == 7)
            goto again;
        $target += 1;
        $x_target = -1;
        $start_x += ywSizeW($room) / 2;
        $id = yeCreateInt(2);
    } else if ($r == 1) {
        if ($x == 0)
            goto again;
        $target -= 1;
        $x_target = +1;
        $start_x -= ywSizeW($room) / 2;
        $id = yeCreateInt(2);
    } else if ($r == 2) {
        if ($y == 7)
            goto again;
        $target += 8;
        $y_target = -1;
        $start_y += ywSizeH($room) / 2;
        $id = yeCreateInt(3);
    } else {
        if ($y == 0)
            goto again;
        $target -= 8;
        $y_target = +1;
        $start_y -= ywSizeH($room) / 2;
        $id = yeCreateInt(3);
    }

    $targeted_room = yeGet($rooms, $target);
    $end_x = $target & 7; 
    $end_x = $end_x * $max_room + $max_room / 2 + (ywSizeW($targeted_room) * $x_target) / 2;

    $end_y = floor($target / 8); 
    $end_y = $end_y * $max_room + $max_room / 2 + (ywSizeH($targeted_room) * $y_target) / 2;
    ywMapDrawSegment($mwid, ywPosCreate($start_x, $start_y),
                     ywPosCreate($end_x, $end_y), $id, 0);
    echo 'corridor: ', $start_x, ' - ', $start_y, ' - ', $target, PHP_EOL;
}

function init_map($mwid) {
    $rooms = yeReCreateArray($mwid, "rooms");
    $tot_rooms = $GLOBALS["TOT_ROOM"];
    for ($i = 0; $i < $tot_rooms - i; $i++) {
        $rand_w = yuiRand() & ($GLOBALS["MAX_ROOM"] - 1);
        $rand_h = yuiRand() & ($GLOBALS["MAX_ROOM"] - 1);
        if ($rand_w < 4)
            $rand_w = 4;
        if ($rand_h < 4)
            $rand_h = 4;
        $r = ywSizeCreate($rand_w, $rand_h, $rooms);
        yeCreateInt(4, $r, 'exits_close');
        draw_room($mwid, $r, $i);
        //echo 'do my sheet: ', $rand_w," - " , $rand_h, " " , $GLOBALS["MAX_ROOM"], PHP_EOL;
    }

    for ($i = 0; $i < $tot_rooms - i; $i++) {
        mk_corridor($mwid, $rooms, $i);
    }
    //yePrint($mwid);
    // yePrint($rooms);
}

function init_wid($cwid) { 
    $resources = yeCreateArray();
 // if I want to create a container, having mwid and cwid diferent will be usefull
    $mwid = $cwid;

    $el = yeCreateArray($resources);
    yeCreateString(".", $el, "map-char"); // 0

    $el = yeCreateArray($resources);
    yeCreateString("#", $el, "map-char"); // 1

    $el = yeCreateArray($resources);
    yeCreateString("=", $el, "map-char"); // 2
    $el = yeCreateArray($resources);
    yeCreateString("H", $el, "map-char"); // 3

    
    $el = yeCreateArray($resources);
    yeCreateString("@", $el, "map-char"); // 4

    ywMapInitEntity($mwid, $resources, 0, $GLOBALS['MAP_W'],
                    $GLOBALS['MAP_H']);
    yeCreateFunction('action', $cwid, 'action');
    yeCreateString('center', $mwid, 'cam-type');
    ywRectCreateInts($pj_x, $pj_y, 30, 30, $cwid, 'cam');
    yeCreateString("rgba: 10 10 10", $cwid, "background");
    init_map($mwid);
    yirl_return_wid($cwid, "map");
}

function mod_init($mod) {
    ygInitWidgetModule($mod, "nyanarchist", yeCreateFunction("init_wid"));
    yirl_return($mod);
}

?>
