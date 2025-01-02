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

namespace EA\State\EndTurn;

require_once(__DIR__ . '/.././../../BX/php/Action.php');
require_once(__DIR__ . '/.././../../BX/php/MultiActiveState.php');
require_once(__DIR__ . '/../Actions/EndTurn.php');

trait GameStatesTrait
{
    public function stPreEndTurn()
    {
        // Commit all players
        $playerIdArray = $this->getPlayerIdArray();
        foreach ($playerIdArray as $playerId) {
            \BX\Action\ActionCommandMgr::commit($playerId);
        }

        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $playerStateMgr->clearAllLastSeenExchangeSproutCountNow();
        $playerStateMgr->clearAllLastSeenEndTurnEventsNow();

        $this->gamestate->nextState();
    }

    public function stEnterEndTurn()
    {
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $playerExchangeMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_exchange');
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $isEndGame = ($gameStateMgr->activeGamePhase() == \EA\GAME_PHASE_END_GAME);

        // Commit all players
        $playerIdArray = $this->getPlayerIdArray();
        foreach ($playerIdArray as $playerId) {
            \BX\Action\ActionCommandMgr::commit($playerId);
        }

        // Events on table
        if (count($cardMgr->getPlayedEndTurnEvenCardsInOrder()) > 0) {
            $this->gamestate->nextState('nextEndTurnEvent');
            return;
        }

        // Check for events in hand
        $considerEvent = false;
        if (!$playerStateMgr->hasAllSameLastSeenEndTurnEvents()) {
            foreach ($playerIdArray as $playerId) {
                if (!$isEndGame && $playerStateMgr->mustSkipEndOfTurn($playerId)) {
                    continue;
                }
                if ($cardMgr->playerHasEndTurnEventInHand($playerId)) {
                    $considerEvent = true;
                    break;
                }
            }
        }
        $playerStateMgr->saveAllLastSeenEndTurnEventsNow();
        if ($considerEvent) {
            $this->gamestate->nextState('endTurn');
            return;
        }

        // Check for sprouts
        $gotoEndTurn = false;
        foreach ($playerIdArray as $playerId) {
            if (!$isEndGame && $playerStateMgr->mustSkipEndOfTurn($playerId)) {
                continue;
            }
            $lastSeenExchangeSproutCount = $playerStateMgr->getLastSeenExchangeSproutCount($playerId);
            if ($isEndGame && $lastSeenExchangeSproutCount === null) {
                $gotoEndTurn = true;
                break;
            }
            $currentExchangeSproutCount = $playerExchangeMgr->getPlayerSproutCount($playerId);
            if ($lastSeenExchangeSproutCount !== null && $lastSeenExchangeSproutCount == $currentExchangeSproutCount) {
                continue;
            }
            if ($currentExchangeSproutCount <= 0) {
                continue;
            }
            if ($cardMgr->getPlayerTotalSproutSpaceCount($playerId) <= 0) {
                continue;
            }
            $gotoEndTurn = true;
        }

        if ($gotoEndTurn) {
            $this->gamestate->nextState('endTurn');
        } else {
            if ($isEndGame) {
                $this->gamestate->nextState('endGame');
            } else {
                $this->gamestate->nextState('nextPhase');
            }
        }
    }

    public function stEndTurn()
    {
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $isEndGame = ($gameStateMgr->activeGamePhase() == \EA\GAME_PHASE_END_GAME);
        if ($isEndGame) {
            $this->gamestate->setAllPlayersMultiactive();
            $this->gamestate->initializePrivateStateForAllActivePlayers();
            return;
        }

        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $playerExchangeMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_exchange');
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');

        $playerIdArray = $this->getPlayerIdArray();
        $activatePlayerIds = [];
        foreach ($playerIdArray as $playerId) {
            if ($playerStateMgr->mustSkipEndOfTurn($playerId)) {
                continue;
            }
            if ($cardMgr->playerHasEndTurnEventInHand($playerId)) {
                $activatePlayerIds[] = $playerId;
                continue;
            }
            if ($playerExchangeMgr->getPlayerSproutCount($playerId) <= 0) {
                continue;
            }
            if ($cardMgr->getPlayerTotalSproutSpaceCount($playerId) <= 0) {
                continue;
            }
            $activatePlayerIds[] = $playerId;
        }
        $this->gamestate->setPlayersMultiactive($activatePlayerIds, null, true /*Exclusive*/);
        $this->gamestate->initializePrivateStateForPlayers($activatePlayerIds);
    }

