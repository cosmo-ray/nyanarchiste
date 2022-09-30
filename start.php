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

function action($cwid, $eves) {
    echo "ACTION !" . PHP_EOL;
    if (yevIsKeyDown($eves, $Y_ESC_KEY) || yevIsKeyDown($eves, $Y_Q_KEY)) {
        if (yeGet($cwid, "quit"))
            yesCall(yeGet($cwid, "quit")); // Untested
        else
            yesCall(ygGet('FinishGame'));
    }
}

function init_wid($cwid) {
    echo "WESH !!!!" .  PHP_EOL;
    echo "WESH !!!!" .  PHP_EOL;
    echo "WESH !!!!" .  PHP_EOL;
    echo "WESH !!!!" .  PHP_EOL;
    $map_w = 100;
    $map_h = 100;

    $resources = yeCreateArray();

    $el = yeCreateArray($resources);
    yeCreateString(".", $el, "map-char");

    $el = yeCreateArray($resources);
    yeCreateString("#", $el, "map-char");

    $el = yeCreateArray($resources);
    yeCreateString("@", $el, "map-char");

    ywMapInitEntity($cwid, $resources, 0, $map_w, $map_h);
    yeCreateFunction('action', $cwid, 'action');
    yeCreateString('center', $cwid, 'cam-type');
    ywRectCreateInts($pj_x, $pj_y, 15, 15, $cwid, 'cam');
    yeCreateString("rgba: 10 10 10", $cwid, "background");
    yirl_return_wid($cwid, "map");
}

function mod_init($mod) {
    echo "Hello world! " . $mod .  PHP_EOL;

    ygInitWidgetModule($mod, "nyanarchist", yeCreateFunction("init_wid"));
    yePrint($mod);
    yirl_return($mod);
}

?>
