<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * earth implementation : © Guillaume Benny bennygui@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */

// Globals
const MAIN_ACTION_ID_PLANT = 0;
const MAIN_ACTION_ID_COMPOST = 1;
const MAIN_ACTION_ID_WATER = 2;
const MAIN_ACTION_ID_GROW = 3;
const MAIN_ACTION_ID_SOLO_FAUNA = 4;
const MAIN_ACTION_IDS = [
    MAIN_ACTION_ID_PLANT,
    MAIN_ACTION_ID_COMPOST,
    MAIN_ACTION_ID_WATER,
    MAIN_ACTION_ID_GROW,
];

function getMainActionName(int $mainActionId)
{
    switch ($mainActionId) {
        case MAIN_ACTION_ID_PLANT:
            return clienttranslate('Plant');
        case MAIN_ACTION_ID_COMPOST:
            return clienttranslate('Compost');
        case MAIN_ACTION_ID_WATER:
            return clienttranslate('Water');
        case MAIN_ACTION_ID_GROW:
            return clienttranslate('Grow');
        case MAIN_ACTION_ID_SOLO_FAUNA:
            return clienttranslate('Gaia Fauna');
        default:
            throw new \BgaSystemException("BUG! Invalid mainActionId: $mainActionId");
    }
}

function getMainActionColorName(int $mainActionId)
{
    switch ($mainActionId) {
        case MAIN_ACTION_ID_PLANT:
            return clienttranslate('Green');
        case MAIN_ACTION_ID_COMPOST:
            return clienttranslate('Red');
        case MAIN_ACTION_ID_WATER:
            return clienttranslate('Blue');
        case MAIN_ACTION_ID_GROW:
            return clienttranslate('Yellow');
        case MAIN_ACTION_ID_SOLO_FAUNA:
            return clienttranslate('Brown');
        default:
            throw new \BgaSystemException("BUG! Invalid mainActionId: $mainActionId");
    }
}

// States

// States: Player setup
const STATE_PLAYER_SETUP = 'STATE_PLAYER_SETUP';
const STATE_PLAYER_SETUP_ID = 100;

const STATE_PLAYER_SETUP_CHOOSE_INITIAL_CARDS = 'STATE_PLAYER_SETUP_CHOOSE_INITIAL_CARDS';
const STATE_PLAYER_SETUP_CHOOSE_INITIAL_CARDS_ID = 101;

const STATE_PLAYER_SETUP_COMPOST_CARDS = 'STATE_PLAYER_SETUP_COMPOST_CARDS';
const STATE_PLAYER_SETUP_COMPOST_CARDS_ID = 102;

// States: Next phase and main action
const STATE_GAME_NEXT_PHASE = 'STATE_GAME_NEXT_PHASE';
const STATE_GAME_NEXT_PHASE_ID = 200;

const STATE_MAIN_ACTION = 'STATE_MAIN_ACTION';
const STATE_MAIN_ACTION_ID = 300;

const STATE_MAIN_ACTION_CHOOSE = 'STATE_MAIN_ACTION_CHOOSE';
const STATE_MAIN_ACTION_CHOOSE_ID = 301;

// States: Action Plant
const STATE_ACTION_PLANT = 'STATE_ACTION_PLANT';
const STATE_ACTION_PLANT_ID = 400;

const STATE_ACTION_PLANT_ACTIVE_FIRST_CARD = 'STATE_ACTION_PLANT_ACTIVE_FIRST_CARD';
const STATE_ACTION_PLANT_ACTIVE_FIRST_CARD_ID = 401;

const STATE_ACTION_PLANT_ACTIVE_SECOND_CARD = 'STATE_ACTION_PLANT_ACTIVE_SECOND_CARD';
const STATE_ACTION_PLANT_ACTIVE_SECOND_CARD_ID = 402;

