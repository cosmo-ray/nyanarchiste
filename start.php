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

$MAP_W = 160;
$MAP_H = 160;

$MAX_ROOM = 16; // $MAP_W / 8;

$CAM_SIZE = 30;

$TOT_ROOM = 8 * 8;



function action($cwid, $eves) {
    $pc = yeGet($cwid, 'pc');
    $mwid = $cwid;

    echo "ACTION !" . PHP_EOL;
    if (yevIsKeyDown($eves, $Y_ESC_KEY) || yevIsKeyDown($eves, $Y_Q_KEY)) {
        if (yeGet($cwid, "quit"))
            yesCall(yeGet($cwid, "quit")); // Untested
        else
            yesCall(ygGet('FinishGame'));
    }
    $xadd = 0;
    $yadd = 0;

    $cur_floor = yeGetIntAt(ywMapCamPointedCase($mwid), 0);

    if (yevIsKeyDown($eves, $Y_LEFT_KEY)) {
        $xadd = -1;
    } else if (yevIsKeyDown($eves, $Y_RIGHT_KEY)) {
        $xadd = +1;
    }

    if (yevIsKeyDown($eves, $Y_UP_KEY)) {
        $yadd = -1;
    } else if (yevIsKeyDown($eves, $Y_DOWN_KEY)) {
        $yadd = +1;
    }

    if ($xadd || $yadd) {
        ywMapCamAddX($mwid, $xadd);
        ywMapCamAddY($mwid, $yadd);
        if (ywMapCamPointedContainId($mwid, 1)) {
            ywMapCamAddX($mwid, -$xadd);
            ywMapCamAddY($mwid, -$yadd);
        } else if ($cur_floor == 2 && $yadd) {
            ywMapCamAddY($mwid, -$yadd);
        } else if ($cur_floor == 3 && $xadd) {
            ywMapCamAddX($mwid, -$xadd);
        }
        echo ywMapCamPointedContainId($mwid, 1), PHP_EOL;
    }
    $cur_item = ywMapIdAt(ywMapCamPointedCase($mwid), 1);
    if ($cur_item == 5 || $cur_item == 6) {
        $equipement = yeGet($pc, 'equipement');
        if ($cur_item == 5) {
            yeIncrAt($equipement, 'hat');
            echo "Nekomimi upgrade, Nekomimi is now a Nekomimi +".
                yeGetIntAt($equipement, 'hat'). PHP_EOL;
        } else if ($cur_item == 6) {
            yeIncrAt($equipement, 'weapon');
            echo "New Bassball bat upgrade !\nbat +".
                (string)(yeGetIntAt($equipement, 'weapon') -1).
                " to break head\nkill catpitalist pig, and bring peace, UwU".
                PHP_EOL;
        }
        echo "ITEM: ", $cur_item, PHP_EOL;
        ywMapPop($mwid, yeGet($mwid, 'cam'));
    } else if ($cur_item == 7) {
        echo "NEXT LEVEL !!!!!!: ", $cur_item, PHP_EOL;
        echo "NEXT LEVEL !!!!!!: ", $cur_item, PHP_EOL;
        echo "NEXT LEVEL !!!!!!: ", $cur_item, PHP_EOL;
        echo "NEXT LEVEL !!!!!!: ", $cur_item, PHP_EOL;
        echo "NEXT LEVEL !!!!!!: ", $cur_item, PHP_EOL;
        echo "NEXT LEVEL !!!!!!: ", $cur_item, PHP_EOL;
    } else if ($cur_item == 8) {
        echo "OVER A MOD !!!!!!!!!";
        ywMapCamAddX($mwid, -$xadd);
        ywMapCamAddY($mwid, -$yadd);
    }

}

