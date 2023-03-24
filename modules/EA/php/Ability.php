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

// Card abilities
const ABILITY_DRAW_CARD_FROM_DECK = 1;
const ABILITY_DRAW_CARD_FROM_COMPOST = 2;
const ABILITY_GROWTH = 3;
const ABILITY_SOIL = 4;
const ABILITY_SPROUT = 5;
const ABILITY_COMPOST_FROM_HAND = 6;
const ABILITY_COMPOST_FROM_DECK = 7;
const ABILITY_COMPOST_DESTROY = 8;
const ABILITY_PLANT_PAY_WITH_COMPOST = 9;
const ABILITY_PLANT_PAY_WITH_SPROUT = 10;
const ABILITY_PLANT_PAY_WITH_GROWTH = 11;
const ABILITY_COPY_OTHER_ABILITY = 12;
const ABILITY_REDUCE_COST_FOR_TYPE = 13;
const ABILITY_REDUCE_COST_FOR_HABITAT = 14;
const ABILITY_PLANT_FREE_COLOR_COMPOST_AND_DRAW = 15;
const ABILITY_CANNOT_CHOOSE_COLOR = 16;
// Solo
const ABILITY_GAIA_FAUNA_UPPER = 17;
const ABILITY_GAIA_FAUNA_LOWER = 18;

// Conditions for abilities
const AB_COND_IF_CHOOSE_COLOR = 1;
const AB_COND_ADD_TO_TYPE_IN_DIRECTION = 2;
const AB_COND_PER_TYPE = 3;
const AB_COND_PER_HABITAT = 4;
const AB_COND_PER_COLOR = 5;
const AB_COND_WHEN_PLAYING_EVENT = 6;
const AB_COND_PER_EMPTY = 7;

// Directions for abilities
const AB_DIRECTION_COLUMN = 0;
const AB_DIRECTION_ROW = 1;
const AB_DIRECTION_ALL_ADJACENT = 2;
const AB_DIRECTION_ORTHO_ADJACENT = 3;
const AB_DIRECTION_DIAG_ADJACENT = 4;
const AB_DIRECTION_ORTHO_LINE = 5;

// Ability colors
const AB_COLOR_RED = 1;
const AB_COLOR_YELLOW = 2;
const AB_COLOR_BLUE = 4;
const AB_COLOR_MULTICOLOR = AB_COLOR_RED | AB_COLOR_YELLOW | AB_COLOR_BLUE;
const AB_COLOR_GREEN = 8;
const AB_COLOR_BROWN = 16;
const AB_COLOR_BLACK = 32;
const MULTICOLOR_COLORS = [
    AB_COLOR_RED,
    AB_COLOR_BLUE,
    AB_COLOR_YELLOW
];

// Fauna scoring
const AB_FAUNA_FLORA_WITH_PIECES = 0;
const AB_FAUNA_SOIL_COUNT = 1;
const AB_FAUNA_COMPOST_COUNT = 2;
const AB_FAUNA_HAND_COUNT = 3;
const AB_FAUNA_EVENT_COUNT = 4;
const AB_FAUNA_CARDS_WITH_HABITAT = 5;
const AB_FAUNA_FLORA_WITH_TYPE = 6;
const AB_FAUNA_CARDS_WITH_ABILITY_COLOR = 7;
const AB_FAUNA_COLUMNS = 8;
const AB_FAUNA_ROWS = 9;
const AB_FAUNA_DIAGONALS = 10;
const AB_FAUNA_WITH_DIRECTIONS = 11;
const AB_FAUNA_CARDS_WITH_LESS_HABITAT = 12;
const AB_FAUNA_CARDS_WITH_MORE_HABITAT = 13;
const AB_FAUNA_CARDS_WITH_LESS_SCORE = 14;
const AB_FAUNA_CARDS_WITH_MORE_SCORE = 15;
const AB_FAUNA_CARDS_WITH_LESS_COST = 16;
const AB_FAUNA_CARDS_WITH_MORE_COST = 17;
const AB_FAUNA_CARDS_SETS = 18;
const AB_FAUNA_CARDS_WITH_MORE_ABILITY = 19;
const AB_FAUNA_FLORA_WITH_BOLD_GEOGRAPHY = 20;
const AB_FAUNA_FLORA_WITH_ITALIC_COLOR = 21;
const AB_FAUNA_FLORA_WITH_UNDERLINE_ANIMAL = 22;
const AB_FAUNA_FLORA_WITH_EXACT_PIECE_SPOT = 23;
const AB_FAUNA_FLORA_WITH_LESS_PIECE_SPOT = 24;
const AB_FAUNA_FLORA_WITH_MORE_PIECE_SPOT = 25;
const AB_FAUNA_FLORA_FILLED_FIECES = 26;
const AB_FAUNA_FLORA_EMPTY_FIECES = 27;
const AB_FAUNA_CARDS_WITH_EVEN_SCORE = 28;
const AB_FAUNA_CARDS_WITH_ODD_SCORE = 29;