const STATE_ACTION_PLANT_ACTIVE_KEEP_CARD = 'STATE_ACTION_PLANT_ACTIVE_KEEP_CARD';
const STATE_ACTION_PLANT_ACTIVE_KEEP_CARD_ID = 403;

const STATE_ACTION_PLANT_INACTIVE_CARD = 'STATE_ACTION_PLANT_INACTIVE_CARD';
const STATE_ACTION_PLANT_INACTIVE_CARD_ID = 404;

const STATE_ACTION_PLANT_SELECT_GAIN = 'STATE_ACTION_PLANT_SELECT_GAIN';
const STATE_ACTION_PLANT_SELECT_GAIN_ID = 405;

const STATE_ACTION_PLANT_SELECT_PAYMENT = 'STATE_ACTION_PLANT_SELECT_PAYMENT';
const STATE_ACTION_PLANT_SELECT_PAYMENT_ID = 406;

// States: Action Compost
const STATE_ACTION_COMPOST = 'STATE_ACTION_COMPOST';
const STATE_ACTION_COMPOST_ID = 500;

const STATE_ACTION_COMPOST_CHOOSE = 'STATE_ACTION_COMPOST_CHOOSE';
const STATE_ACTION_COMPOST_CHOOSE_ID = 501;

// States: Action Water
const STATE_ACTION_WATER = 'STATE_ACTION_WATER';
const STATE_ACTION_WATER_ID = 600;

const STATE_ACTION_PRE_WATER = 'STATE_ACTION_PRE_WATER';
const STATE_ACTION_PRE_WATER_ID = 699;

const STATE_ACTION_WATER_CHOOSE = 'STATE_ACTION_WATER_CHOOSE';
const STATE_ACTION_WATER_CHOOSE_ID = 601;

const STATE_ACTION_WATER_PLACE_SPROUT = 'STATE_ACTION_WATER_PLACE_SPROUT';
const STATE_ACTION_WATER_PLACE_SPROUT_ID = 602;

// States: Action Grow
const STATE_ACTION_GROW = 'STATE_ACTION_GROW';
const STATE_ACTION_GROW_ID = 700;

const STATE_ACTION_PRE_GROW = 'STATE_ACTION_PRE_GROW';
const STATE_ACTION_PRE_GROW_ID = 799;

const STATE_ACTION_GROW_CHOOSE = 'STATE_ACTION_GROW_CHOOSE';
const STATE_ACTION_GROW_CHOOSE_ID = 701;

const STATE_ACTION_GROW_PLACE_GROWTH = 'STATE_ACTION_GROW_PLACE_GROWTH';
const STATE_ACTION_GROW_PLACE_GROWTH_ID = 702;

// States: Activation
const STATE_ACTIVATION = 'STATE_ACTIVATION';
const STATE_ACTIVATION_ID = 800;

const STATE_PRE_ACTIVATION = 'STATE_PRE_ACTIVATION';
const STATE_PRE_ACTIVATION_ID = 899;

const STATE_ACTIVATION_CHOOSE_BOARD_OR_TABLEAU = 'STATE_ACTIVATION_CHOOSE_BOARD_OR_TABLEAU';
const STATE_ACTIVATION_CHOOSE_BOARD_OR_TABLEAU_ID = 801;

const STATE_ACTIVATION_CHOOSE_ACTIVATE_OR_SKIP = 'STATE_ACTIVATION_CHOOSE_ACTIVATE_OR_SKIP';
const STATE_ACTIVATION_CHOOSE_ACTIVATE_OR_SKIP_ID = 802;

const STATE_ACTIVATION_CHOOSE_CARD_TO_COPY = 'STATE_ACTIVATION_CHOOSE_CARD_TO_COPY';
const STATE_ACTIVATION_CHOOSE_CARD_TO_COPY_ID = 803;

const STATE_ACTIVATION_SELECT_PAYMENT = 'STATE_ACTIVATION_SELECT_PAYMENT';
const STATE_ACTIVATION_SELECT_PAYMENT_ID = 804;

