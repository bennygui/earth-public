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

require_once(__DIR__ . '/../../BX/php/Action.php');

const LEAF_ID_ACTION = 0;
const LEAF_ID_BONUS_IDS = [1, 2, 3, 4];
const LEAF_ID_TABLEAU_BONUS = 5;

const LEAF_LOCATION_ID_PLAYER_BOARD = 0;
const LEAF_LOCATION_ID_ACTION = 1;
const LEAF_LOCATION_ID_FAUNA_BOARD_FAUNA = 2;
const LEAF_LOCATION_ID_FAUNA_BOARD_TABLEAU_BONUS = 3;
const LEAF_LOCATION_ID_DICARD = 4;
const LEAF_LOCATION_ID_GAIA_ABUNDANCE = 5;

const MAX_FAUNA_LEAFS_OR_FAUNA_BOARD = 5;

class LeafToken extends \BX\Action\BaseActionRow
{
    /** @dbcol @dbkey */
    public $tokenId;
    /** @dbcol */
    public $leafId;
    /** @dbcol */
    public $playerId;
    /** @dbcol */
    public $locationId;
    /** @dbcol */
    public $locationX;
    /** @dbcol */
    public $locationY;
    /** @dbcol */
    public $locationOrder;
    /** @dbcol */
    public $privateLocationId;
    /** @dbcol */
    public $privateLocationX;
    /** @dbcol */
    public $privateLocationY;
    /** @dbcol */
    public $privateLocationOrder;

    public function __construct()
    {
        $this->tokenId = null;
        $this->leafId = null;
        $this->playerId = null;
        $this->locationId = null;
        $this->locationX = null;
        $this->locationY = null;
        $this->locationOrder = null;
        $this->privateLocationId = null;
        $this->privateLocationX = null;
        $this->privateLocationY = null;
        $this->privateLocationOrder = null;
    }

    public function isBonus()
    {
        return (array_search($this->leafId, LEAF_ID_BONUS_IDS) !== false);
    }

    public function moveToDiscard()
    {
        $this->locationId = LEAF_LOCATION_ID_DICARD;
        $this->locationX = null;
        $this->locationY = null;
        $this->locationOrder = null;
        $this->privateLocationId = null;
        $this->privateLocationX = null;
        $this->privateLocationY = null;
        $this->privateLocationOrder = null;
    }

    public function moveToGaiaAbundance()
    {
        $this->locationId = LEAF_LOCATION_ID_GAIA_ABUNDANCE;
        $this->locationX = null;
        $this->locationY = null;
        $this->locationOrder = null;
        $this->privateLocationId = null;
        $this->privateLocationX = null;
        $this->privateLocationY = null;
        $this->privateLocationOrder = null;
    }

    public function moveToPlayerBoard()
    {
        $this->locationId = LEAF_LOCATION_ID_PLAYER_BOARD;
        $this->locationX = $this->leafId;
        $this->locationY = null;
        $this->locationOrder = null;
    }

    public function moveToAction(int $mainAction)
    {
        $this->locationId = LEAF_LOCATION_ID_ACTION;
        $this->locationX = $mainAction;
        $this->locationY = null;
        $this->locationOrder = null;
    }

    public function isOnAction(int $mainAction)
    {
        if (
            $this->locationId == LEAF_LOCATION_ID_ACTION &&
            $this->locationX == $mainAction &&
            $this->locationY === null &&
            $this->locationOrder === null
        ) {
            return true;
        }
        return false;
    }

    public function moveToFaunaBoardFauna(int $x, int $y)
    {
        $this->locationId = LEAF_LOCATION_ID_FAUNA_BOARD_FAUNA;
        $this->locationX = $x;
        $this->locationY = $y;
        $this->locationOrder = null;
    }

    public function moveToFaunaBoardFaunaPrivate(int $x, int $y)
    {
        $this->privateLocationId = LEAF_LOCATION_ID_FAUNA_BOARD_FAUNA;
        $this->privateLocationX = $x;
        $this->privateLocationY = $y;
        $this->privateLocationOrder = null;
    }