// Scoring abilities
const AB_SCORE_DIRECTION_CONDITION = 0;
const AB_SCORE_ANY_ONE_PLAYER_CONDITION = 1;
const AB_SCORE_WITH_MORE_PIECES = 2;
const AB_SCORE_WITH_LESS_COST = 3;
const AB_SCORE_WITH_MORE_COST = 4;
const AB_SCORE_WITH_LESS_SCORE = 5;
const AB_SCORE_WITH_MORE_SCORE = 6;
const AB_SCORE_REMAINING_SOIL = 7;
const AB_SCORE_REMAINING_CARD_IN_HAND = 8;
const AB_SCORE_CARD_IN_COMPOST = 9;
const AB_SCORE_PER_TERRAIN = 10;

// Ecosystem scoring
const AB_ECO_PER_TOTAL_CARD_TYPE = 0;
const AB_ECO_DIAG_LINE_CARD_TYPE = 1;
const AB_ECO_PER_HABITAT = 2;
const AB_ECO_PER_ABILITY_COLOR = 3;
const AB_ECO_REMAINING_SOIL = 4;
const AB_ECO_REMAINING_CARD_IN_HAND = 5;
const AB_ECO_CARD_IN_COMPOST = 6;
const AB_ECO_CARDS_WITH_LESS_COST = 7;
const AB_ECO_CARDS_WITH_MORE_COST = 8;
const AB_ECO_CARDS_WITH_MORE_SPROUTS = 9;
const AB_ECO_CARDS_WITH_MORE_GROWTH = 10;
const AB_ECO_PER_EVENT = 11;
const AB_ECO_DIRECTION_WITH_DIFFERENT_ABILITY_COLOR = 12;
const AB_ECO_DIRECTION_WITH_SAME_ABILITY_COLOR = 13;
const AB_ECO_CARDS_WITH_LESS_SCORE = 14;
const AB_ECO_CARDS_WITH_MORE_SCORE = 15;
const AB_ECO_CARDS_WITH_DIRECTIONS = 16;
const AB_ECO_DIRECTION_WITH_DIFFERENT_SCORE = 17;
const AB_ECO_CARDS_WITH_LESS_HABITAT = 18;
const AB_ECO_CARDS_WITH_MORE_HABITAT = 19;
const AB_ECO_DIRECTION_WITH_DIFFERENT_SPROUT_COUNT = 20;
const AB_ECO_DIRECTION_WITH_DIFFERENT_GROWTH_COUNT = 21;
const AB_ECO_PER_SPROUT = 22;
const AB_ECO_PER_GROWTH = 23;
const AB_ECO_PER_CARDS_SETS = 24;
const AB_ECO_PER_CARDS_WITH_TWO_ABILITIES = 25;
const AB_ECO_DIRECTION_WITH_DIFFERENT_CARD_TYPE = 26;
const AB_ECO_DIRECTION_WITH_SAME_CARD_TYPE = 27;
const AB_ECO_DIRECTION_WITH_DIFFERENT_HABITAT = 28;
const AB_ECO_DIRECTION_WITH_SAME_HABITAT = 29;
const AB_ECO_PER_CARD_WITH_BOLD_GEOGRAPHY = 30;
const AB_ECO_PER_CARD_WITH_ITALIC_COLOR = 31;
const AB_ECO_PER_CARD_WITH_UNDERLINE_ANIMAL = 32;
const AB_ECO_PER_CARD_WITH_EXACT_PIECE_SPOT = 33;
const AB_ECO_PER_CARD_WITH_LESS_PIECE_SPOT = 34;
const AB_ECO_PER_CARD_WITH_MORE_PIECE_SPOT = 35;
const AB_ECO_PER_CARD_WITH_EVEN_SCORE = 36;
const AB_ECO_PER_CARD_WITH_ODD_SCORE = 37;
const AB_ECO_PER_CARD_WITH_LESS_GROWTH_MAX_SCORE = 38;
const AB_ECO_PER_CARD_WITH_MORE_GROWTH_MAX_SCORE = 39;
const AB_ECO_PER_CARD_WITH_FILLED_FIECES = 40;

