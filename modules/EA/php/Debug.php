<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * theisleofcats implementation : © Guillaume Benny bennygui@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */

namespace EA\Debug;

require_once(__DIR__ . '/../../BX/php/Debug.php');
require_once('Score.php');

trait GameStatesTrait
{
    use \BX\Debug\GameStatesTrait;

    public function debugLoadBug(array $studioPlayers = [])
    {
        $this->debugLoadBugInternal($studioPlayers, function ($studioPlayerId, $replacePlayerId) {
            return array_merge(
                $this->debugGetSqlForActionCommand($studioPlayerId, $replacePlayerId),
                [
                    "UPDATE `card` SET player_id = $studioPlayerId WHERE player_id = $replacePlayerId",
                    "UPDATE `leaf_token` SET player_id = $studioPlayerId WHERE player_id = $replacePlayerId",
                    "UPDATE `player_state` SET player_id = $studioPlayerId WHERE player_id = $replacePlayerId",
                    "UPDATE `player_exchange` SET from_player_id = $studioPlayerId WHERE from_player_id = $replacePlayerId",
                    "UPDATE `player_exchange` SET to_player_id = $studioPlayerId WHERE to_player_id = $replacePlayerId",
                    "UPDATE `game_state` SET active_player_id = $studioPlayerId WHERE active_player_id = $replacePlayerId",
                    "UPDATE `player_score` SET player_id = $studioPlayerId WHERE player_id = $replacePlayerId",
                ],
            );
        });
    }

    public function debugGetCard(int $cardId)
    {
        $playerId = $this->getCurrentPlayerId();
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $cardMgr->debugMoveCardToPlayerHandNow($cardId, $playerId);
        $this->debugSendReload();
    }

    public function debugGetAllEvents()
    {
        $playerId = $this->getCurrentPlayerId();
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $cardMgr->debugMoveAllEventsToPlayerHandNow($playerId);
        $this->debugSendReload();
    }

    public function debugGetAllEarthCards()
    {
        $playerId = $this->getCurrentPlayerId();
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $cardMgr->debugMoveAllEarthToPlayerHandNow($playerId);
        $this->debugSendReload();
    }

    public function debugReplaceFauna(int $cardId1, int $cardId2, int $cardId3, int $cardId4)
    {
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $cardMgr->debugReplaceFauna([$cardId1, $cardId2, $cardId3, $cardId4]);
        $this->debugSendReload();
    }

    public function debugGetSoil()
    {
        $playerId = $this->getCurrentPlayerId();
        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \EA\Actions\Ability\GainSoil($playerId, 100, null));
        $creator->save();
    }

    public function debugGetSeed()
    {
        $playerId = $this->getCurrentPlayerId();
        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \EA\Actions\Ability\GainSeed($playerId, 100, null));
        $creator->save();
    }

    public function debugEndGameScoring()
    {
        \BX\Action\ActionRowMgrRegister::getMgr('player')->debugResetScores();
        \BX\Action\ActionRowMgrRegister::getMgr('player_score')->debugDeleteAll();
        $this->endGameScoring();
    }

    public function debugDeleteScoring()
    {
        \BX\Action\ActionRowMgrRegister::getMgr('player')->debugResetScores();
        \BX\Action\ActionRowMgrRegister::getMgr('player_score')->debugDeleteAll();
        $this->debugSendReload();
    }

    public function debugRandomEndGame()
    {
        $playerIdArray = $this->getPlayerIdArray();
        foreach ($playerIdArray as $playerId) {
            \BX\Action\ActionCommandMgr::commit($playerId);
        }

        \BX\Action\ActionRowMgrRegister::getMgr('player_score')->debugDeleteAll();

        \BX\Action\ActionRowMgrRegister::getMgr('card')->debugDeleteAll();
        \BX\Action\ActionRowMgrRegister::getMgr('leaf_token')->debugDeleteAll();
        \BX\Action\ActionRowMgrRegister::getMgr('player_state')->debugDeleteAll();
        \BX\Action\ActionRowMgrRegister::getMgr('game_state')->debugDeleteAll();

        \BX\Action\ActionRowMgrRegister::getMgr('card')->setup($playerIdArray);
        \BX\Action\ActionRowMgrRegister::getMgr('leaf_token')->setup($playerIdArray);
        \BX\Action\ActionRowMgrRegister::getMgr('player_state')->setup($playerIdArray);
        \BX\Action\ActionRowMgrRegister::getMgr('game_state')->setup($playerIdArray);

        \BX\Action\ActionRowMgrRegister::getMgr('card')->debugRandomEndGame($playerIdArray);
        \BX\Action\ActionRowMgrRegister::getMgr('player_state')->debugRandomEndGame();
        \BX\Action\ActionRowMgrRegister::getMgr('game_state')->debugRandomEndGame($playerIdArray);

        $playerIdArray = \BX\Action\ActionRowMgrRegister::getMgr('game_state')->playerIdsInActiveOrder();
        foreach ($playerIdArray as $playerId) {
            $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
            $this->addCommonActions($creator);
            $creator->commit();
        }
        foreach ($playerIdArray as $playerId) {
            \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\GameEnd\LeafTokenBonusLastRound($playerId));
        }
        foreach ($playerIdArray as $playerId) {
            \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\GameEnd\MarkLastRound($playerId));
        }
        foreach ($playerIdArray as $playerId) {
            \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\Fauna\MoveLeafTokenToFinalPosition($playerId));
        }

        \BX\Action\ActionRowMgrRegister::getMgr('leaf_token')->debugShuffePlacedLeafTokens();

        $this->updateEndTurnScores();

        $this->gamestate->jumpToState(STATE_PRE_GAME_ENDING_LAST_CHANCE_ID);

        $this->debugSendReload();
    }
}
