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

namespace EA;

require_once('Ability.php');

const CARD_TYPE_TREE = 1;
const CARD_TYPE_HERB = 2;
const CARD_TYPE_MUSHROOM = 4;
const CARD_TYPE_BUSH = 8;
const CARD_TYPE_JOKER = (CARD_TYPE_TREE | CARD_TYPE_HERB | CARD_TYPE_MUSHROOM | CARD_TYPE_BUSH);
const CARD_TYPE_TREE_BUSH = (CARD_TYPE_TREE | CARD_TYPE_BUSH);
const CARD_TYPE_TERRAIN = 16;
const CARD_TYPE_EVENT = 32;
const CARD_TYPE_ISLAND = 64;
const CARD_TYPE_CLIMATE = 128;
const CARD_TYPE_ECOSYSTEM = 256;
const CARD_TYPE_FAUNA = 512;
const CARD_TYPE_GAIA = 1024;
const CARD_TYPES_FOR_JOKER = [
    CARD_TYPE_TREE,
    CARD_TYPE_HERB,
    CARD_TYPE_MUSHROOM,
    CARD_TYPE_BUSH,
];
const CARD_TYPES_FOR_TREE_BUSH = [
    CARD_TYPE_TREE,
    CARD_TYPE_BUSH,
];

const BASE_CARD_ID_EARTH = 10000;
const BASE_CARD_ID_ISLAND = 40000;
const BASE_CARD_ID_CLIMATE = 50000;
const BASE_CARD_ID_ECOSYSTEM = 60000;
const BASE_CARD_ID_FAUNA = 70000;
const BASE_CARD_ID_GAIA = 80000;

const CARD_HABITAT_SUNNY = 0;
const CARD_HABITAT_WET = 1;
const CARD_HABITAT_ROCKY = 2;
const CARD_HABITAT_COLD = 3;
const CARD_HABITATS = [
    CARD_HABITAT_SUNNY,
    CARD_HABITAT_WET,
    CARD_HABITAT_ROCKY,
    CARD_HABITAT_COLD,
];

const GERMINATE_SOIL_3_OR_LESS = 1;
const GERMINATE_SOIL_4_OR_MORE = 2;
const GERMINATE_SCORE_3_OR_LESS = 3;
const GERMINATE_SCORE_4_OR_MORE = 4;
const GERMINATE_SCORE_EVEN = 5;
const GERMINATE_SCORE_ODD = 6;
const GERMINATE_SPROUT_SPACE_3_OR_LESS = 7;
const GERMINATE_SPROUT_SPACE_EXACTLY_6 = 8;
const GERMINATE_HABITAT_SUNNY = 9;
const GERMINATE_HABITAT_WET = 10;
const GERMINATE_HABITAT_ROCKY = 11;
const GERMINATE_HABITAT_COLD = 12;
const GERMINATE_CARD_TYPE_TREE = 13;
const GERMINATE_CARD_TYPE_HERB = 14;
const GERMINATE_CARD_TYPE_MUSHROOM = 15;
const GERMINATE_CARD_TYPE_BUSH = 16;
const GERMINATE_CARD_TYPE_TERRAIN = 17;
const GERMINATE_CARD_TYPE_EVENT = 18;
const GERMINATE_HABITAT_1_OR_LESS = 19;
const GERMINATE_HABITAT_2_OR_MORE = 20;
const GERMINATE_GROWTH_SCORE_4_OR_LESS = 21;
const GERMINATE_GROWTH_SCORE_5_OR_MORE = 22;
const GERMINATE_GROWTH_CAPACITY_2_OR_LESS = 23;
const GERMINATE_GROWTH_CAPACITY_4_OR_MORE = 24;
const GERMINATE_ABILITY_COLOR_RED = 25;
const GERMINATE_ABILITY_COLOR_YELLOW = 26;
const GERMINATE_ABILITY_COLOR_BLUE = 27;
const GERMINATE_ABILITY_COLOR_MULTICOLOR = 28;
const GERMINATE_ABILITY_COLOR_GREEN = 29;
const GERMINATE_ABILITY_COLOR_BROWN = 30;
const GERMINATE_ABILITY_COLOR_BLACK = 31;
const GERMINATE_ABILITY_2 = 32;
const GERMINATE_DIRECTIONAL_AID = 33;
const GERMINATE_CARD_NAME_IS_BOLD = 34;
const GERMINATE_CARD_NAME_IS_ITALIC = 35;
const GERMINATE_CARD_NAME_IS_UNDERLINE = 36;
const GERMINATE_CARD_ABILITY_ICON_GROWTH = 37;
const GERMINATE_CARD_ABILITY_ICON_SPROUT = 38;
const GERMINATE_CARD_ABILITY_ICON_SOIL = 39;
const GERMINATE_CARD_ABILITY_ICON_COMPOST = 40;
const GERMINATE_CARD_ABILITY_ICON_DRAW = 41;
const GERMINATE_CARD_ABILITY_ICON_COLON = 42;

