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

namespace EA\State\GameNextPhase;

require_once(__DIR__ . '/../../../BX/php/Action.php');
require_once(__DIR__ . '/../Actions/GameEnd.php');
require_once(__DIR__ . '/../Actions/Gaia.php');

trait GameStatesTrait
{
    public function stGameNextPhase()
    {
        $this->disableSendFaunaProgress();

        $playerMgr = \BX\Action\ActionRowMgrRegister::getMgr('player');
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        // Commit all players
        $playerIdArray = $gameStateMgr->playerIdsInActiveOrder();
        foreach ($playerIdArray as $playerId) {
            \BX\Action\ActionCommandMgr::commit($playerId);
        }


        if ($gameStateMgr->activeGamePhase() == \EA\GAME_PHASE_PLAYER_SETUP) {
            foreach ($playerIdArray as $playerId) {
                \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\PlayerSetup\RevealSetup($playerId));
            }
        }

        // Give extra time
        foreach ($playerIdArray as $playerId) {
            $this->giveExtraTime($playerId);
        }

        $playerStateMgr->resetPlayersActivationNow();

        // Reveal all invisible cards
        foreach ($playerIdArray as $playerId) {
            \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\MainAction\RevealTableau($playerId));
        }
        foreach ($playerIdArray as $playerId) {
            \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\Fauna\RevealPrivateFauna($playerId));
        }
        // Check Fauna once the reveal is completed
        foreach ($playerIdArray as $playerId) {
            $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
            $this->addCommonActions($creator);
            $creator->commit();
        }

        // Detect last round
        $isEndGame = false;
        if (
            isGameSolo()
            && $gameStateMgr->isGaiaTurn()
            && $gameStateMgr->getGaiaDeckShuffle() >= 1
            && $cardMgr->getTopCardFromGaiaDeck() === null
        ) {
            \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\Gaia\LeafTokenBonus($playerIdArray[0]));
            $isEndGame = true;
        }

        foreach ($playerIdArray as $playerId) {
            \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\GameEnd\LeafTokenBonusLastRound($playerId));
        }
        foreach ($playerIdArray as $playerId) {
            \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\GameEnd\MarkLastRound($playerId));
        }

        $prevActiveMainActionId = $gameStateMgr->getActiveMainActionId();
        $prevActivePlayerId = $gameStateMgr->activePlayerId();
        $gameStateMgr->activateNextGamePhase();
        if (isGameSolo()) {
            $this->notifyAllPlayers(
                NTF_UPDATE_ACTIVE,
                '',
                [
                    'activePlayerId' => null,
                    'nextMainActionId' => $gameStateMgr->getActiveMainActionId(),
                ]
            );
        } else {
            $nextActivePlayerId = $gameStateMgr->activePlayerId();
            $nextActivePlayerName = $this->loadPlayersBasicInfos()[$nextActivePlayerId]['player_name'];
            $this->notifyAllPlayers(
                NTF_UPDATE_ACTIVE,
                $prevActivePlayerId == $nextActivePlayerId
                    ?  ''
                    : clienttranslate('${player_name} becomes the active player'),
                [
                    'player_name' => $nextActivePlayerName,
                    'activePlayerId' => $nextActivePlayerId,
                    'nextMainActionId' => $gameStateMgr->getActiveMainActionId(),
                ]
            );
        }
        $phaseIsChooseMainAction = false;
        switch ($gameStateMgr->activeGamePhase()) {
            case \EA\GAME_PHASE_END_GAME:
                $isEndGame = true;
                // Fallthrough
            case \EA\GAME_PHASE_CHOOSE_MAIN_ACTION:
                if ($prevActiveMainActionId !== null && isGameSolo()) {
                    if ($gameStateMgr->isGaiaTurn()) {
                        $this->gaiaEndActiveTurn($playerIdArray[0]);
                        if ($gameStateMgr->getGaiaDeckShuffle() >= 1 && $cardMgr->getTopCardFromGaiaDeck() === null) {
                            \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\Gaia\LeafTokenBonus($playerIdArray[0]));
                            $isEndGame = true;
                        }
                    } else {
                        $this->gaiaInactiveTurn($prevActiveMainActionId, $playerIdArray[0]);
                    }
                    if (!$isEndGame) {
                        // Next solo turn
                        $gameStateMgr->activateNextSoloTurn();
                        $notifText = '';
                        if ($gameStateMgr->isSoloPlayerSecondTurn()) {
                            $notifText = clienttranslate("Beginner Solo: Gaia's turn is skipped");
                        }
                        $this->notifyAllPlayers(
                            NTF_IS_GAIA_TURN,
                            $notifText,
                            [
                                'isGaiaTurn' => $gameStateMgr->isGaiaTurn(),
                            ]
                        );
                        if ($gameStateMgr->isGaiaTurn()) {
                            $this->gaiaStartActiveTurn($playerIdArray[0]);
                        }
                    }
                }
                // Reorder leaf tokens that are not in their final positions (use last turn player order since the active player has changed)
                foreach ($playerIdArray as $playerId) {
                    \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\Fauna\MoveLeafTokenToFinalPosition($playerId));
                }
                $playerStateMgr->resetPlantedCardsNow();
                if ($isEndGame) {
                    $this->gamestate->nextState('gameEndingLastChance');
                    break;
                } else if (!isGameSolo() || !$gameStateMgr->isGaiaTurn()) {
                    $phaseIsChooseMainAction = true;
                    $this->gamestate->nextState('mainAction');
                    break;
                }
                // It's Gaia's turn, select main action
                $gaiaTopCard = $cardMgr->getGaiaDiscardTopCard();
                switch ($gaiaTopCard->getCardDef()->getFirstAbility()->color) {
                    case \EA\AB_COLOR_BLUE:
                        $gameStateMgr->activateMainActionAndExcutePhaseNow(MAIN_ACTION_ID_WATER);
                        break;
                    case \EA\AB_COLOR_YELLOW:
                        $gameStateMgr->activateMainActionAndExcutePhaseNow(MAIN_ACTION_ID_GROW);
                        break;
                    case \EA\AB_COLOR_RED:
                        $gameStateMgr->activateMainActionAndExcutePhaseNow(MAIN_ACTION_ID_COMPOST);
                        break;
                    case \EA\AB_COLOR_GREEN:
                        $gameStateMgr->activateMainActionAndExcutePhaseNow(MAIN_ACTION_ID_PLANT);
                        break;
                    case \EA\AB_COLOR_BROWN:
                        $gameStateMgr->activateMainActionAndExcutePhaseNow(MAIN_ACTION_ID_SOLO_FAUNA);
                        break;
                    default:
                        throw new \BgaSystemException("BUG! Invalid gaia card color");
                }
                // Reset main action leaf token
                $this->resetMainActionLeafToken();
                // Update main action selection
                $this->notifyAllPlayers(
                    NTF_UPDATE_ACTIVE,
                    '',
                    [
                        'activePlayerId' => null,
                        'nextMainActionId' => $gameStateMgr->getActiveMainActionId(),
                    ]
                );
                // Fallthrough in solo when it's Gaia's turn
            case \EA\GAME_PHASE_EXECUTE_MAIN_ACTION:
                $nextMainAction = $gameStateMgr->getActiveMainActionId();
                if ($nextMainAction === null) {
                    throw new \BgaSystemException("BUG! Next main action is null and about to execute main action");
                }
                if (!isGameSolo()) {
                    $this->validateLeafTokenAndMainAction($nextMainAction);
                }
                switch ($nextMainAction) {
                    case MAIN_ACTION_ID_PLANT:
                        $this->gamestate->nextState('actionPlant');
                        break;
                    case MAIN_ACTION_ID_COMPOST:
                        $this->gamestate->nextState('actionCompost');
                        break;
                    case MAIN_ACTION_ID_WATER:
                        $this->gamestate->nextState('actionWater');
                        break;
                    case MAIN_ACTION_ID_GROW:
                        $this->gamestate->nextState('actionGrow');
                        break;
                    case MAIN_ACTION_ID_SOLO_FAUNA:
                        if (!isGameSolo()) {
                            throw new \BgaSystemException("BUG! Cannot go to Solo Fauna main action, not is solo game!");
                        }
                        $this->gamestate->nextState('actionSoloFauna');
                        break;
                    default:
                        throw new \BgaSystemException("BUG! Invalid main action");
                }
                break;
            case \EA\GAME_PHASE_CARD_ACTIVATION:
                $nextMainAction = $gameStateMgr->getActiveMainActionId();
                if ($nextMainAction === null) {
                    throw new \BgaSystemException("BUG! Next main action is null and about to activate actions");
                }
                $this->gamestate->nextState('activation');
                break;
            case \EA\GAME_PHASE_PLAYER_SETUP:
            default:
                throw new \BgaSystemException('BUG! Invalid active game phase');
        }
        if (
            $phaseIsChooseMainAction
            && $gameStateMgr->activePlayerId() ==  $playerMgr->getFirstPlayerId()
            && !$gameStateMgr->isGaiaTurn()
        ) {
            $this->incStat(1, STATS_TABLE_NB_ROUND);
        }

        $this->enableSendFaunaProgress();
        $progress = $this->getFaunaProgressForPlayers($playerIdArray, false);
        $this->notifyAllPlayers(
            NTF_UPDATE_FAUNA_PROGRESS,
            '',
            ['faunaProgress' => $progress]
        );
    }

    private function gaiaInactiveTurn(int $mainActionId, int $playerId)
    {
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $isSoloHarder = (isGameSoloHard() || isGameSoloExpert());
        // It was the player turn, Gaia has gains depending on main action
        switch ($mainActionId) {
            case MAIN_ACTION_ID_PLANT:
                // Nothing do to
                break;
            case MAIN_ACTION_ID_COMPOST:
                $nbToCompost = intdiv($gameStateMgr->getSoloPlayerGainedSoil(), 2);
                if ($isSoloHarder) {
                    $nbToCompost += 5;
                } else {
                    $nbToCompost += 2;
                }
                \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\Gaia\CompostFromDeck($playerId, $nbToCompost));
                break;
            case MAIN_ACTION_ID_WATER:
                $nbSprout = 0;
                if ($isSoloHarder) {
                    $nbSprout += 3;
                }
                foreach ($cardMgr->getPlayerIslandClimateTableauCards($playerId) as $card) {
                    if ($card->getCardDef()->getAbilityMatchingColor(\EA\AB_COLOR_BLUE) !== null) {
                        $nbSprout += 1;
                    }
                }
                \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\Gaia\PlaceSprout($playerId, $nbSprout));
                break;
            case MAIN_ACTION_ID_GROW:
                $nbGrowth = $gameStateMgr->getSoloPlayerGainedCard();
                if ($isSoloHarder) {
                    $nbGrowth += 3;
                }
                \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\Gaia\PlaceGrowth($playerId, $nbGrowth));
                break;
        }
    }

    private function gaiaStartActiveTurn(int $playerId)
    {
        $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
        $creator->add(new \EA\Actions\Gaia\DrawGaiaCard($playerId));
        $creator->commit();
    }

    private function gaiaEndActiveTurn(int $playerId)
    {
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');

        $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
        $gaiaTopCard = $cardMgr->getGaiaDiscardTopCard();
        $ability = $gaiaTopCard->getCardDef()->getFirstAbility();
        switch ($ability->color) {
            case \EA\AB_COLOR_BLUE:
                $nbSprout = 7;
                foreach ($cardMgr->getPlayerIslandClimateTableauCards($playerId) as $card) {
                    if ($card->getCardDef()->getAbilityMatchingColor(\EA\AB_COLOR_BLUE) !== null) {
                        $nbSprout += 1;
                    }
                }
                \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\Gaia\PlaceSprout($playerId, $nbSprout));
                break;
            case \EA\AB_COLOR_YELLOW:
                $nbGrowth = 7 + $gameStateMgr->getSoloPlayerGainedCard();
                \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\Gaia\PlaceGrowth($playerId, $nbGrowth));
                break;
            case \EA\AB_COLOR_RED:
                $nbToCompost = 8;
                \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\Gaia\CompostFromDeck($playerId, $nbToCompost));
                $nbSoil = $gameStateMgr->getSoloPlayerGainedSoil();
                \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\Gaia\GainSoil($playerId, $nbSoil));
                \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\Gaia\CompostFromDeckSoil($playerId));
                break;
            case \EA\AB_COLOR_GREEN:
                \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\Gaia\DrawEarthCard($playerId, 3));
                break;
            case \EA\AB_COLOR_BROWN:
                if (isGameSoloExpert()) {
                    if ($ability->hasGaiaFaunaUpper()) {
                        if ($gameStateMgr->isGaiaLastFaunaLeft()) {
                            \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\Gaia\GainSoil($playerId, 8));
                            \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\Gaia\CompostFromDeckSoil($playerId));
                        } else {
                            \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\Gaia\DrawEarthCard($playerId, 2));
                        }
                    } else {
                        if ($gameStateMgr->isGaiaLastFaunaLeft()) {
                            \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\Gaia\PlaceSprout($playerId, 4));
                        } else {
                            \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\Gaia\PlaceGrowth($playerId, 3));
                        }
                    }
                }
                break;
            default:
                throw new \BgaSystemException("BUG! Invalid gaia card color");
        }
        $creator->commit();
    }

    private function validateLeafTokenAndMainAction(int $mainActionId)
    {
        $leafMgr = \BX\Action\ActionRowMgrRegister::getMgr('leaf_token');
        foreach ($leafMgr->getActionLeafTokenForAllPlayers() as $leafToken) {
            if (!$leafToken->isOnAction($mainActionId)) {
                throw new \BgaSystemException("BUG! Leaf Token is on main action {$leafToken->locationX} but main action is $mainActionId");
            }
        }
    }
}
