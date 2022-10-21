<?php
/*
 * ----------------------------------------------------------------------------
 * "THE BEER-WARE LICENSE" (Revision 43):
 * <matthias.gatto@protonmail.com> wrote this file.
 *  As long as you retain this notice you
 * can do whatever you want with this stuff. If we meet some day, and you think
 * this stuff is worth it, you can buy me a beer in return Matthias Gatto
 * ----------------------------------------------------------------------------
 */

define("MAP_W", 160);
define("MAP_H", 160);

// $MAX_ROOM = 16; // $MAP_W / 8;
define("MAX_ROOM", 16);

define("CAM_SIZE", 14);
define("CAM_SIZE_W", 24);

define("TOT_ROOM", 8 * 8);

define("SCALE_PIXS",
       "                " .
       "   #       #    " .
       "   #########    " .
       "   #       #    " .
       "   #########    " .
       "   #       #    " .
       "   #########    " .
       "   #       #    ");

define("CUCK",
       "                " .
       "    #######   % " .
       "   #########  % " .
       "   # #   # #  % " .
       "   #   #   #  % " .
       "   #  ###  # #  " .
       "   ##########   " .
       "       #        ");

define("PATCH",
       "            ####" .
       "          ####  " .
       "      #######   " .
       "     #%%%%%#    " .
       "    #%%%%%#     " .
       "   #######      " .
       "  ####          " .
       "####            ");

define("ONIGIRI",
       "                " .
       "        #       " .
       "      #####     " .
       "     #######    " .
       "    #########   " .
       "   ###%%%%%###  " .
       "  ####%%%%%#### " .
       " #####%%%%%#####");


define("EVOLVE_GEM",
       "                " .
       "        #       " .
       "    --#####%%   " .
       "     -#---#%    " .
       "    ##-----##   " .
       "     #%%%%%#    " .
       "       %%%      " .
       "                ");


function add_msg($txwid, $str) {
    $msgs = yeGet($txwid, 'msgs');
    echo $str, PHP_EOL;
    yeInsertAt($msgs, yeCreateString($str), 0, NULL);
    yeIncrAt($txwid, 'msg_cnt');
    if (yeGetIntAt($txwid, 'msg_cnt') > 4)
        yePopBack($msgs);
    yeSetStringAt($txwid, 'text',
                  yeGetStringAt($msgs, 0). PHP_EOL .
                  yeGetStringAt($msgs, 1). PHP_EOL .
                  yeGetStringAt($msgs, 2). PHP_EOL .
                  yeGetStringAt($msgs, 3));
}