    public function argsEndTurnChoose(int $playerId)
    {
        return \BX\MultiActiveState\argsMultiActive($playerId, function ($playerId) {
            $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
            $playerExchangeMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_exchange');
            $leafTokenMgr = \BX\Action\ActionRowMgrRegister::getMgr('leaf_token');
            $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
            $isEndGame = ($gameStateMgr->activeGamePhase() == \EA\GAME_PHASE_END_GAME);
            $ret = [
                'isEndGame' => $isEndGame,
                'endOfGameText' => $isEndGame ? clienttranslate('The game has ended. Last chance to play events or conversions.') : '',
                'canPlaceExchangeSprout' => $playerExchangeMgr->getPlayerSproutCount($playerId) > 0
                    && $cardMgr->getPlayerUnusedSproutSpaceCount($playerId) > 0,
                'exchangeSproutCount' => $playerExchangeMgr->getPlayerSproutCount($playerId),
                'canPlayEndTurnEvent' => $cardMgr->playerHasEndTurnEventInHand($playerId),
                'hasSeeds' => $this->playerCanUseSeed($playerId),
                'hasLeafs' => gameHasExpansionAbundance() && count($leafTokenMgr->getDiscardableLeafInOrder($playerId)) > 0,
                'i18n' => ['endOfGameText'],
            ];
            return $this->argsMergeEarthDefault($playerId, $ret);
        });
    }