class CardDef
{
    public $id;
    public $type;
    public $name;
    public $scienceName;
    public $soil;
    public $score;
    public $growthMax;
    public $growthScore;
    public $sproutMax;
    public $isHabitatSunny;
    public $isHabitatWet;
    public $isHabitatRocky;
    public $isHabitatCold;
    public $isBoldGeography;
    public $isItalicColor;
    public $isUnderlineAnimal;
    public $abilities;
    public $isExpansionAbundance;
    public $isEndTurn;
    public $germinateIds;

    public function __construct()
    {
        $this->isHabitatSunny = false;
        $this->isHabitatWet = false;
        $this->isHabitatRocky = false;
        $this->isHabitatCold = false;
        $this->isBoldGeography = false;
        $this->isItalicColor = false;
        $this->isUnderlineAnimal = false;
        $this->abilities = [];
        $this->isExpansionAbundance = false;
        $this->isEndTurn = false;
        $this->germinateIds = [];
    }

    public function isFlora()
    {
        return (($this->type & CARD_TYPE_JOKER) != 0);
    }

    public function isTerrain()
    {
        return ($this->type == CARD_TYPE_TERRAIN);
    }

    public function isEvent()
    {
        return ($this->type == CARD_TYPE_EVENT);
    }

    public function isAnytimeEvent()
    {
        return ($this->isEvent() && !$this->isEndTurn);
    }

    public function isEndTurnEvent()
    {
        return ($this->isEvent() && $this->isEndTurn);
    }

    public function isEarth()
    {
        return ($this->isFlora() || $this->isTerrain() || $this->isEvent());
    }

    public function isPlantable()
    {
        return ($this->isFlora() || $this->isTerrain());
    }

    public function isIsland()
    {
        return ($this->type == CARD_TYPE_ISLAND);
    }

    public function isClimate()
    {
        return ($this->type == CARD_TYPE_CLIMATE);
    }

    public function isEcosystem()
    {
        return ($this->type == CARD_TYPE_ECOSYSTEM);
    }

    public function isFauna()
    {
        return ($this->type == CARD_TYPE_FAUNA);
    }

    public function isGaia()
    {
        return ($this->type == CARD_TYPE_GAIA);
    }

    public function isFront()
    {
        if ($this->isIsland() || $this->isClimate() || $this->isEcosystem() || $this->isFauna()) {
            if (substr(strval($this->id), -1) == '2') {
                return false;
            }
        }
        return true;
    }

    public function isBack()
    {
        return !($this->isFront());
    }

    public function otherSideCardId()
    {
        if ($this->isIsland() || $this->isClimate() || $this->isEcosystem() || $this->isFauna()) {
            if ($this->isFront()) {
                return $this->id + 1;
            } else {
                return $this->id - 1;
            }
        }
        return null;
    }

    public static function mainActionToAbilityColor(int $mainActionId)
    {
        switch ($mainActionId) {
            case MAIN_ACTION_ID_PLANT:
                return AB_COLOR_GREEN;
            case MAIN_ACTION_ID_COMPOST:
                return AB_COLOR_RED;
            case MAIN_ACTION_ID_WATER:
                return AB_COLOR_BLUE;
            case MAIN_ACTION_ID_GROW:
                return AB_COLOR_YELLOW;
            default:
                throw  new \BgaSystemException("BUG! Invalid mainActionId: $mainActionId");
        }
    }