function action($cwid, $eves) {
    $pc = yeGet($cwid, 'pc');
    $equipement = yeGet($pc, 'equipement');
    $stats = yeGet($pc, 'stats');
    $mwid = ywCntGetEntry($cwid, 0);
    $txwid = ywCntGetEntry($cwid, 1);

    if (yevIsKeyDown($eves, $Y_ESC_KEY) || yevIsKeyDown($eves, $Y_Q_KEY)) {
        echo "QUIT THE GAME REQUESTED\n";
        if (yeGet($cwid, "quit"))
            yesCall(yeGet($cwid, "quit")); // Untested
        else
            yesCall(ygGet('FinishGame'));
    }
    $xadd = 0;
    $yadd = 0;
    $map_lvl = yeGetIntAt($mwid, 'level');


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
        yeIncrAt($cwid, 'turn-cnt');
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
        if ((yeGetIntAt($cwid, 'turn-cnt') % 70) == 0) {
            $map_lvl = yeGetIntAt($mwid, 'level') + 1;
            place_mob($mwid, yuiRand() % TOT_ROOM,
                      yeGet($mwid, 'rooms'), 8, $map_lvl);
            add_msg($txwid, "a new bad guy appear");
        }
    }
    $cur_item = ywMapIdAt(ywMapCamPointedCase($mwid), 1);
    if ($cur_item == 5 || $cur_item == 6 || $cur_item == 9  ||
        $cur_item == 10 || $cur_item == 11) {
        if ($cur_item == 6) {
            yeIncrAt($equipement, 'hat');
            add_msg($txwid, "Nekomimi upgrade, Nekomimi is now a Nekomimi +".
                    yeGetIntAt($equipement, 'hat'));
        } else if ($cur_item == 5) {
            yeIncrAt($equipement, 'weapon');
            add_msg($txwid, "New weapon upgrade ! " .
                    yeGetStringAt($equipement, 'weapon_name') . " +".
                    (string)(yeGetIntAt($equipement, 'weapon') -1).
                    " to break head, and bring peace, UwU");
        } else if ($cur_item == 9) {
            add_msg($txwid, 'Nom nom nom, tuna onigiri wa oishi desu neeeeee ?');
            yeSetIntAt($pc, 'life', yeGetIntAt($pc, 'max_life'));
        } else if ($cur_item == 10) {
            if ($map_lvl == 0) {
                $old_hat = yeGetStringAt($equipement, 'hat_name');
                yeSetStringAt($equipement, 'hat_name', 'iron nekomimi');
                add_msg($txwid, $old_hat . ' evolve to ' .
                        yeGetStringAt($equipement, 'hat_name'));
            } else {
                add_msg($txwid, 'find evolve an Evolve gem UwW... but nothing evolve :(');
            }
        } else if ($cur_item == 11) {
            add_msg($txwid, 'Apply a patch Kyuuuu');
            yeIncrAt($pc, 'life');
        }
        ywMapPop($mwid, yeGet($mwid, 'cam'));
    } else if ($cur_item == 7) {
        $last_lvl = yeGetIntAt($mwid, 'last_level');

        if ($map_lvl < $last_lvl) {
            ywMapReset($mwid);
            yeIncrAt($mwid, 'level');
            ywMapSetCamPos($mwid, ywPosCreate(8, 7));
            init_map($mwid, $pc);
            echo "NEW LEVEL !!!\n";
        } else {
            echo "YOU WIN !!!! !!!!\n";
            if (yeGet($cwid, "win"))
                yesCall(yeGet($cwid, "win")); // Untested
            else
                yesCall(ygGet('FinishGame'));
        }
    } else if ($cur_item == 8) {
        $enemy = yeGet(ywMapCamPointedCase($mwid), 1); 
        $pc_atk = 1 + yuiRand() % (yeGetIntAt($equipement, 'weapon') + yeGetIntAt($stats, 'strength') + 1);
        yeAddAt($enemy, 'hp', -$pc_atk);
        if (yeGetIntAt($enemy, 'hp') < 0) {
            $r = yuiRand() % 4;
            $kill_msg = ' kill ';

            if ($r == 0)
                $kill_msg = ' deradicalise ~~~~ ';
            else if ($r == 1)
                $kill_msg = ' explain katprokinnyu the hard way to ';
            add_msg($txwid, yeGetStringAt($pc, 'name') .
                    $kill_msg . yeGetStringAt($enemy, 'name'));
            ywMapPop($mwid, yeGet($mwid, 'cam'));
            goto atk_end;
        }
        $end_msd = ' and seems in a good state';
        if (yeGetIntAt($enemy, 'hp') < 4)
            $end_msd = ', and is near dead';
        add_msg($txwid, yeGetStringAt($pc, 'name') .
                ' attack ' . yeGetStringAt($enemy, 'name') .
                ' for ' . (string) $pc_atk . $end_msd);

        ywMapCamAddX($mwid, -$xadd);
        ywMapCamAddY($mwid, -$yadd);
        atk_end:
    }

    // handle monster movement here,
    // move only if enemies are in the same room as PC
    if ($cur_floor == 0 && ($xadd || $yadd)) {
        $cam = yeGet($mwid, 'cam');
        $cur_room = floor(ywRectX($cam) / MAX_ROOM) +
                  8 * floor(ywRectY($cam) / MAX_ROOM);
        $mobs = yeGet($mwid, 'elem-get');
        for ($i = 0; $i < yeLen($mobs); ++$i) {
            $mob = yeGet($mobs, $i);
            if (yeGetIntAt($mob, 'hp') < 0)
                continue;
            $mob_room = yeGetIntAt($mob, 'room_idx');
            if ($mob_room == $cur_room) {
                $midx = yeGetIntAt($mob, '_map_idx');
                $mx = $midx % 160;
                $my = floor($midx / 160);
                $mo_pos = ywPosCreate($mx, $my);
                $mv_pos = ywPosCreate($mx, $my);
                if ($mx > ywRectX($cam)) {
                    ywPosAddXY($mv_pos, -1, 0);
                } else if ($mx < ywRectX($cam)) {
                    ywPosAddXY($mv_pos, 1, 0);
                }
                if ($my > ywRectY($cam)) {
                    ywPosAddXY($mv_pos, 0, -1);
                } else if ($my < ywRectY($cam)) {
                    ywPosAddXY($mv_pos, 0, 1);
                }

                $mob_targeted_case = ywMapCase($mwid, $mv_pos);
                if (yeLen($mob_targeted_case) > 1 ||
                    yeGetIntAt($mob_targeted_case, 0) == 1) {
                    continue;
                } else if (ywPosIsSameEnt($cam, $mv_pos)) {
                            // enemy attack !
                    $pc_def = 0;
                    if (yeGetStringAt($equipement, 'hat_name') ==
                        'iron nekomimi')
                        $pc_def = yeGetIntAt($equipement, 'hat');
                    $enemy_atk = (1 +
                                  yuiRand() % (yeGetIntAt($mob, 'atk') + 1)) -
                               $pc_def;
                    if ($enemy_atk < 0)
                        $enemy_atk = 0;
                    yeAddAt($pc, 'life', -$enemy_atk);
                    add_msg($txwid, yeGetStringAt($mob, 'name') .
                            ' lvl '. yeGetIntAt($mob, 'lvl').
                            ' attack for ' . (string) $enemy_atk .
                            ' ' . yeGetStringAt($pc, 'name') . ' life left: ' . yeGetIntAt($pc, 'life'));

                    if (yeGetIntAt($pc, 'life') < 0) {
                        echo "YOU LOSE 'CAUS YOUR MEDIOCRE AT BEST" . PHP_EOL;
                        if (yeGet($cwid, "lose"))
                            yesCall(yeGet($cwid, "lose")); // Untested
                        else
                            yesCall(ygGet('FinishGame'));
                    }

                    echo "can attack PC !!!";
                    continue;
                } else if (!ywPosIsSameEnt($mo_pos, $mv_pos)) {
                    ywPosPrint($mo_pos);
                    ywPosPrint($mv_pos);
                    ywMapMoveByEntity($mwid, $mo_pos, $mv_pos, $mob);
                }
            }
            
        }
    }
    return $YEVE_ACTION;
}