    public function moveToFaunaBoardFaunaFinalOrder(int $order)
    {
        $this->locationId = LEAF_LOCATION_ID_FAUNA_BOARD_FAUNA;
        $this->locationOrder = $order;
    }

    public function moveToFaunaBoardTableauBonus()
    {
        $this->locationId = LEAF_LOCATION_ID_FAUNA_BOARD_TABLEAU_BONUS;
        $this->locationX = null;
        $this->locationY = null;
        $this->locationOrder = null;
    }

    public function isOnPlayerBoard()
    {
        return ($this->getLocationId() == LEAF_LOCATION_ID_PLAYER_BOARD);
    }

    public function isOnFaunaBoard()
    {
        return ($this->getLocationId() == LEAF_LOCATION_ID_FAUNA_BOARD_FAUNA
            || $this->getLocationId() == LEAF_LOCATION_ID_FAUNA_BOARD_TABLEAU_BONUS);
    }

    public function isOnFaunaBoardFauna()
    {
        return ($this->getLocationId() == LEAF_LOCATION_ID_FAUNA_BOARD_FAUNA);
    }

    public function isOnFaunaBoardFaunaPublic()
    {
        return ($this->locationId == LEAF_LOCATION_ID_FAUNA_BOARD_FAUNA);
    }

    public function isOnFaunaBoardTableauBonus()
    {
        return ($this->getLocationId() == LEAF_LOCATION_ID_FAUNA_BOARD_TABLEAU_BONUS);
    }

    public function isOnGaiaAbundance()
    {
        return ($this->getLocationId() == LEAF_LOCATION_ID_GAIA_ABUNDANCE);
    }

    private function getLocationId()
    {
        if ($this->privateLocationId !== null) {
            return $this->privateLocationId;
        } else {
            return $this->locationId;
        }
    }

    public function hasPrivateLocation()
    {
        if ($this->privateLocationId !== null) {
            return true;
        } else {
            return false;
        }
    }

    public function moveToPublicLocation()
    {
        if (!$this->hasPrivateLocation()) {
            return;
        }
        $this->locationId = $this->privateLocationId;
        $this->locationX = $this->privateLocationX;
        $this->locationY = $this->privateLocationY;
        $this->locationOrder = $this->privateLocationOrder;

        $this->privateLocationId = null;
        $this->privateLocationX = null;
        $this->privateLocationY = null;
        $this->privateLocationOrder = null;
    }

    public function jsonSerialize()
    {
        throw new \BgaSystemException('BUG! Cannot serialize LeafToken, must use toPlayerUI');
    }

    public function toPlayerUI(int $playerId)
    {
        return new LeafTokenUI($this, $playerId);
    }
}

function leafTokenToPlayerUI($tokens, int $playerId)
{
    if ($tokens === null) {
        return null;
    } else if (is_array($tokens)) {
        return array_map(fn ($t) => $t->toPlayerUI($playerId), $tokens);
    } else {
        return $tokens->toPlayerUI($playerId);
    }
}

class LeafTokenUI extends \BX\UI\UISerializable
{
    public $tokenId;
    public $leafId;
    public $playerId;
    public $locationId;
    public $locationX;
    public $locationY;
    public $locationOrder;

    public function __construct(LeafToken $token, int $playerId)
    {
        $this->tokenId = $token->tokenId;
        $this->leafId = $token->leafId;
        $this->playerId = $token->playerId;
        $this->locationId = $token->locationId;
        $this->locationX = $token->locationX;
        $this->locationY = $token->locationY;
        $this->locationOrder = $token->locationOrder;
        if ($token->playerId == $playerId && $token->privateLocationId !== null) {
            $this->locationId = $token->privateLocationId;
        }
        if ($token->playerId == $playerId && $token->privateLocationX !== null) {
            $this->locationX = $token->privateLocationX;
        }
        if ($token->playerId == $playerId && $token->privateLocationY !== null) {
            $this->locationY = $token->privateLocationY;
        }
        if ($token->playerId == $playerId && $token->privateLocationOrder !== null) {
            $this->locationOrder = $token->privateLocationOrder;
        }
    }
}