const STATE_ACTIVATION_SELECT_GAIN = 'STATE_ACTIVATION_SELECT_GAIN';
const STATE_ACTIVATION_SELECT_GAIN_ID = 805;

// States: Event
const STATE_EVENT_CHOOSE_CARD = 'STATE_EVENT_CHOOSE_CARD';
const STATE_EVENT_CHOOSE_CARD_ID = 901;

const STATE_EVENT_SELECT_PAYMENT = 'STATE_EVENT_SELECT_PAYMENT';
const STATE_EVENT_SELECT_PAYMENT_ID = 902;

const STATE_EVENT_SELECT_GAIN = 'STATE_EVENT_SELECT_GAIN';
const STATE_EVENT_SELECT_GAIN_ID = 903;

// States: Conversion
const STATE_CONVERT_SELECT_PAYMENT = 'STATE_CONVERT_SELECT_PAYMENT';
const STATE_CONVERT_SELECT_PAYMENT_ID = 1001;

// States: Others
const STATE_CONFIRM_END_PHASE = 'STATE_CONFIRM_END_PHASE';
const STATE_CONFIRM_END_PHASE_ID = 1101;

const STATE_GAME_ENDING_LAST_CHANCE = 'STATE_GAME_ENDING_LAST_CHANCE';
const STATE_GAME_ENDING_LAST_CHANCE_ID = 1200;

const STATE_GAME_ENDING_LAST_CHANCE_CONFIRM = 'STATE_GAME_ENDING_LAST_CHANCE_CONFIRM';
const STATE_GAME_ENDING_LAST_CHANCE_CONFIRM_ID = 1201;

const STATE_GAME_ENDING_SCORE_ID = 1300;
const STATE_GAME_ENDING_SCORE = 'STATE_GAME_ENDING_SCORE';

// States: Solo Fauna
const STATE_ACTION_SOLO_FAUNA = 'STATE_ACTION_SOLO_FAUNA';
const STATE_ACTION_SOLO_FAUNA_ID = 1400;

const STATE_ACTION_SOLO_FAUNA_CHOOSE = 'STATE_ACTION_SOLO_FAUNA_CHOOSE';
const STATE_ACTION_SOLO_FAUNA_CHOOSE_ID = 1401;

// Notifications
const NTF_UPDATE_CARDS = 'NTF_UPDATE_CARDS';
const NTF_PLAYER_GAIN_SOIL = 'NTF_PLAYER_GAIN_SOIL';
const NTF_PLAYER_PAY_SOIL = 'NTF_PLAYER_PAY_SOIL';
const NTF_UPDATE_CARD_COUNTS = 'NTF_UPDATE_CARD_COUNTS';
const NTF_UPDATE_LEAF_TOKEN = 'NTF_UPDATE_LEAF_TOKEN';
const NTF_MOVE_COMPOST_FROM_DECK = 'NTF_MOVE_COMPOST_FROM_DECK';
const NTF_UPDATE_PLAYER_TABLEAU = 'NTF_UPDATE_PLAYER_TABLEAU';
const NTF_DESTROY_COMPOST = 'NTF_DESTROY_COMPOST';
const NTF_UPDATE_PLAYER_EVENT = 'NTF_UPDATE_PLAYER_EVENT';
const NTF_LAST_ROUND = 'NTF_LAST_ROUND';
const NTF_SCOREPAD = 'NTF_SCOREPAD';
const NTF_UPDATE_ACTIVE = 'NTF_UPDATE_ACTIVE';
const NTF_UPDATE_CARD_TAG = 'NTF_UPDATE_CARD_TAG';
const NTF_UPDATE_GAIA = 'NTF_UPDATE_GAIA';
const NTF_IS_GAIA_TURN = 'NTF_IS_GAIA_TURN';
const NTF_SEEN_FAUNA_OBJECTIVE = 'NTF_SEEN_FAUNA_OBJECTIVE';
const NTF_UPDATE_FAUNA_PROGRESS = 'NTF_UPDATE_FAUNA_PROGRESS';