function draw_room($mwid, $room, $nb) {
    $x = MAX_ROOM * ($nb & 7) + MAX_ROOM / 2;
    $y = MAX_ROOM * floor($nb / 8) + MAX_ROOM / 2;
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
    $room = yeGet($rooms, $room_idx);
    $x = yuiMin(yuiRand() % ywSizeW($room) - 1, 0);
    $x += ($room_idx & 7) * MAX_ROOM + MAX_ROOM / 2 - (ywSizeW($room)) / 2 + 1;
    $y = yuiMin(yuiRand() % ywSizeH($room) - 1, 0);
    $y += floor($room_idx / 8) * MAX_ROOM + MAX_ROOM / 2 - (ywSizeH($room)) / 2 + 1;

    if (yeLen(ywMapCaseXY($mwid, $x, $y)) > 1)
        return false;

    if (gettype($obj_id) == 'integer' || gettype($obj_id) == 'int')
        ywMapPushNbr($mwid, $obj_id, ywPosCreate($x, $y), null);
    else
        ywMapPushElem($mwid, $obj_id, ywPosCreate($x, $y), null);
    return true;
}

function place_mob($mwid, $rooms, $room_idx, $obj_id, $lvl)
{
    $o = yeCreateArray();
    $r = yuiRand() % 3;

    if ($r == 1)
        yeCreateString('fashist pig', $o, 'name');
    else if ($r == 2)
        yeCreateString('capitalist', $o, 'name');
    else
        yeCreateString('terf', $o, 'name');
    yeCreateInt($obj_id, $o, 'id');
    yeCreateInt($lvl, $o, 'lvl');
    yeCreateInt(6 + $lvl * 4, $o, 'hp');
    yeCreateInt(1 + (yuiRand() & 1) + $lvl * 4, $o, 'atk');
    yeCreateInt((yuiRand() & 1) + $lvl * 3, $o, 'def');
    yeCreateInt($room_idx, $o, 'room_idx');
    yePrint($o);
    return place_objs($mwid, $rooms, $room_idx, $o);
}