class LeafTokenMgr extends \BX\Action\BaseActionRowMgr
{
    public function __construct()
    {
        parent::__construct('leaf_token', \EA\LeafToken::class);
    }

    public function setup(array $playerIdArray)
    {
        if (isGameSolo()) {
            $playerIdArray[] = GAIA_PLAYER_ID;
        }
        $tokenId = 0;
        foreach ($playerIdArray as $playerId) {
            foreach (array_merge([LEAF_ID_ACTION], LEAF_ID_BONUS_IDS, [LEAF_ID_TABLEAU_BONUS]) as $leafId) {
                if ($playerId == GAIA_PLAYER_ID && $leafId == LEAF_ID_ACTION) {
                    continue;
                }
                $t = $this->db->newRow();
                $t->tokenId = $tokenId++;
                $t->leafId = $leafId;
                $t->playerId = $playerId;
                $t->moveToPlayerBoard();
                $this->db->insertRow($t);
            }
        }
    }

    public function getLeafTokenById(int $tokenId)
    {
        return $this->getRowByKey($tokenId);
    }

    public function getLeafTokenByLeafIdAndPlayerId(int $leafId, int $playerId)
    {
        $tokens = array_values(array_filter($this->getAll(), fn ($t) => $t->leafId == $leafId && $t->playerId == $playerId));
        $count = count($tokens);
        switch ($count) {
            case 0:
                return null;
            case 1:
                return $tokens[0];
            default:
                throw new \BgaSystemException("BUG! getLeafTokenByLeafIdAndPlayerId matched $count tokens");
        }
    }

    public function getActionLeafTokenForAllPlayers()
    {
        return array_filter($this->getAll(), fn ($t) => $t->leafId == LEAF_ID_ACTION);
    }

    public function getLeafTokenByPlayerId(int $playerId)
    {
        return array_filter($this->getAll(), fn ($t) => $t->playerId == $playerId);
    }

    public function getTableauBonusLeafTokenForPlayerId(int $playerId)
    {
        return $this->getLeafTokenByLeafIdAndPlayerId(LEAF_ID_TABLEAU_BONUS, $playerId);
    }

    public function getFaunaLeafTokenByPlayerId(int $playerId)
    {
        return array_filter($this->getAll(), fn ($t) => $t->isBonus() && $t->playerId == $playerId);
    }

    public function getFaunaLeafTokenAtFaunaForLocation(int $x, int $y)
    {
        return array_filter(
            $this->getAll(),
            fn ($t) => $t->isBonus() && $t->isOnFaunaBoardFauna() && $t->locationX == $x && $t->locationY == $y
        );
    }

    public function getDiscardableLeafInOrder(int $playerId)
    {
        $leafs = array_values(array_filter($this->getLeafTokenByPlayerId($playerId), fn ($t) => $t->leafId != LEAF_ID_ACTION && $t->isOnPlayerBoard()));
        if (count($leafs) > 0) {
            return $leafs;
        }
        return array_values(array_filter($this->getLeafTokenByPlayerId($playerId), fn ($t) => $t->leafId != LEAF_ID_ACTION && $t->isOnFaunaBoardFauna() && $t->locationOrder === null));
    }

    public function countFaunaLeafOnFaunaFaunaByPlayerId(int $playerId)
    {
        return count(array_filter($this->getAll(), fn ($t) => $t->isBonus() && $t->playerId == $playerId && $t->isOnFaunaBoardFauna()));
    }

    public function hasFaunaBoardTableauBonus()
    {
        foreach ($this->getAll() as $leafToken) {
            if ($leafToken->isOnFaunaBoardTableauBonus()) {
                return true;
            }
        }
        return false;
    }

    public function playerHasFaunaBoardTableauBonus(int $playerId)
    {
        foreach ($this->getAll() as $leafToken) {
            if ($leafToken->isOnFaunaBoardTableauBonus() && $leafToken->playerId == $playerId) {
                return true;
            }
        }
        return false;
    }

