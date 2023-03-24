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

namespace EA\State\MainAction;

require_once(__DIR__ . '/.././../../BX/php/Action.php');
require_once(__DIR__ . '/.././../../BX/php/MultiActiveState.php');
require_once(__DIR__ . '/../Actions/MainAction.php');

trait GameStatesTrait
{
    public function stMainAction()
    {
        // Reset main action leaf token
        $this->resetMainActionLeafToken();

        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $activePlayerId = $gameStateMgr->activePlayerId();
        $this->gamestate->setPlayersMultiactive([$activePlayerId], null, true /*Exclusive*/);
        $this->gamestate->initializePrivateStateForPlayers([$activePlayerId]);
    }

    public function argsMainActionChoose(int $playerId)
    {
        return \BX\MultiActiveState\argsMultiActive($playerId, function ($playerId) {
            $ret['mainActionIds'] = \EA\Actions\MainAction\Choose::getChoosableMainActionIds($playerId);
            return $this->argsMergeEarthDefault($playerId, $ret);
        });
    }

    public function mainActionChoose(int $mainActionId)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction('mainActionChoose');
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $gameStateMgr->checkActivePlayerId($playerId);
        $this->updateSeenFaunaObjective($playerId);

        switch ($mainActionId) {
            case MAIN_ACTION_ID_PLANT:
                $this->incStat(1, STATS_PLAYER_NB_ACTION_PLANT, $playerId);
                break;
            case MAIN_ACTION_ID_COMPOST:
                $this->incStat(1, STATS_PLAYER_NB_ACTION_COMPOST, $playerId);
                break;
            case MAIN_ACTION_ID_WATER:
                $this->incStat(1, STATS_PLAYER_NB_ACTION_WATER, $playerId);
                break;
            case MAIN_ACTION_ID_GROW:
                $this->incStat(1, STATS_PLAYER_NB_ACTION_GROW, $playerId);
                break;
            // Nothing to do for MAIN_ACTION_ID_SOLO_FAUNA
        }

        \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\MainAction\Choose($playerId, $mainActionId));
        \BX\Action\ActionCommandMgr::saveOneAndCommit(new \BX\MultiActiveState\ExitPrivateStateActionCommand($playerId));
    }
}