    public static function abilityColorToMainAction(int $abilityColor)
    {
        switch ($abilityColor) {
            case AB_COLOR_GREEN:
                return MAIN_ACTION_ID_PLANT;
            case AB_COLOR_RED:
                return MAIN_ACTION_ID_COMPOST;
            case AB_COLOR_BLUE:
                return MAIN_ACTION_ID_WATER;
            case AB_COLOR_YELLOW:
                return MAIN_ACTION_ID_GROW;
            default:
                throw  new \BgaSystemException("BUG! Invalid abilityColor: $abilityColor");
        }
    }

    public function activateForMainAction(int $mainActionId)
    {
        $abilityColor = self::mainActionToAbilityColor($mainActionId);
        if ($this->getAbilityMatchingColor($abilityColor) === null) {
            return false;
        }
        return true;
    }

    public function getAbilityForColor(int $abilityColor)
    {
        foreach ($this->abilities as $ability) {
            if ($ability->color == $abilityColor) {
                return $ability;
            }
        }
        return null;
    }

    public function getAbilityMatchingColor(int $abilityColor)
    {
        foreach ($this->abilities as $ability) {
            if (($ability->color & $abilityColor) != 0) {
                return $ability;
            }
        }
        return null;
    }

    public function hasAbilityMatchingColor(int $abilityColor)
    {
        return ($this->getAbilityMatchingColor($abilityColor) !== null);
    }

    public function getAbilityMatchingMainAction(int $mainActionId)
    {
        $abilityColor = self::mainActionToAbilityColor($mainActionId);
        foreach ($this->abilities as $ability) {
            if (($ability->color & $abilityColor) != 0) {
                return $ability;
            }
        }
        return null;
    }

    public function abilityBlack()
    {
        return $this->getAbilityForColor(AB_COLOR_BLACK);
    }

    public function abilityBrown()
    {
        return $this->getAbilityForColor(AB_COLOR_BROWN);
    }

    public function getFirstAbility()
    {
        foreach ($this->abilities as $ability) {
            return $ability;
        }
        return null;
    }

    public function getAllAbilities()
    {
        return $this->abilities;
    }

    public function hasCardType(int $cardType)
    {
        return (($this->type & $cardType) != 0);
    }

    public function hasHabitat(int $habitat)
    {
        switch ($habitat) {
            case CARD_HABITAT_SUNNY:
                return $this->isHabitatSunny;
            case CARD_HABITAT_WET:
                return $this->isHabitatWet;
            case CARD_HABITAT_ROCKY:
                return $this->isHabitatRocky;
            case CARD_HABITAT_COLD:
                return $this->isHabitatCold;
        }
        throw new \BgaSystemException("BUG! Unknown habitat: $habitat");
    }

    public function habitatCount()
    {
        $count = 0;
        if ($this->isHabitatSunny) {
            $count += 1;
        }
        if ($this->isHabitatWet) {
            $count += 1;
        }
        if ($this->isHabitatRocky) {
            $count += 1;
        }
        if ($this->isHabitatCold) {
            $count += 1;
        }
        return $count;
    }

    public function hasGerminateId(int $germinateId)
    {
        if (array_search($germinateId, $this->germinateIds) !== false) {
            return true;
        }
        return false;
    }

