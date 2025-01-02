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

namespace BX\GameBase;

trait GameBaseTrait
{
    protected function initTable()
    {
        parent::initTable();
        \BX\DB\RowMgrRegister::clearAllMgrCache();
        \BX\Action\ActionRowMgrRegister::clearAllMgrCache();
    }

    public function currentPlayerId()
    {
        return $this->getCurrentPlayerId();
    }

    public function _($text)
    {
        return parent::_($text);
    }

    public function getPlayerIdArray()
    {
        $playersInfos = $this->loadPlayersBasicInfos();
        $playerIdArray = array_keys($playersInfos);
        usort($playerIdArray, function ($p1, $p2) use (&$playersInfos) {
            return ($playersInfos[$p1]['player_no'] <=> $playersInfos[$p2]['player_no']);
        });
        return $playerIdArray;
    }

    public function getFirstPlayerId()
    {
        $playerIdArray = $this->getPlayerIdArray();
        if (count($playerIdArray) == 0) {
            return null;
        }
        return $playerIdArray[0];
    }

    public function getPlayerCount()
    {
        return count($this->loadPlayersBasicInfos());
    }
}