function place_rand_good_obj($mwid, $rooms, $room_id)
{
    $rid = yuiRand() & 1;
    if ($rid == 1 && (yuiRand() & 1) == 1)
        $rid = 6;
    return place_objs($mwid, $rooms, $room_id,
                      5 + $rid);
}

function mk_corridor($mwid, $rooms, $i)
{
    $room = yeGet($rooms, $i);
    $x = $i & 7;
    $y = floor($i / 8);
    $id = null;
    $try = 0;

    again:

    if ($try == 16)
        return;
    ++$try;
    $target = $i;
    $x_target = 0;
    $y_target = 0;
    $start_x = $x * MAX_ROOM + MAX_ROOM / 2;
    $start_y = $y * MAX_ROOM + MAX_ROOM / 2;
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
    $end_x = $end_x * MAX_ROOM + MAX_ROOM / 2 +
           (ywSizeW($targeted_room) * $x_target) / 2;

    $end_y = floor($target / 8); 
    $end_y = $end_y * MAX_ROOM + MAX_ROOM / 2 +
           (ywSizeH($targeted_room) * $y_target) / 2;
    ywMapDrawSegment($mwid, ywPosCreate($start_x, $start_y),
                     ywPosCreate($end_x, $end_y), $id,
                     $YMAP_DRAW_REPLACE_FIRST);
    //echo 'corridor: ', $YMAP_DRAW_REPLACE_FIRST, ' < ', $start_x, ' - ', $start_y, ' - ', $target, PHP_EOL;
}

function init_map($mwid, $pc) {
    $rooms = yeReCreateArray($mwid, "rooms");
    $tot_rooms = TOT_ROOM;
    for ($i = 0; $i < $tot_rooms - i; $i++) {
        $rand_w = yuiRand() & (MAX_ROOM - 1);
        $rand_h = yuiRand() & (MAX_ROOM - 1);
        if ($i == 0 && $rand_w < 5)
            $rand_w = 5;
        if ($i == 0 && $rand_h < 5)
            $rand_h = 5;
        if ($rand_w < 3)
            $rand_w = 3;
        if ($rand_h < 3)
            $rand_h = 3;
        $r = ywSizeCreate($rand_w, $rand_h, $rooms);
        yeCreateInt(4, $r, 'exits_close');
        draw_room($mwid, $r, $i);
        //echo 'do my sheet: ', $rand_w," - " , $rand_h, " " , MAX_ROOM, PHP_EOL;
    }

    for ($i = 0; $i < $tot_rooms - i; $i++) {
        mk_corridor($mwid, $rooms, $i);
    }
    for ($i = 0; $i < 15; ++$i)
        mk_corridor($mwid, $rooms, yuiRand() % $tot_rooms);

    for ($i = 0; $i < 2; ++$i) {
        place_rand_good_obj($mwid, $rooms, 0);
    }

    for ($i = 0; $i < 20; ++$i) {
        place_rand_good_obj($mwid, $rooms, yuiRand() % $tot_rooms);
    }
    while (place_objs($mwid, $rooms, yuiRand() % $tot_rooms, 9) == false);
    while (place_objs($mwid, $rooms, yuiRand() % $tot_rooms, 9) == false);
    while (place_objs($mwid, $rooms, yuiRand() % $tot_rooms, 9) == false);
    while (place_objs($mwid, $rooms, yuiRand() % $tot_rooms, 7) == false);
    while (place_objs($mwid, $rooms, yuiRand() % $tot_rooms, 10) == false);

    $map_lvl = yeGetIntAt($mwid, 'level');
    $nb_mob = 20;
    $nb_mob += $map_lvl * 8;
    for ($i = 0; $i < $nb_mob; ++$i) {
        $strong = 0;
        while (!(yuiRand() & 3))
            ++$strong;
        place_mob($mwid, $rooms, yuiRand() % $tot_rooms, 8,
                  $strong + yuiRand() % ($map_lvl + 1));
    }

    //yePrint($mwid);
    // yePrint($rooms);
}