    public static function germinateIdToFilter(int $germinateId)
    {
        switch ($germinateId) {
            case GERMINATE_SOIL_3_OR_LESS:
                return fn ($cd) => !$cd->isEvent() && $cd->soil !== null && $cd->soil <= 3;
            case GERMINATE_SOIL_4_OR_MORE:
                return fn ($cd) => !$cd->isEvent() && $cd->soil !== null && $cd->soil >= 4;
            case GERMINATE_SCORE_3_OR_LESS:
                return fn ($cd) => !$cd->isEvent() && $cd->score !== null && $cd->score <= 3;
            case GERMINATE_SCORE_4_OR_MORE:
                return fn ($cd) => !$cd->isEvent() && $cd->score !== null && $cd->score >= 4;
            case GERMINATE_SCORE_EVEN:
                return fn ($cd) => !$cd->isEvent() && $cd->score !== null && ($cd->score % 2) == 0;
            case GERMINATE_SCORE_ODD:
                return fn ($cd) => !$cd->isEvent() && $cd->score !== null && ($cd->score % 2) != 0;
            case GERMINATE_SPROUT_SPACE_3_OR_LESS:
                return fn ($cd) => !$cd->isEvent() && $cd->isFlora() && ($cd->sproutMax === null || $cd->sproutMax <= 3);
            case GERMINATE_SPROUT_SPACE_EXACTLY_6:
                return fn ($cd) => !$cd->isEvent() && $cd->isFlora() && $cd->sproutMax !== null && $cd->sproutMax == 6;
            case GERMINATE_HABITAT_SUNNY:
                return fn ($cd) => !$cd->isEvent() && $cd->isHabitatSunny;
            case GERMINATE_HABITAT_WET:
                return fn ($cd) => !$cd->isEvent() && $cd->isHabitatWet;
            case GERMINATE_HABITAT_ROCKY:
                return fn ($cd) => !$cd->isEvent() && $cd->isHabitatRocky;
            case GERMINATE_HABITAT_COLD:
                return fn ($cd) => !$cd->isEvent() && $cd->isHabitatCold;
            case GERMINATE_CARD_TYPE_TREE:
                return fn ($cd) => !$cd->isEvent() && $cd->hasCardType(CARD_TYPE_TREE);
            case GERMINATE_CARD_TYPE_HERB:
                return fn ($cd) => !$cd->isEvent() && $cd->hasCardType(CARD_TYPE_HERB);
            case GERMINATE_CARD_TYPE_MUSHROOM:
                return fn ($cd) => !$cd->isEvent() && $cd->hasCardType(CARD_TYPE_MUSHROOM);
            case GERMINATE_CARD_TYPE_BUSH:
                return fn ($cd) => !$cd->isEvent() && $cd->hasCardType(CARD_TYPE_BUSH);
            case GERMINATE_CARD_TYPE_TERRAIN:
                return fn ($cd) => !$cd->isEvent() && $cd->isTerrain();
            case GERMINATE_CARD_TYPE_EVENT:
                return fn ($cd) => $cd->isEvent();
            case GERMINATE_HABITAT_1_OR_LESS:
                return fn ($cd) => !$cd->isEvent() && $cd->habitatCount() <= 1;
            case GERMINATE_HABITAT_2_OR_MORE:
                return fn ($cd) => !$cd->isEvent() && $cd->habitatCount() >= 2;
            case GERMINATE_GROWTH_SCORE_4_OR_LESS:
                return fn ($cd) => !$cd->isEvent() && $cd->isFlora() && ($cd->growthScore === null || $cd->growthScore <= 4);
            case GERMINATE_GROWTH_SCORE_5_OR_MORE:
                return fn ($cd) => !$cd->isEvent() && $cd->isFlora() && $cd->growthScore !== null && $cd->growthScore >= 5;
            case GERMINATE_GROWTH_CAPACITY_2_OR_LESS:
                return fn ($cd) => !$cd->isEvent() && $cd->isFlora() && ($cd->growthMax === null || $cd->growthMax <= 2);
            case GERMINATE_GROWTH_CAPACITY_4_OR_MORE:
                return fn ($cd) => !$cd->isEvent() && $cd->isFlora() && $cd->growthMax !== null && $cd->growthMax >= 4;
            case GERMINATE_ABILITY_COLOR_RED:
                return fn ($cd) => !$cd->isEvent() && $cd->hasAbilityMatchingColor(AB_COLOR_RED);
            case GERMINATE_ABILITY_COLOR_YELLOW:
                return fn ($cd) => !$cd->isEvent() && $cd->hasAbilityMatchingColor(AB_COLOR_YELLOW);
            case GERMINATE_ABILITY_COLOR_BLUE:
                return fn ($cd) => !$cd->isEvent() && $cd->hasAbilityMatchingColor(AB_COLOR_BLUE);
            case GERMINATE_ABILITY_COLOR_MULTICOLOR:
                return fn ($cd) => !$cd->isEvent() && $cd->getAbilityForColor(AB_COLOR_MULTICOLOR);
            case GERMINATE_ABILITY_COLOR_GREEN:
                return fn ($cd) => !$cd->isEvent() && $cd->hasAbilityMatchingColor(AB_COLOR_GREEN);
            case GERMINATE_ABILITY_COLOR_BROWN:
                return fn ($cd) => !$cd->isEvent() && $cd->hasAbilityMatchingColor(AB_COLOR_BROWN);
            case GERMINATE_ABILITY_COLOR_BLACK:
                return fn ($cd) => !$cd->isEvent() && $cd->hasAbilityMatchingColor(AB_COLOR_BLACK);
            case GERMINATE_ABILITY_2:
                return fn ($cd) => !$cd->isEvent() && count($cd->getAllAbilities()) >= 2;
            case GERMINATE_DIRECTIONAL_AID:
                return function ($cd) {
                    if ($cd->isEvent()) {
                        return false;
                    }
                    foreach ($cd->getAllAbilities() as $ability) {
                        if ($ability->getDirection() !== null) {
                            return true;
                        }
                    }
                    return false;
                };
            case GERMINATE_CARD_NAME_IS_BOLD:
                return fn ($cd) => !$cd->isEvent() && $cd->isBoldGeography;
            case GERMINATE_CARD_NAME_IS_ITALIC:
                return fn ($cd) => !$cd->isEvent() && $cd->isItalicColor;
            case GERMINATE_CARD_NAME_IS_UNDERLINE:
                return fn ($cd) => !$cd->isEvent() && $cd->isUnderlineAnimal;
            case GERMINATE_CARD_ABILITY_ICON_GROWTH:
                return function ($cd) {
                    if ($cd->isEvent()) {
                        return false;
                    }
                    if ($cd->hasGerminateId(GERMINATE_CARD_ABILITY_ICON_GROWTH)) {
                        return true;
                    }
                    $hasIcon = false;
                    $f = function ($ab, $count) use (&$hasIcon) {
                        $hasIcon = ($hasIcon
                            || $ab == ABILITY_GROWTH
                            || $ab == ABILITY_PLANT_PAY_WITH_GROWTH
                        );
                    };
                    foreach ($cd->getAllAbilities() as $ability) {
                        $ability->foreachPayment($f);
                        if ($hasIcon) {
                            return true;
                        }
                        $ability->foreachGain($f);
                        if ($hasIcon) {
                            return true;
                        }
                    }
                    return false;
                };
            case GERMINATE_CARD_ABILITY_ICON_SPROUT:
                return function ($cd) {
                    if ($cd->isEvent()) {
                        return false;
                    }
                    if ($cd->hasGerminateId(GERMINATE_CARD_ABILITY_ICON_SPROUT)) {
                        return true;
                    }
                    $hasIcon = false;
                    $f = function ($ab, $count) use (&$hasIcon) {
                        $hasIcon = ($hasIcon
                            || $ab == ABILITY_SPROUT
                            || $ab == ABILITY_PLANT_PAY_WITH_SPROUT
                            || $ab == ABILITY_SPROUT_CHOOSE_ONE
                            || $ab == ABILITY_SPROUT_ALL_OTHERS
                        );
                    };
                    foreach ($cd->getAllAbilities() as $ability) {
                        $ability->foreachPayment($f);
                        if ($hasIcon) {
                            return true;
                        }
                        $ability->foreachGain($f);
                        if ($hasIcon) {
                            return true;
                        }
                    }
                    return false;
                };
            case GERMINATE_CARD_ABILITY_ICON_SOIL:
                return function ($cd) {
                    if ($cd->isEvent()) {
                        return false;
                    }
                    if ($cd->hasGerminateId(GERMINATE_CARD_ABILITY_ICON_SOIL)) {
                        return true;
                    }
                    $hasIcon = false;
                    $f = function ($ab, $count) use (&$hasIcon) {
                        $hasIcon = ($hasIcon
                            || $ab == ABILITY_SOIL
                            || $ab == ABILITY_PLANT_PAY_WITH_COMPOST
                            || $ab == ABILITY_PLANT_PAY_WITH_SPROUT
                            || $ab == ABILITY_PLANT_PAY_WITH_GROWTH
                            || $ab == ABILITY_REDUCE_COST_FOR_TYPE
                            || $ab == ABILITY_REDUCE_COST_FOR_HABITAT
                        );
                    };
                    foreach ($cd->getAllAbilities() as $ability) {
                        $ability->foreachPayment($f);
                        if ($hasIcon) {
                            return true;
                        }
                        $ability->foreachGain($f);
                        if ($hasIcon) {
                            return true;
                        }
                    }
                    return false;
                };
            case GERMINATE_CARD_ABILITY_ICON_COMPOST:
                return function ($cd) {
                    if ($cd->isEvent()) {
                        return false;
                    }
                    if ($cd->hasGerminateId(GERMINATE_CARD_ABILITY_ICON_COMPOST)) {
                        return true;
                    }
                    $hasIcon = false;
                    $f = function ($ab, $count) use (&$hasIcon) {
                        $hasIcon = ($hasIcon
                            || $ab == ABILITY_DRAW_CARD_FROM_COMPOST
                            || $ab == ABILITY_COMPOST_FROM_HAND
                            || $ab == ABILITY_COMPOST_FROM_DECK
                            || $ab == ABILITY_COMPOST_DESTROY
                            || $ab == ABILITY_PLANT_PAY_WITH_COMPOST
                            || $ab == ABILITY_PLANT_FREE_COLOR_COMPOST_AND_DRAW
                        );
                    };
                    foreach ($cd->getAllAbilities() as $ability) {
                        $ability->foreachPayment($f);
                        if ($hasIcon) {
                            return true;
                        }
                        $ability->foreachGain($f);
                        if ($hasIcon) {
                            return true;
                        }
                    }
                    return false;
                };
            case GERMINATE_CARD_ABILITY_ICON_DRAW:
                return function ($cd) {
                    if ($cd->isEvent()) {
                        return false;
                    }
                    if ($cd->hasGerminateId(GERMINATE_CARD_ABILITY_ICON_DRAW)) {
                        return true;
                    }
                    $hasIcon = false;
                    $f = function ($ab, $count) use (&$hasIcon) {
                        $hasIcon = ($hasIcon
                            || $ab == ABILITY_DRAW_CARD_FROM_DECK
                            || $ab == ABILITY_PLANT_FREE_COLOR_COMPOST_AND_DRAW
                        );
                    };
                    foreach ($cd->getAllAbilities() as $ability) {
                        $ability->foreachPayment($f);
                        if ($hasIcon) {
                            return true;
                        }
                        $ability->foreachGain($f);
                        if ($hasIcon) {
                            return true;
                        }
                    }
                    return false;
                };
            case GERMINATE_CARD_ABILITY_ICON_COLON:
                return function ($cd) {
                    if ($cd->isEvent()) {
                        return false;
                    }
                    foreach ($cd->getAllAbilities() as $ability) {
                        if ($ability->color == \EA\AB_COLOR_BLACK && $cd->isIsland()) {
                            continue;
                        }
                        if ($ability->hasPayments()) {
                            return true;
                        }
                    }
                    return false;
                };
            default:
                throw new \BgaSystemException("Invalid germinateId: $germinateId");
        }
    }

