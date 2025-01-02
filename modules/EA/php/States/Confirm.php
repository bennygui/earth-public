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

namespace EA\State\Confirm;

require_once(__DIR__ . '/.././../../BX/php/Action.php');
require_once(__DIR__ . '/.././../../BX/php/MultiActiveState.php');

trait GameStatesTrait
{
    public function argsConfirmEndPhase(int $playerId)
    {
        return \BX\MultiActiveState\argsMultiActive($playerId, function ($playerId) {
            $ret = [];
            if (!gameHasExpansionAbundance() || isGameSolo()) {
                return $this->argsMergeEarthDefault($playerId, $ret);
            }

            $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
            $mainActionId = $gameStateMgr->getActiveMainActionId();
            $gamePhase = $gameStateMgr->activeGamePhase();
            switch ($gamePhase) {
                case \EA\GAME_PHASE_CARD_ACTIVATION:
                    break;
                case \EA\GAME_PHASE_EXECUTE_MAIN_ACTION:
                    if ($mainActionId != MAIN_ACTION_ID_PLANT) {
                        break;
                    }
                    if (!\EA\Actions\Activation\MarkActivatingNextCard::playerHasActivatableCards($playerId)) {
                        break;
                    }
                    // Fallthrough
                default:
                    return $this->argsMergeEarthDefault($playerId, $ret);
            }

            $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
            $playerExchangeMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_exchange');
            if (
                $cardMgr->playerHasEndTurnEventInHand($playerId)
                ||
                (
                    $playerExchangeMgr->getPlayerSproutCount($playerId) > 0
                    &&
                    $cardMgr->getPlayerTotalSproutSpaceCount($playerId) > 0
                )
            ) {
                $ret['askSkipEndTurn'] = true;
            }
            return $this->argsMergeEarthDefault($playerId, $ret);
        });
    }

    public function confirmEndPhase()
    {
        \BX\Lock\Locker::lock();
        $playerId = $this->getCurrentPlayerId();
        $this->checkAction('confirmEndPhase');
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        // Commit if there are no undo so that there are no undo button
        $creator = \BX\Action\buildActionCommandCreator($playerId, \BX\Action\ActionCommandMgr::count($playerId) == 0);
        $creator->add(new \EA\Actions\EndTurn\ConfirmDoNotSkipEndTurn($playerId));
        $creator->add(new \BX\MultiActiveState\ExitPrivateStateActionCommand($playerId));
        $creator->saveOrCommit();
    }

    public function confirmEndPhaseSkipEndOfTurn()
    {
        \BX\Lock\Locker::lock();
        $playerId = $this->getCurrentPlayerId();
        $this->checkAction('confirmEndPhaseSkipEndOfTurn');
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \EA\Actions\EndTurn\ConfirmSkipEndTurn($playerId));
        $creator->add(new \BX\MultiActiveState\ExitPrivateStateActionCommand($playerId));
        $creator->saveOrCommit();
    }
}