// Game Options
const GAME_OPTION_GAME_MODE = 'GAME_OPTION_GAME_MODE';
const GAME_OPTION_GAME_MODE_ID = 100;
const GAME_OPTION_GAME_MODE_VALUE_BEGINNER = 0;
const GAME_OPTION_GAME_MODE_VALUE_STANDARD = 1;
const GAME_OPTION_GAME_MODE_VALUE_ADVANCED = 2;

function isGameModeBeginner()
{
    return (\BX\BGAGlobal\GlobalMgr::getGlobal(GAME_OPTION_GAME_MODE_ID) == GAME_OPTION_GAME_MODE_VALUE_BEGINNER);
}

function isGameModeStandard()
{
    return (\BX\BGAGlobal\GlobalMgr::getGlobal(GAME_OPTION_GAME_MODE_ID) == GAME_OPTION_GAME_MODE_VALUE_STANDARD);
}

function isGameModeAdvanced()
{
    return (\BX\BGAGlobal\GlobalMgr::getGlobal(GAME_OPTION_GAME_MODE_ID) == GAME_OPTION_GAME_MODE_VALUE_ADVANCED);
}

const GAME_OPTION_SOLO_DIFFICULTY = 'GAME_OPTION_SOLO_DIFFICULTY';
const GAME_OPTION_SOLO_DIFFICULTY_ID = 101;
const GAME_OPTION_SOLO_DIFFICULTY_VALUE_BEGINNER = 0;
const GAME_OPTION_SOLO_DIFFICULTY_VALUE_MEDIUM = 1;
const GAME_OPTION_SOLO_DIFFICULTY_VALUE_HARD = 2;
const GAME_OPTION_SOLO_DIFFICULTY_VALUE_EXPERT = 3;

function isGameSolo()
{
    $playerMgr = \BX\Action\ActionRowMgrRegister::getMgr('player');
    return ($playerMgr->getPlayerCount() == 1);
}

function gameSoloDifficulty()
{
    if (!isGameSolo()) {
        return null;
    }
    return \BX\BGAGlobal\GlobalMgr::getGlobal(GAME_OPTION_SOLO_DIFFICULTY_ID);
}

function isGameSoloBeginner()
{
    $difficulty = gameSoloDifficulty();
    if ($difficulty === null) {
        return false;
    }
    return ($difficulty == GAME_OPTION_SOLO_DIFFICULTY_VALUE_BEGINNER);
}

function isGameSoloMedium()
{
    $difficulty = gameSoloDifficulty();
    if ($difficulty === null) {
        return false;
    }
    return ($difficulty == GAME_OPTION_SOLO_DIFFICULTY_VALUE_MEDIUM);
}

function isGameSoloHard()
{
    $difficulty = gameSoloDifficulty();
    if ($difficulty === null) {
        return false;
    }
    return ($difficulty == GAME_OPTION_SOLO_DIFFICULTY_VALUE_HARD);
}

function isGameSoloExpert()
{
    $difficulty = gameSoloDifficulty();
    if ($difficulty === null) {
        return false;
    }
    return ($difficulty == GAME_OPTION_SOLO_DIFFICULTY_VALUE_EXPERT);
}

const GAME_OPTION_HIDE_SETUP = 'GAME_OPTION_HIDE_SETUP';
const GAME_OPTION_HIDE_SETUP_ID = 102;
const GAME_OPTION_HIDE_SETUP_VALUE_VISIBLE = 0;
const GAME_OPTION_HIDE_SETUP_VALUE_HIDDEN = 1;

function isSetupHidden()
{
    return (\BX\BGAGlobal\GlobalMgr::getGlobal(GAME_OPTION_HIDE_SETUP_ID) == GAME_OPTION_HIDE_SETUP_VALUE_HIDDEN);
}

