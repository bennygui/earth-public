<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * earth implementation : © Guillaume Benny bennygui@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * stats.inc.php
 *
 * earth game statistics description
 *
 */

require_once('modules/EA/php/Globals.php');

$stats_type = [
    // Statistics global to table
    'table' => [
        STATS_TABLE_NB_ROUND => ['id' => 10, 'name' => totranslate('Nb Rounds'), 'type' => 'int'],
    ],

    // Statistics for each player
    'player' => [
        STATS_PLAYER_VP_TOTAL => ['id' => 10, 'name' => totranslate('VP Total'), 'type' => 'int'],
        STATS_PLAYER_VP_TABLEAU => ['id' => 11, 'name' => totranslate('VP from Tableau Cards'), 'type' => 'int'],
        STATS_PLAYER_NB_TABLEAU => ['id' => 12, 'name' => totranslate('Nb Tableau Cards'), 'type' => 'int'],
        STATS_PLAYER_VP_EVENT => ['id' => 13, 'name' => totranslate('VP from Event Cards'), 'type' => 'int'],
        STATS_PLAYER_NB_EVENT => ['id' => 14, 'name' => totranslate('Nb Event Cards'), 'type' => 'int'],
        STATS_PLAYER_VP_COMPOST => ['id' => 15, 'name' => totranslate('VP from Compost'), 'type' => 'int'],
        STATS_PLAYER_NB_COMPOST => ['id' => 16, 'name' => totranslate('Nb Compost Cards'), 'type' => 'int'],
        STATS_PLAYER_VP_SPROUT => ['id' => 17, 'name' => totranslate('VP from Sprouts'), 'type' => 'int'],
        STATS_PLAYER_NB_SPROUT => ['id' => 18, 'name' => totranslate('Nb Sprouts'), 'type' => 'int'],
        STATS_PLAYER_VP_GROWTH => ['id' => 19, 'name' => totranslate('VP from Growth and Canopies'), 'type' => 'int'],
        STATS_PLAYER_NB_GROWTH_AND_CANOPIES => ['id' => 20, 'name' => totranslate('Nb Growth and Canopies'), 'type' => 'int'],
        STATS_PLAYER_NB_CANOPIES => ['id' => 21, 'name' => totranslate('Nb Canopies'), 'type' => 'int'],
        STATS_PLAYER_VP_TERRAIN => ['id' => 22, 'name' => totranslate('VP from Terrain Cards'), 'type' => 'int'],
        STATS_PLAYER_NB_TERRAIN => ['id' => 23, 'name' => totranslate('Nb Terrain Cards'), 'type' => 'int'],
        STATS_PLAYER_VP_PLAYER_ECOSYSTEM => ['id' => 24, 'name' => totranslate('VP from Player Ecosystem Card'), 'type' => 'int'],
        STATS_PLAYER_VP_FIRST_ECOSYSTEM => ['id' => 25, 'name' => totranslate('VP from First Ecosystem Card'), 'type' => 'int'],
        STATS_PLAYER_VP_SECOND_ECOSYSTEM => ['id' => 26, 'name' => totranslate('VP from Second Ecosystem Card'), 'type' => 'int'],
        STATS_PLAYER_VP_TOTAL_ECOSYSTEM => ['id' => 27, 'name' => totranslate('VP Total for Ecosystem Cards'), 'type' => 'int'],
        STATS_PLAYER_VP_FAUNA_CARD => ['id' => 28, 'name' => totranslate('VP from Fauna Cards'), 'type' => 'int'],
        STATS_PLAYER_VP_FAUNA_TABLEAU => ['id' => 29, 'name' => totranslate('VP from Tableau Bonus'), 'type' => 'int'],
        STATS_PLAYER_VP_FAUNA_TOTAL => ['id' => 30, 'name' => totranslate('VP Total for Fauna Board'), 'type' => 'int'],
        STATS_PLAYER_NB_CARD_HAND => ['id' => 31, 'name' => totranslate('Nb Cards in Hand at game end'), 'type' => 'int'],
        STATS_PLAYER_NB_SOIL => ['id' => 32, 'name' => totranslate('Nb Soil in reserve at game end'), 'type' => 'int'],
        STATS_PLAYER_NB_ACTION_PLANT => ['id' => 47, 'name' => totranslate('Nb times choose Plant action'), 'type' => 'int'],
        STATS_PLAYER_NB_ACTION_COMPOST => ['id' => 48, 'name' => totranslate('Nb times choose Compost action'), 'type' => 'int'],
        STATS_PLAYER_NB_ACTION_WATER => ['id' => 49, 'name' => totranslate('Nb times choose Water action'), 'type' => 'int'],
        STATS_PLAYER_NB_ACTION_GROW => ['id' => 50, 'name' => totranslate('Nb times choose Grow action'), 'type' => 'int'],
        STATS_PLAYER_NB_CARDS_DRAWN_TOTAL => ['id' => 51, 'name' => totranslate('Nb Cards drawn in total'), 'type' => 'int'],
        STATS_PLAYER_NB_CARDS_COMPOSTED_TOTAL => ['id' => 52, 'name' => totranslate('Nb Cards composted in total'), 'type' => 'int'],
        STATS_PLAYER_NB_SOIL_GAINED_TOTAL => ['id' => 53, 'name' => totranslate('Nb Soil gained in total'), 'type' => 'int'],
        STATS_PLAYER_NB_CARDS_PAID_FROM_COMPOST => ['id' => 54, 'name' => totranslate('Nb Cards discarded (paid) from compost'), 'type' => 'int'],
        STATS_PLAYER_NB_SPROUTS_PLACED_TOTAL => ['id' => 55, 'name' => totranslate('Nb Sprouts placed in total'), 'type' => 'int'],
        STATS_PLAYER_NB_SPROUTS_PAID_TOTAL => ['id' => 56, 'name' => totranslate('Nb Sprouts discarded (paid) in total'), 'type' => 'int'],
        STATS_PLAYER_NB_SPROUTS_CONVERTED_TOTAL => ['id' => 57, 'name' => totranslate('Nb Sprouts converted in total'), 'type' => 'int'],
        STATS_PLAYER_NB_GROWTH_PLACED_TOTAL => ['id' => 58, 'name' => totranslate('Nb Growth placed in total'), 'type' => 'int'],
        STATS_PLAYER_NB_GROWTH_PAID_TOTAL => ['id' => 59, 'name' => totranslate('Nb Growth discarded (paid) in total'), 'type' => 'int'],

        STATS_PLAYER_GAIA_VP_TOTAL => ['id' => 33, 'name' => totranslate('Solo: Gaia VP Total'), 'type' => 'int'],
        STATS_PLAYER_GAIA_VP_EARTH => ['id' => 34, 'name' => totranslate('Solo: Gaia VP from Earth Cards'), 'type' => 'int'],
        STATS_PLAYER_GAIA_NB_EARTH => ['id' => 35, 'name' => totranslate('Solo: Gaia Nb Earth Cards'), 'type' => 'int'],
        STATS_PLAYER_GAIA_VP_COMPOST => ['id' => 36, 'name' => totranslate('Solo: Gaia VP from Compost'), 'type' => 'int'],
        STATS_PLAYER_GAIA_NB_COMPOST => ['id' => 37, 'name' => totranslate('Solo: Gaia Nb Compost'), 'type' => 'int'],
        STATS_PLAYER_GAIA_VP_SPROUT => ['id' => 38, 'name' => totranslate('Solo: Gaia VP from Sprouts'), 'type' => 'int'],
        STATS_PLAYER_GAIA_NB_SPROUT => ['id' => 39, 'name' => totranslate('Solo: Gaia Nb Sprouts'), 'type' => 'int'],
        STATS_PLAYER_GAIA_VP_GROWTH => ['id' => 40, 'name' => totranslate('Solo: Gaia VP from Growth'), 'type' => 'int'],
        STATS_PLAYER_GAIA_NB_GROWTH_AND_CANOPIES => ['id' => 41, 'name' => totranslate('Solo: Gaia Nb Growth and Canopies'), 'type' => 'int'],
        STATS_PLAYER_GAIA_NB_CANOPIES => ['id' => 42, 'name' => totranslate('Solo: Gaia Nb Canopies'), 'type' => 'int'],
        STATS_PLAYER_GAIA_VP_FAUNA_CARD => ['id' => 43, 'name' => totranslate('Solo: Gaia VP from Fauna Cards'), 'type' => 'int'],
        STATS_PLAYER_GAIA_VP_FAUNA_TABLEAU => ['id' => 44, 'name' => totranslate('Solo: Gaia VP from Tableau Bonus'), 'type' => 'int'],
        STATS_PLAYER_GAIA_VP_FAUNA_TOTAL => ['id' => 45, 'name' => totranslate('Solo: Gaia VP Total for Fauna Board'), 'type' => 'int'],
        STATS_PLAYER_GAIA_NB_SOIL => ['id' => 46, 'name' => totranslate('Solo: Gaia Nb Soil in reserve at game end'), 'type' => 'int'],
    ],
];