function draw_room($mwid, $room, $nb) {
    $max_room = $GLOBALS['MAX_ROOM'];
    $x = $max_room * ($nb & 7) + $max_room / 2;
    $y = $max_room * floor($nb / 8) + $max_room / 2;
    $w = ywSizeW($room);
    $h = ywSizeH($room);

    // echo 'draw :x ', $x, ' - y: ',$y, ' w: ' ,
    //     $w, ' h: ', $h, ' | ',$nb, PHP_EOL;

    // Walls
    // up
    $start = ywPosCreate(floor($x - $w / 2), floor($y - $h / 2));
    $end = ywPosCreate(floor($x + $w / 2), floor($y - $h / 2));
    ywMapDrawSegment($mwid, $start, $end, yeCreateInt(1),
                     $YMAP_DRAW_REPLACE_FIRST);
    // right
    $start = ywPosCreate(floor($x + $w / 2), floor($y + $h / 2));
    ywMapDrawSegment($mwid, $start, $end, yeCreateInt(1),
                     $YMAP_DRAW_REPLACE_FIRST);
    // bottom
    $start = ywPosCreate(floor($x - $w / 2), floor($y + $h / 2));
    $end = ywPosCreate(floor($x + $w / 2), floor($y + $h / 2));
    ywMapDrawSegment($mwid, $start, $end, yeCreateInt(1),
                     $YMAP_DRAW_REPLACE_FIRST);
    // left
    $start = ywPosCreate(floor($x - $w / 2), floor($y - $h / 2));
    $end = ywPosCreate(floor($x - $w / 2), floor($y + $h / 2));
    ywMapDrawSegment($mwid, $start, $end, yeCreateInt(1),
                     $YMAP_DRAW_REPLACE_FIRST);
}

function place_objs($mwid, $rooms, $room_idx, $obj_id)
{
    $max_room = $GLOBALS['MAX_ROOM'];
    $room = $rooms[$room_idx];
    $x = yuiRand() % ywSizeW($room) - 2;
    $x += ($room_idx & 7) * $max_room + $max_room / 2 - ywSizeW($room) / 2;
    $y = yuiRand() % ywSizeH($room) - 1;
    $y += floor($room_idx / 8) * $max_room + $max_room / 2 - ywSizeH($room) / 2;

    if (yeLen(ywMapCaseXY($mwid, $x, $y)) > 1)
        return false;

    if (gettype($obj_id) == 'integer' || gettype($obj_id) == 'int')
        ywMapPushNbr($mwid, $obj_id, ywPosCreate($x, $y), null);
    else
        ywMapPushElem($mwid, $obj_id, ywPosCreate($x, $y), null);
    return true;
}

function place_mob($mwid, $rooms, $room_idx, $obj_id)
{
    $o = yeCreateArray();
    yeCreateInt($obj_id, $o, 'id');
    return place_objs($mwid, $rooms, $room_idx, $o);
}

function mk_corridor($mwid, $rooms, $i)
{
    $room = yeGet($rooms, $i);
    $x = $i & 7;
    $y = floor($i / 8);
    $max_room = $GLOBALS['MAX_ROOM'];
    $id = null;
    $try = 0;

    again:

    if ($try == 16)
        return;
    ++$try;
    $target = $i;
    $x_target = 0;
    $y_target = 0;
    $start_x = $x * $max_room + $max_room / 2;
    $start_y = $y * $max_room + $max_room / 2;
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
    if (yeGetIntAt(ywMapCaseXY($mwid, $start_x, $start_y), 0) != 1)
        goto again;
    //yePrint(ywMapCaseXY($mwid, $start_x, $start_y));

    $targeted_room = yeGet($rooms, $target);
    $end_x = $target & 7; 
    $end_x = $end_x * $max_room + $max_room / 2 +
           (ywSizeW($targeted_room) * $x_target) / 2;

    $end_y = floor($target / 8); 
    $end_y = $end_y * $max_room + $max_room / 2 +
           (ywSizeH($targeted_room) * $y_target) / 2;
    ywMapDrawSegment($mwid, ywPosCreate($start_x, $start_y),
                     ywPosCreate($end_x, $end_y), $id,
                     $YMAP_DRAW_REPLACE_FIRST);
    //echo 'corridor: ', $YMAP_DRAW_REPLACE_FIRST, ' < ', $start_x, ' - ', $start_y, ' - ', $target, PHP_EOL;
}