// User preferences
const USER_PREF_ENVIRONMENT_ANIMATIONS_ID = 150;
const USER_PREF_ENVIRONMENT_ANIMATIONS_VALUE_DISABLED = 0;
const USER_PREF_ENVIRONMENT_ANIMATIONS_VALUE_ENABLED = 1;

const USER_PREF_CONFIRM_ID = 151;
const USER_PREF_CONFIRM_VALUE_DISABLED = 0;
const USER_PREF_CONFIRM_VALUE_ENABLED = 1;

const USER_PREF_COLORBLIND_ID = 152;
const USER_PREF_COLORBLIND_VALUE_DISABLED = 0;
const USER_PREF_COLORBLIND_VALUE_ENABLED = 1;

const USER_PREF_PIECE_SHADOW_ID = 153;
const USER_PREF_PIECE_SHADOW_VALUE_DISABLED = 0;
const USER_PREF_PIECE_SHADOW_VALUE_ENABLED = 1;

// Game Statistics
const STATS_TABLE_NB_ROUND = 'STATS_TABLE_NB_ROUND';
const STATS_PLAYER_VP_TOTAL = 'STATS_PLAYER_VP_TOTAL';
const STATS_PLAYER_VP_TABLEAU = 'STATS_PLAYER_VP_TABLEAU';
const STATS_PLAYER_NB_TABLEAU = 'STATS_PLAYER_NB_TABLEAU';
const STATS_PLAYER_VP_EVENT = 'STATS_PLAYER_VP_EVENT';
const STATS_PLAYER_NB_EVENT = 'STATS_PLAYER_NB_EVENT';
const STATS_PLAYER_VP_COMPOST = 'STATS_PLAYER_VP_COMPOST';
const STATS_PLAYER_NB_COMPOST = 'STATS_PLAYER_NB_COMPOST';
const STATS_PLAYER_VP_SPROUT = 'STATS_PLAYER_VP_SPROUT';
const STATS_PLAYER_NB_SPROUT = 'STATS_PLAYER_NB_SPROUT';
const STATS_PLAYER_VP_GROWTH = 'STATS_PLAYER_VP_GROWTH';
const STATS_PLAYER_NB_GROWTH_AND_CANOPIES = 'STATS_PLAYER_NB_GROWTH_AND_CANOPIES';
const STATS_PLAYER_NB_CANOPIES = 'STATS_PLAYER_NB_CANOPIES';
const STATS_PLAYER_VP_TERRAIN = 'STATS_PLAYER_VP_TERRAIN';
const STATS_PLAYER_NB_TERRAIN = 'STATS_PLAYER_NB_TERRAIN';
const STATS_PLAYER_VP_PLAYER_ECOSYSTEM = 'STATS_PLAYER_VP_PLAYER_ECOSYSTEM';
const STATS_PLAYER_VP_FIRST_ECOSYSTEM = 'STATS_PLAYER_VP_FIRST_ECOSYSTEM';
const STATS_PLAYER_VP_SECOND_ECOSYSTEM = 'STATS_PLAYER_VP_SECOND_ECOSYSTEM';
const STATS_PLAYER_VP_TOTAL_ECOSYSTEM = 'STATS_PLAYER_VP_TOTAL_ECOSYSTEM';
const STATS_PLAYER_VP_FAUNA_CARD = 'STATS_PLAYER_VP_FAUNA_CARD';
const STATS_PLAYER_VP_FAUNA_TABLEAU = 'STATS_PLAYER_VP_FAUNA_TABLEAU';
const STATS_PLAYER_VP_FAUNA_TOTAL = 'STATS_PLAYER_VP_FAUNA_TOTAL';
const STATS_PLAYER_NB_CARD_HAND = 'STATS_PLAYER_NB_CARD_HAND';
const STATS_PLAYER_NB_SOIL = 'STATS_PLAYER_NB_SOIL';
const STATS_PLAYER_NB_ACTION_PLANT = 'STATS_PLAYER_NB_ACTION_PLANT';
const STATS_PLAYER_NB_ACTION_COMPOST = 'STATS_PLAYER_NB_ACTION_COMPOST';
const STATS_PLAYER_NB_ACTION_WATER = 'STATS_PLAYER_NB_ACTION_WATER';
const STATS_PLAYER_NB_ACTION_GROW = 'STATS_PLAYER_NB_ACTION_GROW';
const STATS_PLAYER_NB_CARDS_DRAWN_TOTAL = 'STATS_PLAYER_NB_CARDS_DRAWN_TOTAL';
const STATS_PLAYER_NB_CARDS_COMPOSTED_TOTAL = 'STATS_PLAYER_NB_CARDS_COMPOSTED_TOTAL';
const STATS_PLAYER_NB_SOIL_GAINED_TOTAL = 'STATS_PLAYER_NB_SOIL_GAINED_TOTAL';
const STATS_PLAYER_NB_CARDS_PAID_FROM_COMPOST = 'STATS_PLAYER_NB_CARDS_PAID_FROM_COMPOST';
const STATS_PLAYER_NB_SPROUTS_PLACED_TOTAL = 'STATS_PLAYER_NB_SPROUTS_PLACED_TOTAL';
const STATS_PLAYER_NB_SPROUTS_PAID_TOTAL = 'STATS_PLAYER_NB_SPROUTS_PAID_TOTAL';
const STATS_PLAYER_NB_SPROUTS_CONVERTED_TOTAL = 'STATS_PLAYER_NB_SPROUTS_CONVERTED_TOTAL';
const STATS_PLAYER_NB_GROWTH_PLACED_TOTAL = 'STATS_PLAYER_NB_GROWTH_PLACED_TOTAL';
const STATS_PLAYER_NB_GROWTH_PAID_TOTAL = 'STATS_PLAYER_NB_GROWTH_PAID_TOTAL';

