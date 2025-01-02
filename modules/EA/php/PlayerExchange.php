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

class PlayerExchange extends \BX\Action\BaseActionRow
{
    /** @dbcol @dbkey */
    public $playerExchangeId;
    /** @dbcol */
    public $fromPlayerId;
    /** @dbcol */
    public $toPlayerId;
    /** @dbcol */
    public $sproutGive;
    /** @dbcol */
    public $sproutTake;

    public function __construct()
    {
        $this->playerExchangeId = null;
        $this->fromPlayerId = null;
        $this->toPlayerId = null;
        $this->sproutGive = 0;
        $this->sproutTake = 0;
    }
}

class PlayerExchangeMgr extends \BX\Action\BaseActionRowMgr
{
    public function __construct()
    {
        parent::__construct('player_exchange', \EA\PlayerExchange::class);
        $this->db->setTableIsOptional();
    }

    public function setup(array $playerIdArray)
    {
        foreach ($playerIdArray as $fromPlayerId) {
            foreach ($playerIdArray as $toPlayerId) {
                $pe = $this->db->newRow();
                $pe->fromPlayerId = $fromPlayerId;
                $pe->toPlayerId = $toPlayerId;
                $this->db->insertRow($pe);
            }
        }
    }

    public function getAll()
    {
        return array_values($this->getAllRowsByKey());
    }

    public function getBySameFromToPlayerId(int $playerId)
    {
        foreach ($this->getAll() as $pe) {
            if ($pe->fromPlayerId == $playerId && $pe->toPlayerId == $playerId) {
                return $pe;
            }
        }
        return null;
    }

    public function getByFromPlayerIdExceptSame(int $playerId)
    {
        return array_filter($this->getAll(), fn ($pe) => $pe->fromPlayerId == $playerId && $pe->toPlayerId != $playerId);
    }

    public function getByToPlayerIdExceptSame(int $playerId)
    {
        return array_filter($this->getAll(), fn ($pe) => $pe->fromPlayerId != $playerId && $pe->toPlayerId == $playerId);
    }

    public function getByFromPlayerIdToPlayerId(int $fromPlayerId, int $toPlayerId)
    {
        foreach ($this->getAll() as $pe) {
            if ($pe->fromPlayerId == $fromPlayerId && $pe->toPlayerId == $toPlayerId) {
                return $pe;
            }
        }
        return null;
    }

    public function getPlayerSproutCount(int $playerId)
    {
        $count = 0;
        foreach ($this->getAll() as $pe) {
            if ($pe->toPlayerId != $playerId) {
                continue;
            }
            $count += ($pe->sproutGive - $pe->sproutTake);
        }
        return $count;
    }
}
