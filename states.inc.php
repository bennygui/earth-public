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
 * states.inc.php
 *
 * earth game states description
 *
 */

require_once("modules/BX/php/Globals.php");
require_once("modules/EA/php/Globals.php");

$machinestates = [

    // The initial state. Please do not modify.
    STATE_GAME_START_ID => [
        "name" => STATE_GAME_START,
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => [
            "" => STATE_PLAYER_SETUP_ID,
        ],
    ],

    // Player setup
    STATE_PLAYER_SETUP_ID => [
        "name" => STATE_PLAYER_SETUP,
        "description" => clienttranslate('Other players must choose inital cards'),
        "descriptionmyturn" => clienttranslate('${you} must choose inital cards'),
        "type" => "multipleactiveplayer",
        "args" => "argsEarthDefaultMultiActive",
        "initialprivate" => STATE_PLAYER_SETUP_CHOOSE_INITIAL_CARDS_ID,
        "action" => 'stPlayerSetup',
        "possibleactions" => [
            'eventPlay',
            'convertPlay',
        ],
        "transitions" => [
            '' => STATE_GAME_NEXT_PHASE_ID,
        ],
    ],
    STATE_PLAYER_SETUP_CHOOSE_INITIAL_CARDS_ID => [
        "name" => STATE_PLAYER_SETUP_CHOOSE_INITIAL_CARDS,
        "description" => clienttranslate('Other players must play'),
        "descriptionmyturn" => clienttranslate('${you} must choose: ${message}'),
        "type" => "private",
        "args" => "argsPlayerSetupChooseInitialCards",
        "possibleactions" => [
            'playerSetupChoose',
        ],
        "transitions" => [
            'hasCardToCompost' => STATE_PLAYER_SETUP_COMPOST_CARDS_ID,
            'confirmEndPhase' => STATE_CONFIRM_END_PHASE_ID,
        ],
    ],
    STATE_PLAYER_SETUP_COMPOST_CARDS_ID => [
        "name" => STATE_PLAYER_SETUP_COMPOST_CARDS,
        "description" => clienttranslate('Other players must play'),
        "descriptionmyturn" => clienttranslate('${you} must choose ${compostFromHandCount} card(s) to compost'),
        "type" => "private",
        "args" => "argsPlayerSetupCompostCards",
        "possibleactions" => [
            'playerSetupCompost',
        ],
        "transitions" => [
            'confirmEndPhase' => STATE_CONFIRM_END_PHASE_ID,
        ],
    ],

    // Next phase and main action
    STATE_GAME_NEXT_PHASE_ID => [
        "name" => STATE_GAME_NEXT_PHASE,
        "type" => "game",
        "action" => 'stGameNextPhase',
        "updateGameProgression" => true,
        "transitions" => [
            'mainAction' => STATE_MAIN_ACTION_ID,
            'actionPlant' => STATE_ACTION_PLANT_ID,
            'actionCompost' => STATE_ACTION_COMPOST_ID,
            'actionWater' => STATE_ACTION_PRE_WATER_ID,
            'actionGrow' => STATE_ACTION_PRE_GROW_ID,
            'actionSoloFauna' => STATE_ACTION_SOLO_FAUNA_ID,
            'activation' => STATE_PRE_ACTIVATION_ID,
            'gameEndingLastChance' => STATE_GAME_ENDING_LAST_CHANCE_ID,
        ],
    ],
    STATE_MAIN_ACTION_ID => [
        "name" => STATE_MAIN_ACTION,
        "description" => clienttranslate('Active player must choose the main action'),
        "descriptionmyturn" => clienttranslate('${you} must choose the main action'),
        "type" => "multipleactiveplayer",
        "args" => "argsEarthDefaultMultiActive",
        "initialprivate" => STATE_MAIN_ACTION_CHOOSE_ID,
        "action" => 'stMainAction',
        "possibleactions" => [
            'eventPlay',
            'convertPlay',
        ],
        "transitions" => [
            '' => STATE_GAME_NEXT_PHASE_ID,
        ],
    ],
    STATE_MAIN_ACTION_CHOOSE_ID => [
        "name" => STATE_MAIN_ACTION_CHOOSE,
        "description" => clienttranslate('Other players must play'),
        "descriptionmyturn" => clienttranslate('${you} must choose the main action'),
        "type" => "private",
        "args" => "argsMainActionChoose",
        "possibleactions" => [
            'mainActionChoose',
            'eventPlay',
            'convertPlay',
        ],
        "transitions" => [
            'confirmEndPhase' => STATE_CONFIRM_END_PHASE_ID,
        ],
    ],

    // Action: Plant
    STATE_ACTION_PLANT_ID => [
        "name" => STATE_ACTION_PLANT,
        "description" => clienttranslate('All players must plant and keep a card'),
        "descriptionmyturn" => clienttranslate('${you} must plant ${mainActionId}'),
        "type" => "multipleactiveplayer",
        "args" => "argsEarthDefaultMultiActive",
        "initialprivate" => STATE_ACTION_PLANT_INACTIVE_CARD_ID,
        "action" => 'stActionPlant',
        "possibleactions" => [
            'eventPlay',
            'convertPlay',
        ],
        "transitions" => [
            '' => STATE_GAME_NEXT_PHASE_ID,
        ],
    ],
    STATE_ACTION_PLANT_ACTIVE_FIRST_CARD_ID => [
        "name" => STATE_ACTION_PLANT_ACTIVE_FIRST_CARD,
        "description" => clienttranslate('Other players must play'),
        "descriptionmyturn" => clienttranslate('${you} must plant your first card or skip ${mainActionId}'),
        "type" => "private",
        "args" => "argsPlantActionPlant",
        "possibleactions" => [
            'plantActionPlanCard',
            'plantActionSkipPlanting',
            'eventPlay',
            'convertPlay',
        ],
        "transitions" => [
            'chooseCards' => STATE_ACTION_PLANT_ACTIVE_KEEP_CARD_ID,
            'plantSecondCard' => STATE_ACTION_PLANT_ACTIVE_SECOND_CARD_ID,
            'activateSelectGain' => STATE_ACTION_PLANT_SELECT_GAIN_ID,
            'activateSelectPayment' => STATE_ACTION_PLANT_SELECT_PAYMENT_ID,
            'confirmEndPhase' => STATE_CONFIRM_END_PHASE_ID,
        ],
    ],
    STATE_ACTION_PLANT_ACTIVE_SECOND_CARD_ID => [
        "name" => STATE_ACTION_PLANT_ACTIVE_SECOND_CARD,
        "description" => clienttranslate('Other players must play'),
        "descriptionmyturn" => clienttranslate('${you} must plant your second card or skip ${mainActionId}'),
        "type" => "private",
        "args" => "argsPlantActionPlant",
        "possibleactions" => [
            'plantActionPlanCard',
            'plantActionSkipPlanting',
            'eventPlay',
            'convertPlay',
        ],
        "transitions" => [
            'chooseCards' => STATE_ACTION_PLANT_ACTIVE_KEEP_CARD_ID,
            'activateSelectGain' => STATE_ACTION_PLANT_SELECT_GAIN_ID,
            'activateSelectPayment' => STATE_ACTION_PLANT_SELECT_PAYMENT_ID,
            'confirmEndPhase' => STATE_CONFIRM_END_PHASE_ID,
        ],
    ],
    STATE_ACTION_PLANT_ACTIVE_KEEP_CARD_ID => [
        "name" => STATE_ACTION_PLANT_ACTIVE_KEEP_CARD,
        "description" => clienttranslate('Other players must play'),
        "descriptionmyturn" => clienttranslate('${you} must keep one of the drawn cards (plant: ${mainActionId})'),
        "type" => "private",
        "args" => "argsPlantActionKeepCard",
        "possibleactions" => [
            'planActionKeepOneDrawnCard',
        ],
        "transitions" => [
            'confirmEndPhase' => STATE_CONFIRM_END_PHASE_ID,
        ],
    ],
    STATE_ACTION_PLANT_INACTIVE_CARD_ID => [
        "name" => STATE_ACTION_PLANT_INACTIVE_CARD,
        "description" => clienttranslate('Other players must play'),
        "descriptionmyturn" => clienttranslate('${you} must plant a card or skip ${mainActionId}'),
        "type" => "private",
        "args" => "argsPlantActionPlant",
        "possibleactions" => [
            'plantActionPlanCard',
            'plantActionSkipPlanting',
            'eventPlay',
            'convertPlay',
        ],
        "transitions" => [
            'activateSelectGain' => STATE_ACTION_PLANT_SELECT_GAIN_ID,
            'activateSelectPayment' => STATE_ACTION_PLANT_SELECT_PAYMENT_ID,
            'confirmEndPhase' => STATE_CONFIRM_END_PHASE_ID,
        ],
    ],
    STATE_ACTION_PLANT_SELECT_GAIN_ID => [
        "name" => STATE_ACTION_PLANT_SELECT_GAIN,
        "description" => clienttranslate('Other players must play'),
        "descriptionmyturn" => clienttranslate('${you} can gain up to ${gainList} for planting ${mainActionId}'),
        "type" => "private",
        "args" => "argsAbilityGain",
        "possibleactions" => [
            'plantActionGain',
        ],
        "transitions" => [
            'chooseCards' => STATE_ACTION_PLANT_ACTIVE_KEEP_CARD_ID,
            'plantSecondCard' => STATE_ACTION_PLANT_ACTIVE_SECOND_CARD_ID,
            'confirmEndPhase' => STATE_CONFIRM_END_PHASE_ID,
        ],
    ],
    STATE_ACTION_PLANT_SELECT_PAYMENT_ID => [
        "name" => STATE_ACTION_PLANT_SELECT_PAYMENT,
        "description" => clienttranslate('Other players must play'),
        "descriptionmyturn" => clienttranslate('${you} can select another payment for planting ${mainActionId}'),
        "type" => "private",
        "args" => "argsPlantActionPayment",
        "possibleactions" => [
            'plantActionPlanCardWithPayment',
        ],
        "transitions" => [
            'chooseCards' => STATE_ACTION_PLANT_ACTIVE_KEEP_CARD_ID,
            'plantSecondCard' => STATE_ACTION_PLANT_ACTIVE_SECOND_CARD_ID,
            'activateSelectGain' => STATE_ACTION_PLANT_SELECT_GAIN_ID,
            'confirmEndPhase' => STATE_CONFIRM_END_PHASE_ID,
        ],
    ],

    // Action: Compost
    STATE_ACTION_COMPOST_ID => [
        "name" => STATE_ACTION_COMPOST,
        "description" => clienttranslate('All players must compost'),
        "descriptionmyturn" => clienttranslate('${you} must compost'),
        "type" => "multipleactiveplayer",
        "args" => "argsEarthDefaultMultiActive",
        "initialprivate" => STATE_ACTION_COMPOST_CHOOSE_ID,
        "action" => 'stActionCompost',
        "possibleactions" => [
            'eventPlay',
            'convertPlay',
        ],
        "transitions" => [
            '' => STATE_GAME_NEXT_PHASE_ID,
        ],
    ],
    STATE_ACTION_COMPOST_CHOOSE_ID => [
        "name" => STATE_ACTION_COMPOST_CHOOSE,
        "description" => clienttranslate('Other players must play'),
        "descriptionmyturn" => clienttranslate('${you} must choose how to compost ${mainActionId}'),
        "type" => "private",
        "args" => "argsEarthDefault",
        "possibleactions" => [
            'compostActionChooseCompostFromDeck',
            'compostActionChooseGainSoil',
            'eventPlay',
            'convertPlay',
        ],
        "transitions" => [
            'confirmEndPhase' => STATE_CONFIRM_END_PHASE_ID,
        ],
    ],

    // Action: Water
    STATE_ACTION_PRE_WATER_ID => [
        "name" => STATE_ACTION_PRE_WATER,
        "type" => "game",
        "action" => 'stActionPreWater',
        "transitions" => [
            '' => STATE_ACTION_WATER_ID,
        ],
    ],
    STATE_ACTION_WATER_ID => [
        "name" => STATE_ACTION_WATER,
        "description" => clienttranslate('All players must water'),
        "descriptionmyturn" => clienttranslate('${you} must water'),
        "type" => "multipleactiveplayer",
        "args" => "argsEarthDefaultMultiActive",
        "initialprivate" => STATE_ACTION_WATER_CHOOSE_ID,
        "action" => 'stActionWater',
        "possibleactions" => [
            'eventPlay',
            'convertPlay',
        ],
        "transitions" => [
            '' => STATE_GAME_NEXT_PHASE_ID,
        ],
    ],
    STATE_ACTION_WATER_CHOOSE_ID => [
        "name" => STATE_ACTION_WATER_CHOOSE,
        "description" => clienttranslate('Other players must play'),
        "descriptionmyturn" => clienttranslate('${you} must choose how to water ${mainActionId}'),
        "type" => "private",
        "args" => "argsEarthDefault",
        "possibleactions" => [
            'waterActionChoosePlaceSprout',
            'waterActionChooseGainSoil',
            'eventPlay',
            'convertPlay',
        ],
        "transitions" => [
            'choosePlaceSprout' => STATE_ACTION_WATER_PLACE_SPROUT_ID,
            'confirmEndPhase' => STATE_CONFIRM_END_PHASE_ID,
        ],
    ],
    STATE_ACTION_WATER_PLACE_SPROUT_ID => [
        "name" => STATE_ACTION_WATER_PLACE_SPROUT,
        "description" => clienttranslate('Other players must play'),
        "descriptionmyturn" => clienttranslate('${you} must place up to: ${gainList} (water: ${mainActionId})'),
        "type" => "private",
        "args" => "argsAbilityGain",
        "possibleactions" => [
            'waterActionPlaceSprout',
        ],
        "transitions" => [
            'confirmEndPhase' => STATE_CONFIRM_END_PHASE_ID,
        ],
    ],

    // Action: Grow
    STATE_ACTION_PRE_GROW_ID => [
        "name" => STATE_ACTION_PRE_GROW,
        "type" => "game",
        "action" => 'stActionPreGrow',
        "transitions" => [
            '' => STATE_ACTION_GROW_ID,
        ],
    ],
    STATE_ACTION_GROW_ID => [
        "name" => STATE_ACTION_GROW,
        "description" => clienttranslate('All players must grow'),
        "descriptionmyturn" => clienttranslate('${you} must grow'),
        "type" => "multipleactiveplayer",
        "args" => "argsEarthDefaultMultiActive",
        "initialprivate" => STATE_ACTION_GROW_CHOOSE_ID,
        "action" => 'stActionGrow',
        "possibleactions" => [
            'eventPlay',
            'convertPlay',
        ],
        "transitions" => [
            '' => STATE_GAME_NEXT_PHASE_ID,
        ],
    ],
    STATE_ACTION_GROW_CHOOSE_ID => [
        "name" => STATE_ACTION_GROW_CHOOSE,
        "description" => clienttranslate('Other players must play'),
        "descriptionmyturn" => clienttranslate('${you} must choose how to grow ${mainActionId}'),
        "type" => "private",
        "args" => "argsEarthDefault",
        "possibleactions" => [
            'growActionChoosePlaceGrowth',
            'growActionChooseDrawCard',
            'eventPlay',
            'convertPlay',
        ],
        "transitions" => [
            'choosePlaceGrowth' => STATE_ACTION_GROW_PLACE_GROWTH_ID,
            'confirmEndPhase' => STATE_CONFIRM_END_PHASE_ID,
        ],
    ],
    STATE_ACTION_GROW_PLACE_GROWTH_ID => [
        "name" => STATE_ACTION_GROW_PLACE_GROWTH,
        "description" => clienttranslate('Other players must play'),
        "descriptionmyturn" => clienttranslate('${you} must place up to: ${gainList} (grow: ${mainActionId})'),
        "type" => "private",
        "args" => "argsAbilityGain",
        "possibleactions" => [
            'growActionPlaceGrowth',
        ],
        "transitions" => [
            'confirmEndPhase' => STATE_CONFIRM_END_PHASE_ID,
        ],
    ],

    // Action: Solo Fauna
    STATE_ACTION_SOLO_FAUNA_ID => [
        "name" => STATE_ACTION_SOLO_FAUNA,
        "description" => clienttranslate('Player must choose a Fauna card for Gaia'),
        "descriptionmyturn" => clienttranslate('${you} must choose a Fauna card for Gaia'),
        "type" => "multipleactiveplayer",
        "args" => "argsEarthDefaultMultiActive",
        "initialprivate" => STATE_ACTION_SOLO_FAUNA_CHOOSE_ID,
        "action" => 'stActionSoloFauna',
        "possibleactions" => [
            'eventPlay',
            'convertPlay',
        ],
        "transitions" => [
            '' => STATE_GAME_NEXT_PHASE_ID,
        ],
    ],
    STATE_ACTION_SOLO_FAUNA_CHOOSE_ID => [
        "name" => STATE_ACTION_SOLO_FAUNA_CHOOSE,
        "description" => clienttranslate('Player must choose a Fauna card for Gaia'),
        "descriptionmyturn" => clienttranslate('${you} must choose a Fauna card for Gaia'),
        "type" => "private",
        "args" => "argsSoloFaunaChoose",
        "possibleactions" => [
            'eventPlay',
            'convertPlay',
            'soloFaunaChoose',
        ],
        "transitions" => [
            'confirmEndPhase' => STATE_CONFIRM_END_PHASE_ID,
        ],
    ],

    // Activation
    STATE_PRE_ACTIVATION_ID => [
        "name" => STATE_PRE_ACTIVATION,
        "type" => "game",
        "action" => 'stPreActivation',
        "transitions" => [
            'activation' => STATE_ACTIVATION_ID,
            'nextPhase' => STATE_GAME_NEXT_PHASE_ID,
        ],
    ],
    STATE_ACTIVATION_ID => [
        "name" => STATE_ACTIVATION,
        "description" => clienttranslate('All players must activate their island, climate and tableau'),
        "descriptionmyturn" => clienttranslate('${you} must activate your island, climate and tableau'),
        "type" => "multipleactiveplayer",
        "args" => "argsEarthDefaultMultiActive",
        "initialprivate" => STATE_ACTIVATION_CHOOSE_BOARD_OR_TABLEAU_ID,
        "action" => 'stActivation',
        "possibleactions" => [
            'eventPlay',
            'convertPlay',
        ],
        "transitions" => [
            '' => STATE_GAME_NEXT_PHASE_ID,
        ],
    ],
    STATE_ACTIVATION_CHOOSE_BOARD_OR_TABLEAU_ID => [
        "name" => STATE_ACTIVATION_CHOOSE_BOARD_OR_TABLEAU,
        "description" => clienttranslate('Other players must play'),
        "descriptionmyturn" => clienttranslate('${you} must choose activation order for ${mainActionName} action ${mainActionId}'),
        "type" => "private",
        "args" => "argsEarthDefault",
        "possibleactions" => [
            'activationChooseActivationDirection',
            'eventPlay',
            'convertPlay',
        ],
        "transitions" => [
            'activateOrSkip' => STATE_ACTIVATION_CHOOSE_ACTIVATE_OR_SKIP_ID,
        ],
    ],
    STATE_ACTIVATION_CHOOSE_ACTIVATE_OR_SKIP_ID => [
        "name" => STATE_ACTIVATION_CHOOSE_ACTIVATE_OR_SKIP,
        "description" => clienttranslate('Other players must play'),
        "descriptionmyturn" => clienttranslate('${you} must choose to activate or skip card'),
        "type" => "private",
        "args" => "argsActivationChooseActivateOrSkip",
        "possibleactions" => [
            'activationActivateCard',
            'activationSkipCard',
            'eventPlay',
            'convertPlay',
        ],
        "transitions" => [
            'activateCopyCard' => STATE_ACTIVATION_CHOOSE_CARD_TO_COPY_ID,
            'activateSelectPayment' => STATE_ACTIVATION_SELECT_PAYMENT_ID,
            'activateSelectGain' => STATE_ACTIVATION_SELECT_GAIN_ID,
            'nextCard' => STATE_ACTIVATION_CHOOSE_ACTIVATE_OR_SKIP_ID,
            'confirmEndPhase' => STATE_CONFIRM_END_PHASE_ID,
        ],
    ],
    STATE_ACTIVATION_CHOOSE_CARD_TO_COPY_ID => [
        "name" => STATE_ACTIVATION_CHOOSE_CARD_TO_COPY,
        "description" => clienttranslate('Other players must play'),
        "descriptionmyturn" => clienttranslate('${you} must choose a card to copy'),
        "type" => "private",
        "args" => "argsActivationChooseCardToCopy",
        "possibleactions" => [
            'activationSelectCardToCopy',
        ],
        "transitions" => [
            'activateSelectPayment' => STATE_ACTIVATION_SELECT_PAYMENT_ID,
            'activateSelectGain' => STATE_ACTIVATION_SELECT_GAIN_ID,
            'nextCard' => STATE_ACTIVATION_CHOOSE_ACTIVATE_OR_SKIP_ID,
            'confirmEndPhase' => STATE_CONFIRM_END_PHASE_ID,
        ],
    ],
    STATE_ACTIVATION_SELECT_PAYMENT_ID => [
        "name" => STATE_ACTIVATION_SELECT_PAYMENT,
        "description" => clienttranslate('Other players must play'),
        "descriptionmyturn" => clienttranslate('${you} must pay ${payList} to activate ${mainActionId}'),
        "type" => "private",
        "args" => "argsAbilityPayment",
        "possibleactions" => [
            'activationPay',
            'convertPlay',
        ],
        "transitions" => [
            'activateSelectGain' => STATE_ACTIVATION_SELECT_GAIN_ID,
            'nextCard' => STATE_ACTIVATION_CHOOSE_ACTIVATE_OR_SKIP_ID,
            'confirmEndPhase' => STATE_CONFIRM_END_PHASE_ID,
        ],
    ],
    STATE_ACTIVATION_SELECT_GAIN_ID => [
        "name" => STATE_ACTIVATION_SELECT_GAIN,
        "description" => clienttranslate('Other players must play'),
        "descriptionmyturn" => clienttranslate('${you} can gain up to ${gainList} for activating ${mainActionId}'),
        "type" => "private",
        "args" => "argsAbilityGain",
        "possibleactions" => [
            'activationGain',
        ],
        "transitions" => [
            'nextCard' => STATE_ACTIVATION_CHOOSE_ACTIVATE_OR_SKIP_ID,
            'confirmEndPhase' => STATE_CONFIRM_END_PHASE_ID,
        ],
    ],

    // Event
    STATE_EVENT_CHOOSE_CARD_ID => [
        "name" => STATE_EVENT_CHOOSE_CARD,
        "description" => clienttranslate('Other players must play'),
        "descriptionmyturn" => clienttranslate('${you} must choose the event to play'),
        "type" => "private",
        "args" => "argsEventChooseCard",
        "possibleactions" => [
            'eventChooseCard',
            'convertPlay',
        ],
        "transitions" => [
            'eventSelectPayment' => STATE_EVENT_SELECT_PAYMENT_ID,
            'eventSelectGain' => STATE_EVENT_SELECT_GAIN_ID,
        ],
    ],
    STATE_EVENT_SELECT_PAYMENT_ID => [
        "name" => STATE_EVENT_SELECT_PAYMENT,
        "description" => clienttranslate('Other players must play'),
        "descriptionmyturn" => clienttranslate('${you} must pay ${payList} to play the event'),
        "type" => "private",
        "args" => "argsAbilityPayment",
        "possibleactions" => [
            'eventPay',
            'convertPlay',
        ],
        "transitions" => [
            'eventSelectGain' => STATE_EVENT_SELECT_GAIN_ID,
        ],
    ],
    STATE_EVENT_SELECT_GAIN_ID => [
        "name" => STATE_EVENT_SELECT_GAIN,
        "description" => clienttranslate('Other players must play'),
        "descriptionmyturn" => clienttranslate('${you} can gain up to ${gainList} for playing the event'),
        "type" => "private",
        "args" => "argsAbilityGain",
        "possibleactions" => [
            'eventGain',
        ],
        "transitions" => [],
    ],

    // Conversion
    STATE_CONVERT_SELECT_PAYMENT_ID => [
        "name" => STATE_CONVERT_SELECT_PAYMENT,
        "description" => clienttranslate('Other players must play'),
        "descriptionmyturn" => clienttranslate('${you} must pay 3, 6, 9... ${sproutIcon} for 2, 4, 6... ${soilIcon}'),
        "type" => "private",
        "args" => "argsConvertPayment",
        "possibleactions" => [
            'convertSelectPayment',
        ],
        "transitions" => [],
    ],

    // Other
    STATE_CONFIRM_END_PHASE_ID => [
        "name" => STATE_CONFIRM_END_PHASE,
        "description" => clienttranslate('Other players must play'),
        "descriptionmyturn" => clienttranslate('${you} must end your current phase'),
        "type" => "private",
        "args" => "argsEarthDefault",
        "possibleactions" => [
            'eventPlay',
            'convertPlay',
            'confirmEndPhase',
        ],
        "transitions" => [],
    ],

    STATE_GAME_ENDING_LAST_CHANCE_ID => [
        "name" => STATE_GAME_ENDING_LAST_CHANCE,
        "description" => clienttranslate('The game has ended. Other players have a last chance to play events or conversions'),
        "descriptionmyturn" => clienttranslate('The game has ended. Last chance to play events or conversions'),
        "type" => "multipleactiveplayer",
        "args" => "argsEarthDefaultMultiActive",
        "initialprivate" => STATE_GAME_ENDING_LAST_CHANCE_CONFIRM_ID,
        "action" => 'stGameEndingLastChance',
        "possibleactions" => [
            'eventPlay',
            'convertPlay',
        ],
        "transitions" => [
            '' => STATE_GAME_ENDING_SCORE_ID,
        ],
    ],

    STATE_GAME_ENDING_LAST_CHANCE_CONFIRM_ID => [
        "name" => STATE_GAME_ENDING_LAST_CHANCE_CONFIRM,
        "description" => clienttranslate('The game has ended. Other players have a last chance to play events or conversions'),
        "descriptionmyturn" => clienttranslate('The game has ended. Last chance to play events or conversions'),
        "type" => "private",
        "args" => "argsEarthDefault",
        "possibleactions" => [
            'eventPlay',
            'convertPlay',
            'confirmEndGame',
        ],
        "transitions" => [],
    ],

    STATE_GAME_ENDING_SCORE_ID => [
        "name" => STATE_GAME_ENDING_SCORE,
        "description" => clienttranslate('End game scoring'),
        "type" => "game",
        "action" => 'stGameEndingScore',
        "updateGameProgression" => true,
        "transitions" => [
            '' => STATE_GAME_END_ID,
        ],
    ],

    // Final state.
    // Please do not modify (and do not overload action/args methods).
    STATE_GAME_END_ID => [
        "name" => STATE_GAME_END,
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    ],
];
