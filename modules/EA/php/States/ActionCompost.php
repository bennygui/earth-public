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

namespace EA\State\ActionCompost;

require_once(__DIR__ . '/.././../../BX/php/Action.php');
require_once(__DIR__ . '/.././../../BX/php/MultiActiveState.php');
require_once(__DIR__ . '/../Actions/MainAction.php');

const ACTIVE_PLAYER_GAIN_SOIL = 5;
const ACTIVE_PLAYER_GAIN_COMPOST = 2;

const INACTIVE_PLAYER_GAIN_SOIL = 2;
const INACTIVE_PLAYER_GAIN_COMPOST = 2;

trait GameStatesTrait
{
    public function stActionCompost()
    {
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $activePlayerId = $gameStateMgr->activePlayerId();

        if ($activePlayerId != \EA\GAIA_PLAYER_ID) {
            $creator = new \BX\Action\ActionCommandCreatorCommit($activePlayerId);
            $creator->add(new \EA\Actions\Ability\GainSoil($activePlayerId, ACTIVE_PLAYER_GAIN_SOIL, null, MAIN_ACTION_ID_COMPOST));
            $creator->add(new \EA\Actions\Ability\CompostFromDeck($activePlayerId, ACTIVE_PLAYER_GAIN_COMPOST));
            $this->addCommonActions($creator);
            $creator->commit();
        }

        $this->gamestate->setAllPlayersMultiactive();
        $inactivePlayerIdArray = array_diff($this->getPlayerIdArray(), [$activePlayerId]);
        if ($activePlayerId != \EA\GAIA_PLAYER_ID) {
            if (gameVersionHasFalltroughActivation()) {
                if (\EA\Actions\Activation\MarkActivatingNextCard::playerHasActivatableCards($activePlayerId)) {
                    if (\EA\Actions\Activation\MarkActivatingNextCard::playerMustChooseDirection($activePlayerId)) {
                        $this->gamestate->setPrivateState($activePlayerId, STATE_ACTIVATION_CHOOSE_BOARD_OR_TABLEAU_ID);
                    } else {
                        $creator = new \BX\Action\ActionCommandCreatorCommit($activePlayerId);
                        $creator->add(new \EA\Actions\Activation\ChooseBoardOrTableau($activePlayerId, \EA\ACTIVATION_DIRECTION_ISLAND_CLIMATE_TABLEAU, false));
                        $creator->add(new \EA\Actions\Activation\MarkActivatingNextCard($activePlayerId));
                        $this->addCommonActions($creator);
                        $creator->commit();
                        $this->gamestate->setPrivateState($activePlayerId, STATE_ACTIVATION_CHOOSE_ACTIVATE_OR_SKIP_ID);
                    }
                } else {
                    if ($this->mustGotoConfirmEndPhase($activePlayerId, null)) {
                        $this->gamestate->setPrivateState($activePlayerId, STATE_CONFIRM_END_PHASE_ID);
                    } else {
                        $this->gamestate->setPlayerNonMultiactive($activePlayerId, null);
                    }
                }
            } else {
                if ($this->mustGotoConfirmEndPhase($activePlayerId, null)) {
                    $this->gamestate->setPrivateState($activePlayerId, STATE_CONFIRM_END_PHASE_ID);
                } else {
                    $this->gamestate->setPlayerNonMultiactive($activePlayerId, null);
                }
            }
        }
        $this->gamestate->initializePrivateStateForPlayers($inactivePlayerIdArray);
    }

    public function compostActionChooseGainSoil()
    {
        \BX\Lock\Locker::lock();
        $this->checkAction('compostActionChooseGainSoil');
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $gameStateMgr->checkInactivePlayerId($playerId);

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \EA\Actions\Ability\GainSoil($playerId, INACTIVE_PLAYER_GAIN_SOIL, null, MAIN_ACTION_ID_COMPOST));
        $this->addMainActionMoveToActivation($playerId, $creator);
        $this->addCommonActions($creator);
        $creator->save();
    }

    public function compostActionChooseCompostFromDeck()
    {
        \BX\Lock\Locker::lock();
        $this->checkAction('compostActionChooseCompostFromDeck');
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $gameStateMgr->checkInactivePlayerId($playerId);

        $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
        $creator->add(new \EA\Actions\Ability\CompostFromDeck($playerId, INACTIVE_PLAYER_GAIN_COMPOST));
        $this->addMainActionMoveToActivation($playerId, $creator);
        $this->addCommonActions($creator);
        $creator->commit();
    }
}