    public function getMainActionLeafTokens()
    {
        return array_filter($this->getAll(), fn ($t) => $t->leafId == LEAF_ID_ACTION);
    }

    public function resetMainActionNow()
    {
        $allTokens = $this->getMainActionLeafTokens();
        foreach ($allTokens as $leafToken) {
            $leafToken->moveToPlayerBoard();
            $this->db->updateRow($leafToken);
        }
        return $allTokens;
    }

    public function getAll()
    {
        return $this->getAllRowsByKey();
    }

    public function getAllOnFaunaBoardFaunaPublic()
    {
        return array_filter($this->getAll(), fn ($t) => $t->isOnFaunaBoardFaunaPublic());
    }

    public function getLeafIdFromBoardLocation(int $locationX, int $locationY)
    {
        if ($locationX == 0) {
            if ($locationY == 0) {
                return 1;
            } else if ($locationY == 1) {
                return 2;
            }
            throw new \BgaSystemException("BUG! Invalid location: $locationX and $locationY");
        }
        if ($locationX == 1) {
            if ($locationY == 0) {
                return 3;
            } else if ($locationY == 1) {
                return 4;
            }
            throw new \BgaSystemException("BUG! Invalid location: $locationX and $locationY");
        }
        throw new \BgaSystemException("BUG! Invalid location: $locationX and $locationY");
    }

    public function getLeafTokenCanBeOnFaunaBoardByPositiondAndPlayerId(int $locationX, int $locationY, int $playerId, bool $considerPrivateVisibility = true)
    {
        foreach ($this->getAll() as $t) {
            if ($t->playerId != $playerId) {
                continue;
            }
            if ($considerPrivateVisibility && $t->hasPrivateLocation()) {
                if (
                    $t->privateLocationId != LEAF_LOCATION_ID_FAUNA_BOARD_FAUNA
                    || $t->privateLocationX != $locationX
                    || $t->privateLocationY != $locationY
                ) {
                    continue;
                }
            } else if (
                $t->locationId != LEAF_LOCATION_ID_FAUNA_BOARD_FAUNA
                || $t->locationX != $locationX
                || $t->locationY != $locationY
            ) {
                continue;
            }
            return $t;
        }

        foreach ($this->getFaunaLeafTokenByPlayerId($playerId) as $t) {
            if ($t->isOnPlayerBoard()) {
                return $t;
            }
        }
        $t = $this->getTableauBonusLeafTokenForPlayerId($playerId);
        if ($t->isOnPlayerBoard()) {
            return $t;
        }
        return null;
    }

    public function getLeafTokenCanBeOnTableauBonusByPlayerId(int $playerId)
    {
        $t = $this->getTableauBonusLeafTokenForPlayerId($playerId);
        if ($t->isOnFaunaBoardTableauBonus()) {
            return $t;
        }
        foreach ($this->getFaunaLeafTokenByPlayerId($playerId) as $t) {
            if ($t->isOnFaunaBoardTableauBonus()) {
                return $t;
            }
        }
        $t = $this->getTableauBonusLeafTokenForPlayerId($playerId);
        if ($t->isOnPlayerBoard()) {
            return $t;
        }
        foreach ($this->getFaunaLeafTokenByPlayerId($playerId) as $t) {
            if ($t->isOnPlayerBoard()) {
                return $t;
            }
        }
        return null;
    }

    public function isFaunaPositionFull(int $locationX, int $locationY)
    {
        $count = count(array_filter($this->getAll(), fn ($t) => $t->locationOrder !== null && $t->locationX == $locationX && $locationY == $t->locationY));
        return ($count >= MAX_FAUNA_LEAFS_OR_FAUNA_BOARD);
    }

    public function debugShuffePlacedLeafTokens()
    {
        foreach (LEAF_ID_BONUS_IDS as $id) {
            $tokens = array_values(array_filter($this->getAll(), fn ($t) => $t->leafId == $id && $t->isOnFaunaBoardFauna()));
            shuffle($tokens);
            foreach ($tokens as $i => $t) {
                $t->locationOrder = $i;
                $this->db->updateRow($t);
            }
        }
    }
}
