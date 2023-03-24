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

namespace EA\State\ActionWater;

require_once(__DIR__ . '/.././../../BX/php/Action.php');
require_once(__DIR__ . '/.././../../BX/php/MultiActiveState.php');

const ACTIVE_PLAYER_GAIN_SOIL = 2;
const ACTIVE_PLAYER_GAIN_SPROUT = 6;

const INACTIVE_PLAYER_GAIN_SOIL = 2;
const INACTIVE_PLAYER_GAIN_SPROUT = 2;

trait GameStatesTrait
{
    public function stActionPreWater()
    {
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $activePlayerId = $gameStateMgr->activePlayerId();
        if ($activePlayerId != \EA\GAIA_PLAYER_ID) {
            \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\Ability\GainSprout($activePlayerId, ACTIVE_PLAYER_GAIN_SPROUT));
            \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\Ability\GainSoil($activePlayerId, ACTIVE_PLAYER_GAIN_SOIL, null, MAIN_ACTION_ID_WATER));
        }
        $this->gamestate->nextState();
    }

    public function stActionWater()
    {
        \BX\Lock\Locker::lock();
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $activePlayerId = $gameStateMgr->activePlayerId();

        $this->gamestate->setAllPlayersMultiactive();
        $inactivePlayerIdArray = array_diff($this->getPlayerIdArray(), [$activePlayerId]);
        if ($activePlayerId != \EA\GAIA_PLAYER_ID) {
            $this->gamestate->setPrivateState($activePlayerId, STATE_ACTION_WATER_PLACE_SPROUT_ID);
        }
        $this->gamestate->initializePrivateStateForPlayers($inactivePlayerIdArray);
    }

    public function waterActionChooseGainSoil()
    {
        \BX\Lock\Locker::lock();
        $this->checkAction('waterActionChooseGainSoil');
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $gameStateMgr->checkInactivePlayerId($playerId);

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \EA\Actions\Ability\GainSoil($playerId, INACTIVE_PLAYER_GAIN_SOIL, null, MAIN_ACTION_ID_WATER));
        $this->addNextConfirmEndPhaseOrExit($playerId, $creator);
        $this->addCommonActions($creator);
        $creator->save();
    }

    public function waterActionChoosePlaceSprout()
    {
        \BX\Lock\Locker::lock();
        $this->checkAction('waterActionChoosePlaceSprout');
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $gameStateMgr->checkInactivePlayerId($playerId);

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \EA\Actions\Ability\GainSprout($playerId, INACTIVE_PLAYER_GAIN_SPROUT));
        $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'choosePlaceSprout'));
        $this->addCommonActions($creator);
        $creator->save();
    }

    public function waterActionPlaceSprout($placedSproutList)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction('waterActionPlaceSprout');
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \EA\Actions\Ability\PlaceSprout($playerId, $placedSproutList));
        $this->addNextConfirmEndPhaseOrExit($playerId, $creator);
        $this->addCommonActions($creator);
        $creator->save();
    }
}
