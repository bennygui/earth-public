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

class PlayerSeenLeafToken extends \BX\Action\BaseActionRow
{
    /** @dbcol @dbkey */
    public $playerSeenLeafTokenId;
    /** @dbcol */
    public $tokenId;
    /** @dbcol */
    public $playerId;

    public function __construct()
    {
        $this->playerSeenLeafTokenId = null;
        $this->tokenId = null;
        $this->playerId = null;
    }
}

class PlayerSeenLeafTokenMgr extends \BX\Action\BaseActionRowMgr
{
    public function __construct()
    {
        parent::__construct('player_seen_leaf_token', \EA\PlayerSeenLeafToken::class);
    }

    public function newFaunaObjectivePlayerIdsForPlayerId(int $playerId)
    {
        $playerMgr = \BX\Action\ActionRowMgrRegister::getMgr('player');
        if (array_search($playerId, $playerMgr->getAllPlayerIds()) === false) {
            return [];
        }
        $leafTokenMgr = \BX\Action\ActionRowMgrRegister::getMgr('leaf_token');
        $seenTokenIds = $this->getSeenTokenIds($playerId);
        $newPlayerIds = [];
        foreach ($leafTokenMgr->getAllOnFaunaBoardFaunaPublic() as $token) {
            if (!array_key_exists($token->tokenId, $seenTokenIds)) {
                $newPlayerIds[$token->playerId] = true;
            }
        }
        return array_keys($newPlayerIds);
    }

    public function updateSeenLeafTokenForPlayerId(int $playerId)
    {
        $leafTokenMgr = \BX\Action\ActionRowMgrRegister::getMgr('leaf_token');
        $seenTokenIds = $this->getSeenTokenIds($playerId);
        $newTokenIds = [];
        foreach ($leafTokenMgr->getAllOnFaunaBoardFaunaPublic() as $token) {
            if (!array_key_exists($token->tokenId, $seenTokenIds)) {
                $newTokenIds[$token->tokenId] = true;
            }
        }
        foreach (array_keys($newTokenIds) as $tokenId) {
            $row = $this->db->newRow();
            $row->tokenId = $tokenId;
            $row->playerId = $playerId;
            $this->db->insertRow($row);
        }
    }

    private function getSeenTokenIds(int $playerId)
    {
        return array_flip(array_map(fn ($t) => $t->tokenId, array_filter($this->db->getAllRows(), fn ($s) => $s->playerId == $playerId)));
    }
}