    public function endTurnPlaceExchangeSprout()
    {
        \BX\Lock\Locker::lock();
        $playerId = $this->getCurrentPlayerId();
        $this->checkAction('endTurnPlaceExchangeSprout');
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $playerExchangeMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_exchange');
        $sproutCount = $playerExchangeMgr->getPlayerSproutCount($playerId);

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(
            new \BX\Action\SendMessage(
                $playerId,
                clienttranslate('${player_name} can place up to ${sproutCount} ${sproutIcon} from their player board'),
                [
                    'sproutCount' => $sproutCount,
                    'sproutIcon' => clienttranslate('sprout(s)'),
                    'i18n' => ['sproutIcon'],
                ]
            )
        );
        $creator->add(new \EA\Actions\Ability\GainSprout($playerId, $sproutCount));
        $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'placeSprout'));
        $creator->save();
    }

    public function endTurnPlaceExchangeSproutGain(array $placedSproutList)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction('endTurnPlaceExchangeSproutGain');
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \EA\Actions\Ability\PlaceSprout($playerId, $placedSproutList, true));
        $this->addCommonActions($creator);
        $this->addCommonActions($creator, true);
        switch ($this->gamestate->state_id()) {
            case STATE_END_TURN_ID:
                $creator->add(new \BX\MultiActiveState\JumpPrivateStateActionCommand($playerId, STATE_END_TURN_CHOOSE_ID));
                break;
            case STATE_END_TURN_EVENT_ID:
                $creator->add(new \BX\MultiActiveState\JumpPrivateStateActionCommand($playerId, STATE_END_TURN_EVENT_CHOOSE_ID));
                break;
            case STATE_GAME_ENDING_LAST_CHANCE_ID:
                $creator->add(new \BX\MultiActiveState\JumpPrivateStateActionCommand($playerId, STATE_GAME_ENDING_LAST_CHANCE_CONFIRM_ID));
                break;
            default:
                throw new \BgaSystemException('BUG! Cannot return from current state');
        }
        $creator->save();
    }

    public function endTurnPass()
    {
        \BX\Lock\Locker::lock();
        $playerId = $this->getCurrentPlayerId();
        $this->checkAction('endTurnPass');
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \EA\Actions\EndTurn\ConfirmDoNotSkipEndTurn($playerId));
        $creator->add(new \EA\Actions\EndTurn\EndTurnPass($playerId));
        $creator->add(new \BX\MultiActiveState\ExitPrivateStateActionCommand($playerId));
        $creator->save();
    }

    public function endTurnPlayEndTurnEvent()
    {
        \BX\Lock\Locker::lock();
        $playerId = $this->getCurrentPlayerId();
        $this->checkAction('endTurnPlayEndTurnEvent');
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        if (!$cardMgr->playerHasEndTurnEventInHand($playerId)) {
            throw new \BgaUserException($this->_('You do not have any end turn event card to play'));
        }

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'endTurnEvent'));
        $creator->save();
    }

    public function argsEndTurnChooseEndTurnEvent(int $playerId)
    {
        return \BX\MultiActiveState\argsMultiActive($playerId, function ($playerId) {
            $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
            $ret['eventCardIds'] = array_keys($cardMgr->getPlayerEndTurnEventHandCards($playerId));
            return $this->argsMergeEarthDefault($playerId, $ret);
        });
    }

    public function endTurnChooseEndTurnEvent(int $cardId)
    {
        \BX\Lock\Locker::lock();
        $playerId = $this->getCurrentPlayerId();
        $this->checkAction('endTurnChooseEndTurnEvent');
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        if ($this->getPlayerCount() == 1) {
            $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
            $creator->add(new \EA\Actions\Event\KeepReturnFromSoloEndTurnEventState($playerId));
            $creator->commit();
            $this->commonPlayEvent(
                $playerId,
                $cardId,
                fn ($creator) => $creator->add(new \EA\Actions\Event\PlayEventCard($playerId, $cardId)),
                function ($creator, $event) use ($playerId) {
                    switch ($event) {
                        case EVENT_COMMON_PAYMENT:
                            $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'eventSelectPayment'));
                            break;
                        case EVENT_COMMON_GAIN:
                            $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'eventSelectGain'));
                            break;
                        case EVENT_COMMON_INSTANT:
                            $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
                            $creator->add(new \BX\MultiActiveState\JumpPrivateStateActionCommand($playerId, $playerStateMgr->getPlayerReturnFromEventStateId($playerId)));
                            break;
                        default:
                            throw new \BgaSystemException("BUG! Invalid event $event");
                    }
                }
            );
        } else {
            $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
            $creator->add(new \EA\Actions\EndTurn\ChooseEndTurnEventCard($playerId, $cardId));
            $creator->add(new \BX\MultiActiveState\ExitPrivateStateActionCommand($playerId));
            $creator->commit();
        }
    }

    public function argsEndTurnEventChoose(int $playerId)
    {
        return \BX\MultiActiveState\argsMultiActive($playerId, function ($playerId) {
            $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
            $playerExchangeMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_exchange');
            $ret = [
                'canPlaceExchangeSprout' => $playerExchangeMgr->getPlayerSproutCount($playerId) > 0
                    && $cardMgr->getPlayerUnusedSproutSpaceCount($playerId) > 0,
                'exchangeSproutCount' => $playerExchangeMgr->getPlayerSproutCount($playerId),
            ];
            return $this->argsMergeEarthDefault($playerId, $ret);
        });
    }

    public function endTurnEventActivate()
    {
        \BX\Lock\Locker::lock();
        $playerId = $this->getCurrentPlayerId();
        $this->checkAction('endTurnEventActivate');
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $cards = $cardMgr->getPlayedEndTurnEvenCardsInOrder();
        if (count($cards) <= 0) {
            throw new \BgaSystemException('BUG! Activating end turn event but no event!');
        }
        $cardId = $cards[0]->cardId;

        $cardDef = \EA\CardDefMgr::getByCardId($cardId);
        if ($cardDef === null) {
            throw new \BgaSystemException("BUG! No card def for cardId $cardId");
        }

        $activateEvent = fn ($creator) => $creator->add(
            new \BX\Action\SendMessage(
                $playerId,
                clienttranslate('${player_name} activates an end turn event: ${cardName}'),
                [
                    'cardName' => $cardDef->name,
                    'i18n' => ['cardName'],
                ]
            )
        );

        $this->commonPlayEvent(
            $playerId,
            $cardId,
            function ($creator) use ($playerId, $activateEvent) {
                $creator->add(new \EA\Actions\EndTurn\KeepReturnFromEndTurnEventState($playerId));
                $activateEvent($creator);
            },
            function ($creator, $event) use ($playerId) {
                switch ($event) {
                    case EVENT_COMMON_PAYMENT:
                        $creator->add(new \BX\MultiActiveState\JumpPrivateStateActionCommand($playerId, STATE_EVENT_SELECT_PAYMENT_ID));
                        break;
                    case EVENT_COMMON_GAIN:
                        $creator->add(new \BX\MultiActiveState\JumpPrivateStateActionCommand($playerId, STATE_EVENT_SELECT_GAIN_ID));
                        break;
                    case EVENT_COMMON_INSTANT:
                        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
                        $creator->add(new \BX\MultiActiveState\JumpPrivateStateActionCommand($playerId, $playerStateMgr->getPlayerReturnFromEventStateId($playerId)));
                        break;
                    default:
                        throw new \BgaSystemException("BUG! Invalid event $event");
                }
            }
        );
    }

    public function endTurnEventPass()
    {
        \BX\Lock\Locker::lock();
        $playerId = $this->getCurrentPlayerId();
        $this->checkAction('endTurnEventPass');
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $cards = $cardMgr->getPlayedEndTurnEvenCardsInOrder();
        if (count($cards) <= 0) {
            throw new \BgaSystemException('BUG! Passing end turn event but no event!');
        }
        $cardId = $cards[0]->cardId;

        $cardDef = \EA\CardDefMgr::getByCardId($cardId);
        if ($cardDef === null) {
            throw new \BgaSystemException("BUG! No card def for cardId $cardId");
        }

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(
            new \BX\Action\SendMessage(
                $playerId,
                clienttranslate('${player_name} passes on activating the end turn event: ${cardName}'),
                [
                    'cardName' => $cardDef->name,
                    'i18n' => ['cardName'],
                ]
            )
        );
        $creator->add(new \BX\MultiActiveState\ExitPrivateStateActionCommand($playerId));
        $creator->save();
    }

    public function stEndTurnEventAfter()
    {
        // Commit all players
        $playerIdArray = $this->getPlayerIdArray();
        foreach ($playerIdArray as $playerId) {
            \BX\Action\ActionCommandMgr::commit($playerId);
        }
        foreach ($playerIdArray as $playerId) {
            \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\EndTurn\ConfirmDoNotSkipEndTurn($playerId));
        }

        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $cards = $cardMgr->getPlayedEndTurnEvenCardsInOrder();
        if (count($cards) <= 0) {
            throw new \BgaSystemException('BUG! After end turn event but no event!');
        }
        $cardId = $cards[0]->cardId;
        $playerId = $cards[0]->playerId;

        $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
        $creator->add(new \EA\Actions\EndTurn\MoveEndTurnEventCardToPlayer($playerId, $cardId));
        $this->addCommonActions($creator);
        $this->addCommonActions($creator, true);
        $creator->commit();

        $this->gamestate->nextState();
    }
}