function init_map($mwid, $pc) {
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
    for ($i = 0; $i < 15; ++$i)
        mk_corridor($mwid, $rooms, yuiRand() % $tot_rooms);


    for ($i = 0; $i < 20; ++$i)
        place_objs($mwid, $rooms, yuiRand() % $tot_rooms,
                   5 + (yuiRand() & 1));
    while (place_objs($mwid, $rooms, yuiRand() % $tot_rooms, 7) == false);


    for ($i = 0; $i < 20; ++$i)
        place_mob($mwid, $rooms, yuiRand() % $tot_rooms, 8);

    //yePrint($mwid);
    // yePrint($rooms);
}

function init_wid($cwid) { 
    $resources = yeCreateArray();
 // if I want to create a container, having mwid and cwid diferent will be usefull
    $mwid = $cwid;

    $pc = yeGet($cwid, 'pc');
    if (!$pc) {
        $pc = yeCreateArray($cwid, 'pc');
        $mlife = yeCreateInt(8, $pc, 'max_life');
        yeCreateInt(yeGetInt($mlife), $pc, 'life');
        yeCreateInt(0, $pc, 'xp');
        $stats = yeCreateArray($pc, 'stats');
        yeCreateInt(0, $stats, 'strength');
        yeCreateInt(0, $stats, 'agility');
        $equipement = yeCreateArray($pc, 'equipement');
        yeCreateInt(1, $equipement, "weapon");
        yeCreateInt(0, $equipement, "hat");
        echo "CREATE PC !!!!\n";
    }
    $el = yeCreateArray($resources);
    yeCreateString(".", $el, "map-char"); // 0, floor

    $el = yeCreateArray($resources);
    yeCreateString("#", $el, "map-char"); // 1, wall

    $el = yeCreateArray($resources);
    yeCreateString("=", $el, "map-char"); // 2, horrizontal corridor
    $el = yeCreateArray($resources);
    yeCreateString("H", $el, "map-char"); // 3, vertical corridor


    $el = yeCreateArray($resources);
    yeCreateString("@", $el, "map-char"); // 4, pc

    $el = yeCreateArray($resources);
    yeCreateString("^", $el, "map-char"); // 5, nekomimi

    $el = yeCreateArray($resources);
    yeCreateString("/", $el, "map-char"); // 6, bat, weapon

    $el = yeCreateArray($resources);
    yeCreateString("C", $el, "map-char"); // 7, victory ?

    $el = yeCreateArray($resources);
    yeCreateString("R", $el, "map-char"); // 8, rat


    ywMapInitEntity($mwid, $resources, 0, $GLOBALS['MAP_W'],
                    $GLOBALS['MAP_H']);
    yeCreateFunction('action', $cwid, 'action');
    yeCreateString('center', $mwid, 'cam-type');
    ywSizeCreate(-$GLOBALS['CAM_SIZE'] / 2, -$GLOBALS['CAM_SIZE'] / 2,
                 $cwid, 'cam-threshold');
    yeCreateInt(4, $cwid, 'cam-pointer');
    $cam = ywRectCreateInts(8, 7, $GLOBALS['CAM_SIZE'], $GLOBALS['CAM_SIZE'],
                            $cwid, 'cam');
    yePushBack($pc, $cam, 'pos'); // cam and pos are the same element
    yeCreateString("rgba: 10 10 10", $cwid, "background");
    init_map($mwid, $pc);
    yirl_return_wid($cwid, "map");
}

function mod_init($mod) {
    ygInitWidgetModule($mod, "nyanarchist", yeCreateFunction("init_wid"));
    yirl_return($mod);
}

?>
