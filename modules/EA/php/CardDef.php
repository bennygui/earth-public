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
}