    public static function germinateIdToText(int $germinateId)
    {
        switch ($germinateId) {
            case GERMINATE_SOIL_3_OR_LESS:
                return clienttranslate('3 or less soil');
            case GERMINATE_SOIL_4_OR_MORE:
                return clienttranslate('4 or more soil');
            case GERMINATE_SCORE_3_OR_LESS:
                return clienttranslate('Score of 3 or less');
            case GERMINATE_SCORE_4_OR_MORE:
                return clienttranslate('Score of 4 or more');
            case GERMINATE_SCORE_EVEN:
                return clienttranslate('Even score');
            case GERMINATE_SCORE_ODD:
                return clienttranslate('Odd score');
            case GERMINATE_SPROUT_SPACE_3_OR_LESS:
                return clienttranslate('3 or less sprout spaces');
            case GERMINATE_SPROUT_SPACE_EXACTLY_6:
                return clienttranslate('Exactly 6 sprout spaces');
            case GERMINATE_HABITAT_SUNNY:
                return clienttranslate('Habitat: Sunny');
            case GERMINATE_HABITAT_WET:
                return clienttranslate('Habitat: Wet');
            case GERMINATE_HABITAT_ROCKY:
                return clienttranslate('Habitat: Rocky');
            case GERMINATE_HABITAT_COLD:
                return clienttranslate('Habitat: Cold');
            case GERMINATE_CARD_TYPE_TREE:
                return clienttranslate('Tree');
            case GERMINATE_CARD_TYPE_HERB:
                return clienttranslate('Herb');
            case GERMINATE_CARD_TYPE_MUSHROOM:
                return clienttranslate('Mushroom');
            case GERMINATE_CARD_TYPE_BUSH:
                return clienttranslate('Bush');
            case GERMINATE_CARD_TYPE_TERRAIN:
                return clienttranslate('Terrain');
            case GERMINATE_CARD_TYPE_EVENT:
                return clienttranslate('Event');
            case GERMINATE_HABITAT_1_OR_LESS:
                return clienttranslate('1 or less habitat');
            case GERMINATE_HABITAT_2_OR_MORE:
                return clienttranslate('2 or more habitat');
            case GERMINATE_GROWTH_SCORE_4_OR_LESS:
                return clienttranslate('Growth score of 4 or less');
            case GERMINATE_GROWTH_SCORE_5_OR_MORE:
                return clienttranslate('Growth score of 5 or more');
            case GERMINATE_GROWTH_CAPACITY_2_OR_LESS:
                return clienttranslate('Growth capacity of 2 or less');
            case GERMINATE_GROWTH_CAPACITY_4_OR_MORE:
                return clienttranslate('Growth capacity of 4 or more');
            case GERMINATE_ABILITY_COLOR_RED:
                return clienttranslate('Ability Color: Red');
            case GERMINATE_ABILITY_COLOR_YELLOW:
                return clienttranslate('Ability Color: Yellow');
            case GERMINATE_ABILITY_COLOR_BLUE:
                return clienttranslate('Ability Color: Blue');
            case GERMINATE_ABILITY_COLOR_MULTICOLOR:
                return clienttranslate('Ability Color: Multicolor');
            case GERMINATE_ABILITY_COLOR_GREEN:
                return clienttranslate('Ability Color: Green');
            case GERMINATE_ABILITY_COLOR_BROWN:
                return clienttranslate('Ability Color: Brown');
            case GERMINATE_ABILITY_COLOR_BLACK:
                return clienttranslate('Ability Color: Black');
            case GERMINATE_ABILITY_2:
                return clienttranslate('2 Abilities');
            case GERMINATE_DIRECTIONAL_AID:
                return clienttranslate('Directional aid');
            case GERMINATE_CARD_NAME_IS_BOLD:
                return clienttranslate('Name has Bold');
            case GERMINATE_CARD_NAME_IS_ITALIC:
                return clienttranslate('Name has Italic');
            case GERMINATE_CARD_NAME_IS_UNDERLINE:
                return clienttranslate('Name has Underline');
            case GERMINATE_CARD_ABILITY_ICON_GROWTH:
                return clienttranslate('Icon: Growth');
            case GERMINATE_CARD_ABILITY_ICON_SPROUT:
                return clienttranslate('Icon: Sprout');
            case GERMINATE_CARD_ABILITY_ICON_SOIL:
                return clienttranslate('Icon: Soil');
            case GERMINATE_CARD_ABILITY_ICON_COMPOST:
                return clienttranslate('Icon: Compost');
            case GERMINATE_CARD_ABILITY_ICON_DRAW:
                return clienttranslate('Icon: Draw');
            case GERMINATE_CARD_ABILITY_ICON_COLON:
                return clienttranslate('Icon: Colon');
            default:
                throw new \BgaSystemException("Invalid germinateId: $germinateId");
        }
    }
}

