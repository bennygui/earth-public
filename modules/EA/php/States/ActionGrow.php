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

namespace EA\State\ActionGrow;

require_once(__DIR__ . '/.././../../BX/php/Action.php');
require_once(__DIR__ . '/.././../../BX/php/MultiActiveState.php');

const ACTIVE_PLAYER_GAIN_CARD = 4;
const ACTIVE_PLAYER_GAIN_GROWTH = 2;

const INACTIVE_PLAYER_GAIN_CARD = 2;
const INACTIVE_PLAYER_GAIN_GROWTH = 2;

trait GameStatesTrait
{
    public function stActionPreGrow()
    {
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $activePlayerId = $gameStateMgr->activePlayerId();

        if ($activePlayerId != \EA\GAIA_PLAYER_ID) {
            $creator = new \BX\Action\ActionCommandCreatorCommit($activePlayerId);
            $creator->add(new \EA\Actions\Ability\GainDrawCardFromDeck($activePlayerId, ACTIVE_PLAYER_GAIN_CARD));
            $creator->add(new \EA\Actions\Ability\GainGrowth($activePlayerId, ACTIVE_PLAYER_GAIN_GROWTH));
            $this->addCommonActions($creator);
            $creator->commit();
        }

        $this->gamestate->nextState();
    }

    public function stActionGrow()
    {
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $activePlayerId = $gameStateMgr->activePlayerId();

        $this->gamestate->setAllPlayersMultiactive();
        $inactivePlayerIdArray = array_diff($this->getPlayerIdArray(), [$activePlayerId]);
        if ($activePlayerId != \EA\GAIA_PLAYER_ID) {
            $this->gamestate->setPrivateState($activePlayerId, STATE_ACTION_GROW_PLACE_GROWTH_ID);
        }
        $this->gamestate->initializePrivateStateForPlayers($inactivePlayerIdArray);
    }

    public function growActionChooseDrawCard()
    {
        \BX\Lock\Locker::lock();
        $this->checkAction('growActionChooseDrawCard');
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $gameStateMgr->checkInactivePlayerId($playerId);

        $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
        $creator->add(new \EA\Actions\Ability\GainDrawCardFromDeck($playerId, INACTIVE_PLAYER_GAIN_CARD));
        $this->addMainActionMoveToActivation($playerId, $creator);
        $this->addCommonActions($creator);
        $creator->commit();
    }

    public function growActionChoosePlaceGrowth()
    {
        \BX\Lock\Locker::lock();
        $this->checkAction('growActionChoosePlaceGrowth');
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $gameStateMgr->checkInactivePlayerId($playerId);

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \EA\Actions\Ability\GainGrowth($playerId, INACTIVE_PLAYER_GAIN_GROWTH));
        $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'choosePlaceGrowth'));
        $this->addCommonActions($creator);
        $creator->save();
    }

    public function growActionPlaceGrowth($placedGrowthList)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction('growActionPlaceGrowth');
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \EA\Actions\Ability\PlaceGrowth($playerId, $placedGrowthList));
        $this->addMainActionMoveToActivation($playerId, $creator);
        $this->addCommonActions($creator);
        $creator->save();
    }
}