class Ability
{
    public $color;
    public $direction;
    public $conditions;
    public $payments;
    public $gains;
    public $scores;
    public $description;
    public $clarification;

    public function __construct()
    {
        $this->color = null;
        $this->direction = null;
        $this->conditions = [];
        $this->payments = [];
        $this->gains = [];
        $this->scores = [];
        $this->description = null;
        $this->clarification = null;
    }

    public function getScores()
    {
        return $this->scores;
    }

    public function getDirection()
    {
        return $this->direction;
    }

    public function getPayment(int $ability)
    {
        foreach ($this->payments as $payment) {
            if ($payment->ability == $ability) {
                return $payment->count;
            }
        }
        throw new \BgaSystemException("BUG! Ability has no payment: $ability");
    }

    public function foreachPayment(callable $callback)
    {
        foreach ($this->payments as $payment) {
            $callback($payment->ability, $payment->count);
        }
    }

    public function getGain(int $ability)
    {
        foreach ($this->gains as $gain) {
            if ($gain->ability == $ability) {
                return $gain->count;
            }
        }
        throw new \BgaSystemException("BUG! Ability has no gain: $ability");
    }

    public function foreachGain(callable $callback)
    {
        foreach ($this->gains as $gain) {
            $callback($gain->ability, $gain->count);
        }
    }

    public function paymentCountCompostFromHand()
    {
        return $this->getPayment(ABILITY_COMPOST_FROM_HAND);
    }

    public function gainDrawCardFromDeck()
    {
        return $this->getGain(ABILITY_DRAW_CARD_FROM_DECK);
    }

    public function gainSoil()
    {
        return $this->getGain(ABILITY_SOIL);
    }

    public function hasCopy()
    {
        foreach ($this->gains as $gain) {
            if ($gain->ability == ABILITY_COPY_OTHER_ABILITY) {
                return true;
            }
        }
        return false;
    }

    public function hasGainSprout()
    {
        foreach ($this->gains as $gain) {
            if ($gain->ability == ABILITY_SPROUT) {
                return true;
            }
        }
        return false;
    }

    public function hasGainGrowth()
    {
        foreach ($this->gains as $gain) {
            if ($gain->ability == ABILITY_GROWTH) {
                return true;
            }
        }
        return false;
    }

    public function hasGaiaFaunaUpper()
    {
        foreach ($this->gains as $gain) {
            if ($gain->ability == ABILITY_GAIA_FAUNA_UPPER) {
                return true;
            }
        }
        return false;
    }

    public function hasUserPlacementGain()
    {
        foreach ($this->gains as $gain) {
            switch ($gain->ability) {
                case ABILITY_DRAW_CARD_FROM_COMPOST:
                case ABILITY_GROWTH:
                case ABILITY_SPROUT:
                case ABILITY_COMPOST_FROM_HAND:
                    return true;
            }
        }
        return false;
    }

    public function hasUserPlacementPayment()
    {
        foreach ($this->payments as $payment) {
            switch ($payment->ability) {
                case ABILITY_GROWTH:
                case ABILITY_SPROUT:
                case ABILITY_COMPOST_FROM_HAND:
                    return true;
            }
        }
        return false;
    }

    public function mustGainCommit()
    {
        foreach ($this->gains as $gain) {
            switch ($gain->ability) {
                case ABILITY_DRAW_CARD_FROM_DECK:
                case ABILITY_DRAW_CARD_FROM_COMPOST:
                case ABILITY_COMPOST_FROM_DECK:
                case ABILITY_COMPOST_DESTROY:
                case ABILITY_PLANT_FREE_COLOR_COMPOST_AND_DRAW:
                    return true;
            }
        }
        return false;
    }

    public function mustPaymentCommit()
    {
        foreach ($this->payments as $payment) {
            switch ($payment->ability) {
                case ABILITY_COMPOST_DESTROY:
                    return true;
            }
        }
        return false;
    }

    public function getIfChooseColorCondition()
    {
        foreach ($this->conditions as $cond) {
            if ($cond->condition == AB_COND_IF_CHOOSE_COLOR) {
                return $cond->conditionType;
            }
        }
        return null;
    }