class CardDefBuilder
{
    private $def;

    public function __construct()
    {
        $this->def = new CardDef();
    }

    public function build()
    {
        return $this->def;
    }

    public function treeId(int $id)
    {
        $this->def->id = BASE_CARD_ID_EARTH + $id;
        $this->def->type = CARD_TYPE_TREE;
        return $this;
    }

    public function herbId(int $id)
    {
        $this->def->id = BASE_CARD_ID_EARTH + $id;
        $this->def->type = CARD_TYPE_HERB;
        return $this;
    }

    public function mushroomId(int $id)
    {
        $this->def->id = BASE_CARD_ID_EARTH + $id;
        $this->def->type = CARD_TYPE_MUSHROOM;
        return $this;
    }

    public function bushId(int $id)
    {
        $this->def->id = BASE_CARD_ID_EARTH + $id;
        $this->def->type = CARD_TYPE_BUSH;
        return $this;
    }

    public function jokerId(int $id)
    {
        $this->def->id = BASE_CARD_ID_EARTH + $id;
        $this->def->type = CARD_TYPE_TREE | CARD_TYPE_HERB | CARD_TYPE_MUSHROOM | CARD_TYPE_BUSH;
        return $this;
    }

    public function bushTreeId(int $id)
    {
        $this->def->id = BASE_CARD_ID_EARTH + $id;
        $this->def->type = CARD_TYPE_TREE | CARD_TYPE_BUSH;
        return $this;
    }

