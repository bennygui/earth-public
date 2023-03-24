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
 * gameoptions.inc.php
 *
 * earth game options description
 *
 */

require_once('modules/EA/php/Globals.php');

$game_options = [
    GAME_OPTION_GAME_MODE_ID => [
        'name' => totranslate('Game Mode'),
        'default' => GAME_OPTION_GAME_MODE_VALUE_STANDARD,
        'level' => 'base',
        'values' => [
            GAME_OPTION_GAME_MODE_VALUE_BEGINNER => [
                'name' => totranslate('Beginner'),
                'tmdisplay' => totranslate('Beginner'),
                'description' => totranslate('Beginner game with no ecosystem cards and equal scores for Fauna cards'),
            ],
            GAME_OPTION_GAME_MODE_VALUE_STANDARD => [
                'name' => totranslate('Standard'),
                'tmdisplay' => totranslate('Standard'),
                'description' => totranslate('Standard game with 2 starting choices for Island, Climate and Ecosystem cards'),
                'nobeginner' => true,
            ],
            GAME_OPTION_GAME_MODE_VALUE_ADVANCED => [
                'name' => totranslate('Advanced'),
                'tmdisplay' => totranslate('Advanced'),
                'description' => totranslate('Advanced game with 4 starting choices for Island, Climate and Ecosystem cards'),
                'nobeginner' => true,
            ],
        ],
        'startcondition' => [
            GAME_OPTION_GAME_MODE_VALUE_BEGINNER => [
                [
                    'type' => 'minplayers',
                    'value' => 2,
                    'message' => clienttranslate('Solo games must be played on Standard or Advanced mode'),
                ],

            ],
        ],
    ],
    GAME_OPTION_SOLO_DIFFICULTY_ID => [
        'name' => totranslate('Solo Difficulty Level'),
        'default' => GAME_OPTION_SOLO_DIFFICULTY_VALUE_BEGINNER,
        'level' => 'base',
        'values' => [
            GAME_OPTION_SOLO_DIFFICULTY_VALUE_BEGINNER => [
                'name' => totranslate('Beginner'),
                'tmdisplay' => totranslate('Beginner Solo'),
                'description' => totranslate('Beginner solo difficulty level with weaker Gaia actions and extra turns for the player'),
            ],
            GAME_OPTION_SOLO_DIFFICULTY_VALUE_MEDIUM => [
                'name' => totranslate('Medium'),
                'tmdisplay' => totranslate('Medium Solo'),
                'description' => totranslate('Medium solo difficulty level with weaker Gaia actions'),
                'nobeginner' => true,
            ],
            GAME_OPTION_SOLO_DIFFICULTY_VALUE_HARD => [
                'name' => totranslate('Hard'),
                'tmdisplay' => totranslate('Hard Solo'),
                'description' => totranslate('Hard solo difficulty level with powerful Gaia actions'),
                'nobeginner' => true,
            ],
            GAME_OPTION_SOLO_DIFFICULTY_VALUE_EXPERT => [
                'name' => totranslate('Expert'),
                'tmdisplay' => totranslate('Expert Solo'),
                'description' => totranslate('Expert solo difficulty level with powerful Gaia actions and extra actions for Gaia'),
                'nobeginner' => true,
            ],
        ],
        'displaycondition' => [
            [
                'type' => 'maxplayers',
                'value' => 1,
            ],
        ],
    ],
];

$game_preferences = [
    USER_PREF_ENVIRONMENT_ANIMATIONS_ID => [
        'name' => totranslate('Subtle environment animations'),
        'needReload' => true,
        'values' => [
            USER_PREF_ENVIRONMENT_ANIMATIONS_VALUE_ENABLED => [
                'name' => totranslate('Enabled'),
            ],
            USER_PREF_ENVIRONMENT_ANIMATIONS_VALUE_DISABLED => [
                'name' => totranslate('Disabled'),
                'cssPref' => 'ea-hide-environment-animations'
            ],
        ],
        'default' => USER_PREF_ENVIRONMENT_ANIMATIONS_VALUE_ENABLED,
    ],
    USER_PREF_CONFIRM_ID => [
        'name' => totranslate('Confirm main action'),
        'needReload' => true,
        'values' => [
            USER_PREF_CONFIRM_VALUE_ENABLED => [
                'name' => totranslate('Enabled'),
                'cssPref' => 'ea-pref-confim-actions'
            ],
            USER_PREF_CONFIRM_VALUE_DISABLED => [
                'name' => totranslate('Disabled'),
            ],
        ],
        'default' => USER_PREF_CONFIRM_VALUE_DISABLED,
    ],
    USER_PREF_COLORBLIND_ID => [
        'name' => totranslate('Colorblind mode'),
        'needReload' => true,
        'values' => [
            USER_PREF_COLORBLIND_VALUE_ENABLED => [
                'name' => totranslate('Enabled'),
            ],
            USER_PREF_COLORBLIND_VALUE_DISABLED => [
                'name' => totranslate('Disabled'),
                'cssPref' => 'ea-colorblind-inactive'
            ],
        ],
        'default' => USER_PREF_COLORBLIND_VALUE_DISABLED,
    ],
];