    public function getSpecialPlantingPayment()
    {
        foreach ($this->gains as $gain) {
            switch ($gain->ability) {
                case ABILITY_PLANT_PAY_WITH_COMPOST:
                case ABILITY_PLANT_PAY_WITH_SPROUT:
                case ABILITY_PLANT_PAY_WITH_GROWTH:
                    return $gain->ability;
            }
        }
        return null;
    }

    public function hasSpecialPlantingPayment()
    {
        return ($this->getSpecialPlantingPayment() !== null);
    }

    public function hasCondition()
    {
        return (count($this->conditions) > 0);
    }

    public function hasConditionForCount()
    {
        foreach ($this->conditions as $cond) {
            switch ($cond->condition) {
                case AB_COND_ADD_TO_TYPE_IN_DIRECTION:
                case AB_COND_PER_TYPE:
                case AB_COND_PER_HABITAT:
                case AB_COND_PER_COLOR:
                    return true;
            }
        }
        return false;
    }

    public function foreachCondition(callable $callback)
    {
        foreach ($this->conditions as $cond) {
            $callback($cond->condition, $cond->conditionType);
        }
    }

    public function hasConditionAddToTypeInDirection()
    {
        foreach ($this->conditions as $cond) {
            if ($cond->condition == AB_COND_ADD_TO_TYPE_IN_DIRECTION) {
                return true;
            }
        }
        return false;
    }

    public function getPerTypeCondition()
    {
        foreach ($this->conditions as $cond) {
            if ($cond->condition == AB_COND_PER_TYPE) {
                return $cond->conditionType;
            }
        }
        return null;
    }

    public function getPerHabitatCondition()
    {
        foreach ($this->conditions as $cond) {
            if ($cond->condition == AB_COND_PER_HABITAT) {
                return $cond->conditionType;
            }
        }
        return null;
    }

    public function canPlantOverColor()
    {
        foreach ($this->gains as $gain) {
            if ($gain->ability == ABILITY_PLANT_FREE_COLOR_COMPOST_AND_DRAW) {
                foreach ($this->conditions as $cond) {
                    if ($cond->condition == AB_COND_PER_COLOR) {
                        return $cond->conditionType;
                    }
                }
            }
        }
        return null;
    }

    public function hasCanPlantOver()
    {
        return ($this->canPlantOverColor() !== null);
    }
}

class AbilityCount
{
    public $ability;
    public $count;

    public function __construct(int $ability, int $count)
    {
        $this->ability = $ability;
        $this->count = $count;
    }
}

class AbilityCondition
{
    public $condition;
    public $conditionType;

    public function __construct(int $condition, ?int $conditionType)
    {
        $this->condition = $condition;
        $this->conditionType = $conditionType;
    }
}

class AbilityBuilder
{
    private $ab;

    public function __construct()
    {
        $this->ab = new Ability();
    }

    public function build()
    {
        return $this->ab;
    }

    public function red()
    {
        $this->ab->color = AB_COLOR_RED;
        return $this;
    }

    public function yellow()
    {
        $this->ab->color = AB_COLOR_YELLOW;
        return $this;
    }

    public function blue()
    {
        $this->ab->color = AB_COLOR_BLUE;
        return $this;
    }

    public function multicolor()
    {
        $this->ab->color = AB_COLOR_MULTICOLOR;
        return $this;
    }

    public function green()
    {
        $this->ab->color = AB_COLOR_GREEN;
        return $this;
    }

    public function brown()
    {
        $this->ab->color = AB_COLOR_BROWN;
        return $this;
    }

    public function black()
    {
        $this->ab->color = AB_COLOR_BLACK;
        return $this;
    }

    public function condition(int $condition, ?int $conditionType = null)
    {
        $this->ab->conditions[] = new AbilityCondition($condition, $conditionType);
        return $this;
    }

    public function direction(int $direction)
    {
        $this->ab->direction = $direction;
        return $this;
    }

    public function pay(int $payment, int $count)
    {
        $this->ab->payments[] = new AbilityCount($payment, $count);
        return $this;
    }

    public function gain(int $gain, int $count)
    {
        $this->ab->gains[] = new AbilityCount($gain, $count);
        return $this;
    }

    public function scores(array $scores)
    {
        $this->ab->scores = $scores;
        return $this;
    }

    public function desc(string $description)
    {
        $this->ab->description = $description;
        return $this;
    }

    public function faq(string $clarification)
    {
        $this->ab->clarification = $clarification;
        return $this;
    }
}