const STATS_PLAYER_GAIA_VP_TOTAL = 'STATS_PLAYER_GAIA_VP_TOTAL';
const STATS_PLAYER_GAIA_VP_EARTH = 'STATS_PLAYER_GAIA_VP_EARTH';
const STATS_PLAYER_GAIA_NB_EARTH = 'STATS_PLAYER_GAIA_NB_EARTH';
const STATS_PLAYER_GAIA_VP_COMPOST = 'STATS_PLAYER_GAIA_VP_COMPOST';
const STATS_PLAYER_GAIA_NB_COMPOST = 'STATS_PLAYER_GAIA_NB_COMPOST';
const STATS_PLAYER_GAIA_VP_SPROUT = 'STATS_PLAYER_GAIA_VP_SPROUT';
const STATS_PLAYER_GAIA_NB_SPROUT = 'STATS_PLAYER_GAIA_NB_SPROUT';
const STATS_PLAYER_GAIA_VP_GROWTH = 'STATS_PLAYER_GAIA_VP_GROWTH';
const STATS_PLAYER_GAIA_NB_GROWTH_AND_CANOPIES = 'STATS_PLAYER_GAIA_NB_GROWTH_AND_CANOPIES';
const STATS_PLAYER_GAIA_NB_CANOPIES = 'STATS_PLAYER_GAIA_NB_CANOPIES';
const STATS_PLAYER_GAIA_VP_FAUNA_CARD = 'STATS_PLAYER_GAIA_VP_FAUNA_CARD';
const STATS_PLAYER_GAIA_VP_FAUNA_TABLEAU = 'STATS_PLAYER_GAIA_VP_FAUNA_TABLEAU';
const STATS_PLAYER_GAIA_VP_FAUNA_TOTAL = 'STATS_PLAYER_GAIA_VP_FAUNA_TOTAL';
const STATS_PLAYER_GAIA_NB_SOIL = 'STATS_PLAYER_GAIA_NB_SOIL';