function init_wid($cwid) {
    yeCreateInt(0, $cwid, 'turn-cnt');
    $resources = yeCreateArray();
 // if I want to create a container, having mwid and cwid diferent will be usefull
    $entries = yeCreateArray($cwid, "entries");
    $mwid = yeCreateArray($entries);
    yeCreateInt(80, $mwid, 'size');
    yeCreateString("map", $mwid, "<type>");

    $txwid = yeCreateArray($entries);
    yeCreateString("text-screen", $txwid, "<type>");
    $margin = yeCreateArray($txwid, 'margin');
    yeCreateInt(6, $margin, 'size');
    yeCreateString('rgba: 170 170 170 220', $margin, 'color');
    yeCreateString('', $txwid, 'text');
    yeCreateArray($txwid, 'msgs');
    yeCreateInt(0, $txwid, 'msg_cnt');
    add_msg($txwid, 'nyalcom to nyanachiste. an nyanarchist gamu not cringe at all, UwU');
    yeCreateString('rgba: 170 170 170', $txwid, 'text-color');

    $pc = yeGet($cwid, 'pc');
    if (!$pc) {
        $pc = yeCreateArray($cwid, 'pc');
        yeCreateString('Tabby-chan', $pc, 'name');
        $mlife = yeCreateInt(8, $pc, 'max_life');
        yeCreateInt(yeGetInt($mlife), $pc, 'life');
        yeCreateInt(0, $pc, 'xp');
        $stats = yeCreateArray($pc, 'stats');
        yeCreateInt(0, $stats, 'strength');
        yeCreateInt(0, $stats, 'agility');
        $equipement = yeCreateArray($pc, 'equipement');
        yeCreateInt(1, $equipement, "weapon");
        yeCreateString('bat', $equipement, "weapon_name");
        yeCreateString('nekomimi', $equipement, "hat_name");
        yeCreateInt(0, $equipement, "hat");
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
    yeCreateString(".         .     " .
                   "...    ..#.    *" .
                   ".#.###..# .   **" .
                   " .# .  . .   ** " .
                   " ##  .   .  **  " .
                   "###     .. *    " .
                   "####.......     " .
                   " ##  .  ..      ",
                   $el, "map-pixels"); // 4, pc
    $pix_infi = yeCreateArray($el, 'map-pixels-info');
    $pix_mapping = yeCreateArray($pix_infi, 'mapping');
    yeCreateInt(0xffffffff, $pix_mapping, '.');
    yeCreateInt(0x523a28ff, $pix_mapping, '*');
    yeCreateInt(0x7a7a7aff, $pix_mapping, '#');
    ywSizeCreate(2, 4, $pix_infi, 'pix_per_char');
    ywSizeCreate(16, 8, $pix_infi, 'size');

    $el = yeCreateArray($resources);
    yeCreateString("/", $el, "map-char"); // 5, bat, weapon

    $el = yeCreateArray($resources);
    yeCreateString("^", $el, "map-char"); // 6, nekomimi

    $el = yeCreateArray($resources);
    yeCreateString(">", $el, "map-char"); // 7, exit
    yeCreateString(SCALE_PIXS, $el, "map-pixels");
    $pix_infi = yeCreateArray($el, 'map-pixels-info');
    $pix_mapping = yeCreateArray($pix_infi, 'mapping');
    yeCreateInt(0xffffffff, $pix_mapping, '#');
    ywSizeCreate(2, 4, $pix_infi, 'pix_per_char');
    ywSizeCreate(16, 8, $pix_infi, 'size');


    $el = yeCreateArray($resources);
    yeCreateString("R", $el, "map-char"); // 8, rat
    yeCreateString(CUCK, $el, "map-pixels");
    $pix_infi = yeCreateArray($el, 'map-pixels-info');
    $pix_mapping = yeCreateArray($pix_infi, 'mapping');
    yeCreateInt(0xffffffff, $pix_mapping, '#');
    yeCreateInt(0xaa44a3ff, $pix_mapping, '%');
    ywSizeCreate(2, 4, $pix_infi, 'pix_per_char');
    ywSizeCreate(16, 8, $pix_infi, 'size');

    $el = yeCreateArray($resources);
    yeCreateString("o", $el, "map-char"); // 9, onigiri
    yeCreateString(ONIGIRI, $el, "map-pixels");
    $pix_infi = yeCreateArray($el, 'map-pixels-info');
    $pix_mapping = yeCreateArray($pix_infi, 'mapping');
    yeCreateInt(0xffffffff, $pix_mapping, '#');
    yeCreateInt(0x000000ff, $pix_mapping, '%');
    ywSizeCreate(2, 4, $pix_infi, 'pix_per_char');
    ywSizeCreate(16, 8, $pix_infi, 'size');

    $el = yeCreateArray($resources);
    yeCreateString("*", $el, "map-char"); // 10, evolve
    yeCreateString(EVOLVE_GEM, $el, "map-pixels");
    $pix_infi = yeCreateArray($el, 'map-pixels-info');
    $pix_mapping = yeCreateArray($pix_infi, 'mapping');
    yeCreateInt(0xffffffff, $pix_mapping, '#');
    yeCreateInt(0xff2319ff, $pix_mapping, '%');
    yeCreateInt(0x2ff231ff, $pix_mapping, '-');
    ywSizeCreate(2, 4, $pix_infi, 'pix_per_char');
    ywSizeCreate(16, 8, $pix_infi, 'size');

    $el = yeCreateArray($resources);
    yeCreateString("p", $el, "map-char"); // 11, patch, tmp +1 life
    yeCreateString(PATCH, $el, "map-pixels");
    $pix_infi = yeCreateArray($el, 'map-pixels-info');
    $pix_mapping = yeCreateArray($pix_infi, 'mapping');
    yeCreateInt(0xffdeadff, $pix_mapping, '#');
    yeCreateInt(0xf4a460ff, $pix_mapping, '%');
    ywSizeCreate(2, 4, $pix_infi, 'pix_per_char');
    ywSizeCreate(16, 8, $pix_infi, 'size');

    ywMapInitEntity($mwid, $resources, 0, MAP_W,
                    MAP_H);
    yeCreateFunction('action', $cwid, 'action');

    yeCreateInt(8, $mwid, 'cam-getter');

    yeCreateString('center', $mwid, 'cam-type');
    ywSizeCreate(-CAM_SIZE_W / 2, -CAM_SIZE / 2,
                 $mwid, 'cam-threshold');
    yeCreateInt(4, $mwid, 'cam-pointer');
    $cam = ywRectCreateInts(8, 7, CAM_SIZE_W, CAM_SIZE, $mwid, 'cam');
    yePushBack($pc, $cam, 'pos'); // cam and pos are the same element
    yeCreateString("rgba: 10 10 10 255", $cwid, "background");
    yeCreateInt(0, $mwid, 'level');
    yeCreateInt(3, $mwid, 'last_level');
    init_map($mwid, $pc);
    ywSetTurnLengthOverwrite(-1);
    yirl_return_wid($cwid, "container");
}

function mod_init($mod) {
    ygInitWidgetModule($mod, "nyanarchist", yeCreateFunction("init_wid"));
    yirl_return($mod);
}

?>