    public function terrainId(int $id)
    {
        $this->def->id = BASE_CARD_ID_EARTH + $id;
        $this->def->type = CARD_TYPE_TERRAIN;
        return $this;
    }

    public function eventId(int $id)
    {
        $this->def->id = BASE_CARD_ID_EARTH + $id;
        $this->def->type = CARD_TYPE_EVENT;
        return $this;
    }

    public function islandId(int $id, int $side)
    {
        $this->def->id = BASE_CARD_ID_ISLAND + $id * 10 + $side;
        $this->def->type = CARD_TYPE_ISLAND;
        return $this;
    }

    public function climateId(int $id, int $side)
    {
        $this->def->id = BASE_CARD_ID_CLIMATE + $id * 10 + $side;
        $this->def->type = CARD_TYPE_CLIMATE;
        return $this;
    }

    public function ecosystemId(int $id, int $side)
    {
        $this->def->id = BASE_CARD_ID_ECOSYSTEM + $id * 10 + $side;
        $this->def->type = CARD_TYPE_ECOSYSTEM;
        return $this;
    }

    public function faunaId(int $id, int $side)
    {
        $this->def->id = BASE_CARD_ID_FAUNA + $id * 10 + $side;
        $this->def->type = CARD_TYPE_FAUNA;
        return $this;
    }

    public function gaiaId(int $id)
    {
        $this->def->id = BASE_CARD_ID_GAIA + $id;
        $this->def->type = CARD_TYPE_GAIA;
        return $this;
    }

    public function name(string $name, ?string $scienceName = null)
    {
        $this->def->name = $name;
        $this->def->scienceName = $scienceName;
        return $this;
    }

    public function soil(int $soil)
    {
        $this->def->soil = $soil;
        return $this;
    }

    public function score(int $score)
    {
        $this->def->score = $score;
        return $this;
    }

    public function growth(int $max, int $score)
    {
        $this->def->growthMax = $max;
        $this->def->growthScore = $score;
        return $this;
    }

    public function sprout(int $sprout)
    {
        $this->def->sproutMax = $sprout;
        return $this;
    }

    public function sunny()
    {
        $this->def->isHabitatSunny = true;
        return $this;
    }

    public function wet()
    {
        $this->def->isHabitatWet = true;
        return $this;
    }

    public function rocky()
    {
        $this->def->isHabitatRocky = true;
        return $this;
    }

    public function cold()
    {
        $this->def->isHabitatCold = true;
        return $this;
    }

    public function bold()
    {
        $this->def->isBoldGeography = true;
        return $this;
    }

    public function italic()
    {
        $this->def->isItalicColor = true;
        return $this;
    }

    public function underline()
    {
        $this->def->isUnderlineAnimal = true;
        return $this;
    }

    public function ability(\EA\Ability $ability)
    {
        $this->def->abilities[] = $ability;
        if (count($this->def->abilities) > 2) {
            throw new \BgaSystemException('BUG! CardDef cannot have more than 2 abilities');
        }
        return $this;
    }

    public function abundance()
    {
        $this->def->isExpansionAbundance = true;
        return $this;
    }

    public function endTurn()
    {
        $this->def->isEndTurn = true;
        return $this;
    }

    public function germinate(int $germinateId)
    {
        $this->def->germinateIds[] = $germinateId;
        return $this;
    }
}
