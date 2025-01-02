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

namespace EA\State\ActionPlant;

require_once(__DIR__ . '/.././../../BX/php/Action.php');
require_once(__DIR__ . '/.././../../BX/php/MultiActiveState.php');

const ACTIVE_PLAYER_DRAW_CARDS = 4;
const INACTIVE_PLAYER_DRAW_CARDS = 1;

trait GameStatesTrait
{
    public function stActionPlant()
    {
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $activePlayerId = $gameStateMgr->activePlayerId();

        $this->gamestate->setAllPlayersMultiactive();
        $inactivePlayerIdArray = array_diff($this->getPlayerIdArray(), [$activePlayerId]);
        if ($activePlayerId != \EA\GAIA_PLAYER_ID) {
            $this->gamestate->setPrivateState($activePlayerId, STATE_ACTION_PLANT_ACTIVE_FIRST_CARD_ID);
        }
        $this->gamestate->initializePrivateStateForPlayers($inactivePlayerIdArray);
    }

    public function argsPlantActionPlant(int $playerId)
    {
        return \BX\MultiActiveState\argsMultiActive($playerId, function ($playerId) {
            $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
            $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');

            $playerSoilCount = $playerStateMgr->getPlayerSoilCount($playerId);
            $plantableCards = $cardMgr->getPlayerHandPlantableCards($playerId, $playerSoilCount);
            $ret['tableauPerCardId'] = [];
            foreach ($plantableCards as $card) {
                $ret['tableauPerCardId'][$card->cardId] = \EA\cardsToCompactUI($cardMgr->getPlayerTableauWithPlacementCardsUI($playerId, $card->cardId));
            }
            $ret['costPerCardId'] = [];
            foreach ($plantableCards as $card) {
                $ret['costPerCardId'][$card->cardId] = $cardMgr->getPlayerCardCost($playerId, $card->cardId);
            }
            return $this->argsMergeEarthDefault($playerId, $ret);
        });
    }

    public function plantActionSkipPlanting()
    {
        \BX\Lock\Locker::lock();
        $this->checkAction('plantActionSkipPlanting');
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $activePlayerId = $gameStateMgr->activePlayerId();

        if ($this->gamestate->state_id() == STATE_ACTION_PLANT_ADDITIONAL_ID) {
            $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
            $creator->add(new \EA\Actions\MainAction\SkipPlantingCard($playerId));
            $this->addCommonActions($creator);
            $this->addNextConfirmEndPhaseOrExit($playerId, $creator);
            $creator->commit();
        } else if ($playerId == $activePlayerId) {
            $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
            $creator->add(new \EA\Actions\MainAction\SkipPlantingCard($playerId));
            $creator->add(new \EA\Actions\Ability\GainDrawCardFromDeck($playerId, ACTIVE_PLAYER_DRAW_CARDS, true));
            $this->addCommonActions($creator);
            $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
            if ($cardMgr->playerHasHandChoosingCards($playerId)) {
                $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'chooseCards'));
            } else {
                $this->addNextConfirmEndPhaseOrExit($playerId, $creator);
            }
            $creator->commit();
        } else {
            $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
            $creator->add(new \EA\Actions\MainAction\SkipPlantingCard($playerId));
            $creator->add(new \EA\Actions\Ability\GainDrawCardFromDeck($playerId, INACTIVE_PLAYER_DRAW_CARDS));
            $this->addCommonActions($creator);
            $this->addNextConfirmEndPhaseOrExit($playerId, $creator);
            $creator->commit();
        }
    }

    public function plantActionPlanCard(int $cardId, int $posX, int $posY)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction('plantActionPlanCard');
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);
        $this->plantActionPlanCardWithPossiblePayement(
            $cardId,
            $posX,
            $posY,
            null,
            null,
            null
        );
    }

    private function plantActionPlanCardWithPossiblePayement(
        int $cardId,
        int $posX,
        int $posY,
        ?array $payedSproutList,
        ?array $payedGrowthList,
        ?array $payedCompostFromHandCardIds
    ) {
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $activePlayerId = $gameStateMgr->activePlayerId();
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $cardDef = \EA\CardDefMgr::getByCardId($cardId);
        if ($cardDef === null) {
            throw new \BgaSystemException("BUG! No card def for cardId $cardId");
        }
        $abilityBlack = $cardDef->abilityBlack();
        if ($abilityBlack !== null && $abilityBlack->hasConditionPerNeighbour()) {
            $abilityBlack = null;
        }
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $card = $cardMgr->getCardById($cardId);

        if (!$card->isInPlayerTableau($playerId) && $abilityBlack !== null && $abilityBlack->hasSpecialPlantingPayment()) {
            // Can pay with soil, sprouts, growth or compost from hand
            $creator = new \BX\Action\ActionCommandCreator($playerId);
            // Note: This type of card cannot be planted over another card
            $creator->add(new \EA\Actions\MainAction\PlantCard($playerId, $cardId, $posX, $posY));
            $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'activateSelectPayment'));
            $creator->save();
            return;
        }

        $willOverrideCard = false;
        $overrideCard = $cardMgr->getPlayerTableauCardAtPos($playerId, $posX, $posY);
        if ($overrideCard !== null && $overrideCard->cardId != $cardId) {
            $willOverrideCard = true;
            $abilityBrown = $overrideCard->getCardDef()->abilityBrown();
            if (
                $abilityBrown === null
                || !$abilityBrown->hasCanPlantOver()
                || !$cardDef->hasAbilityMatchingColor($abilityBrown->canPlantOverColor())
            ) {
                throw new \BgaUserException($this->_('You cannot plant this card over the selected terrain'));
            }
        }

        $isFirstCard = true;
        $specialPaymentAction = null;
        if ($abilityBlack !== null && $abilityBlack->hasSpecialPlantingPayment()) {
            if ($payedSproutList === null || $payedGrowthList === null || $payedCompostFromHandCardIds === null) {
                throw new \BgaSystemException('BUG! Planting with special payment requires payment list');
            }
            switch ($abilityBlack->getSpecialPlantingPayment()) {
                case \EA\ABILITY_PLANT_PAY_WITH_SPROUT:
                    if (count($payedGrowthList) > 0 || count($payedCompostFromHandCardIds) > 0) {
                        throw new \BgaSystemException('BUG! Wrong special payment list');
                    }
                    $specialPaymentAction = new \EA\Actions\Ability\PaySprout($playerId, null, $payedSproutList);
                    break;
                case \EA\ABILITY_PLANT_PAY_WITH_GROWTH:
                    if (count($payedSproutList) > 0 || count($payedCompostFromHandCardIds) > 0) {
                        throw new \BgaSystemException('BUG! Wrong special payment list');
                    }
                    $specialPaymentAction = new \EA\Actions\Ability\PayGrowth($playerId, null, $payedGrowthList);
                    break;
                case \EA\ABILITY_PLANT_PAY_WITH_COMPOST:
                    if (count($payedSproutList) > 0 || count($payedGrowthList) > 0) {
                        throw new \BgaSystemException('BUG! Wrong special payment list');
                    }
                    $specialPaymentAction = new \EA\Actions\Ability\AnyCompostFromHand($playerId, $payedCompostFromHandCardIds);
                    break;
                default:
                    throw new \BgaSystemException('BUG! Unkown special payment');
            }
            if (count($playerStateMgr->playerPlantedCardIds($playerId)) >= 2) {
                $isFirstCard = false;
            }
        } else {
            if (count($playerStateMgr->playerPlantedCardIds($playerId)) >= 1) {
                $isFirstCard = false;
            }
        }

        $doPlant = function ($creator) use ($playerId, $cardId, $cardMgr, $abilityBlack, $posX, $posY, $specialPaymentAction, $overrideCard, $willOverrideCard) {
            $cost = $cardMgr->getPlayerCardCost($playerId, $cardId);
            if ($specialPaymentAction !== null) {
                $creator->add($specialPaymentAction);
                $cost -= $specialPaymentAction->nbPayed();
                if ($cost < 0) {
                    throw new \BgaSystemException('BUG! Special payement payed too much');
                }
                if ($cost > 0) {
                    // Pay soil
                    $creator->add(new \EA\Actions\Ability\PaySoil($playerId, $cost, $cardId));
                }
            } else {
                // Pay soil
                if (!$willOverrideCard) {
                    $creator->add(new \EA\Actions\Ability\PaySoil($playerId, $cost, $cardId));
                }
                if ($willOverrideCard) {
                    $creator->add(new \EA\Actions\Ability\CompostFromTableau($playerId, $overrideCard->cardId));
                }
                // Plant card
                $creator->add(new \EA\Actions\MainAction\PlantCard($playerId, $cardId, $posX, $posY));
                if ($willOverrideCard) {
                    $creator->add(new \EA\Actions\Ability\GainDrawCardFromDeck($playerId, 2));
                }
            }
            if ($abilityBlack !== null) {
                $creator->add(new \EA\Actions\Activation\ForceCardActivation($playerId, $cardId));
                $this->addPlacementGain($cardId, $creator, $abilityBlack, null);
                $this->addInstantGain($cardId, $creator, $abilityBlack);
            }
        };

        if ($this->gamestate->state_id() == STATE_ACTION_PLANT_ADDITIONAL_ID) {
            $mustSelectGain = ($abilityBlack !== null && $abilityBlack->hasUserPlacementGain());
            $creator = \BX\Action\buildActionCommandCreator($playerId, !$mustSelectGain || ($abilityBlack !== null && $abilityBlack->mustGainCommit()));
            $doPlant($creator);
            $this->addCommonActions($creator);
            $this->addCommonActions($creator, true);
            if ($mustSelectGain) {
                $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'activateSelectGain'));
            } else {
                $this->addNextConfirmEndPhaseOrExit($playerId, $creator);
            }
            $creator->saveOrCommit();
        } else if ($playerId == $activePlayerId) {
            if ($isFirstCard) {
                $creator = \BX\Action\buildActionCommandCreator($playerId, $willOverrideCard || ($abilityBlack !== null && $abilityBlack->mustGainCommit()));
                $doPlant($creator);
                $this->addCommonActions($creator);
                $this->addCommonActions($creator, true);
                if ($abilityBlack !== null && $abilityBlack->hasUserPlacementGain()) {
                    $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'activateSelectGain'));
                } else {
                    $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'plantSecondCard'));
                }
                $creator->saveOrCommit();
            } else {
                if ($abilityBlack !== null && $abilityBlack->hasUserPlacementGain()) {
                    $creator = \BX\Action\buildActionCommandCreator($playerId, $willOverrideCard || $abilityBlack->mustGainCommit());
                    $doPlant($creator);
                    $this->addCommonActions($creator);
                    $this->addCommonActions($creator, true);
                    $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'activateSelectGain'));
                    $creator->saveOrCommit();
                } else {
                    $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
                    $doPlant($creator);
                    $creator->add(new \EA\Actions\Ability\GainDrawCardFromDeck($playerId, ACTIVE_PLAYER_DRAW_CARDS, true));
                    $this->addCommonActions($creator);
                    $this->addCommonActions($creator, true);
                    if ($cardMgr->playerHasHandChoosingCards($playerId)) {
                        $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'chooseCards'));
                    } else {
                        $this->addNextConfirmEndPhaseOrExit($playerId, $creator);
                    }
                    $creator->commit();
                }
            }
        } else {
            $mustSelectGain = ($abilityBlack !== null && $abilityBlack->hasUserPlacementGain());
            $creator = \BX\Action\buildActionCommandCreator($playerId, !$mustSelectGain || ($abilityBlack !== null && $abilityBlack->mustGainCommit()));
            $doPlant($creator);
            if (!$mustSelectGain) {
                $creator->add(new \EA\Actions\Ability\GainDrawCardFromDeck($playerId, INACTIVE_PLAYER_DRAW_CARDS));
            }
            $this->addCommonActions($creator);
            $this->addCommonActions($creator, true);
            if ($mustSelectGain) {
                $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'activateSelectGain'));
            } else {
                $this->addNextConfirmEndPhaseOrExit($playerId, $creator);
            }
            $creator->saveOrCommit();
        }
    }

    public function argsPlantActionPayment(int $playerId)
    {
        return \BX\MultiActiveState\argsMultiActive($playerId, function ($playerId) {
            $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
            $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
            $cardIds = $playerStateMgr->playerPlantedCardIds($playerId);
            if (count($cardIds) == 0) {
                return $this->argsMergeEarthBasic([]);
            }

            $cardId = $cardIds[count($cardIds) - 1];
            $cardDef = \EA\CardDefMgr::getByCardId($cardId);
            $abilityBlack = $cardDef->abilityBlack();
            $ret = [
                'plantedCardId' => $cardId,
                'totalCost' => $cardMgr->getPlayerCardCost($playerId, $cardId),
                'soilCount' => $playerStateMgr->getPlayerSoilCount($playerId),
                'paymentType' => $abilityBlack === null ? null : $abilityBlack->getSpecialPlantingPayment(),
                'sproutCards' => $cardMgr->getPlayerTableauCountSprout($playerId),
                'growthCards' => $cardMgr->getPlayerTableauCountGrowth($playerId),
                'handCardIds' => array_keys($cardMgr->getPlayerHandCards($playerId)),
            ];
            return $this->argsMergeEarthBasic($ret);
        });
    }

    public function plantActionPlanCardWithPayment(array $payedSproutList, array $payedGrowthList, array $payedCompostFromHandCardIds)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction('plantActionPlanCardWithPayment');
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $cardIds = $playerStateMgr->playerPlantedCardIds($playerId);
        $lastCardId = $cardIds[count($cardIds) - 1];
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $lastCard = $cardMgr->getCardById($lastCardId);
        $this->plantActionPlanCardWithPossiblePayement(
            $lastCardId,
            $lastCard->locationX,
            $lastCard->locationY,
            $payedSproutList,
            $payedGrowthList,
            $payedCompostFromHandCardIds
        );
    }

    public function argsPlantActionKeepCard(int $playerId)
    {
        return \BX\MultiActiveState\argsMultiActive($playerId, function ($playerId) {
            $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
            $ret = [
                'handCardIds' => array_keys($cardMgr->getPlayerHandChoosingCards($playerId)),
                'nbCards' => $cardMgr->getPlayerNbPlantActionKeepCard($playerId),
            ];
            return $this->argsMergeEarthBasic($ret);
        });
    }

    public function planActionKeepOneDrawnCard(array $cardIds)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction('planActionKeepOneDrawnCard');
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
        $creator->add(new \EA\Actions\MainAction\PlantKeepCard($playerId, $cardIds));
        $this->addCommonActions($creator);
        $this->addNextConfirmEndPhaseOrExit($playerId, $creator);
        $creator->commit();
    }

    public function plantActionGain(array $placedSproutList, array $placedGrowthList, array $selectedCompostFromHandCardIds)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction('plantActionGain');
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $activePlayerId = $gameStateMgr->activePlayerId();
        $cardId = $playerStateMgr->stateActivatedAfterCopyCardId($playerId);
        $ability = \EA\CardDefMgr::getByCardId($cardId)->abilityBlack();

        $privateTableauCardsCount = count($cardMgr->getPlayerPrivateTableauCards($playerId));
        $mustCommit = $ability->mustGainCommit();
        $outsideMainPlantAction = false;
        switch ($this->gamestate->state_id()) {
            case STATE_ACTION_PLANT_ADDITIONAL_ID:
            case STATE_ACTION_PLANT_SPECIAL_GAIN_ID:
                $outsideMainPlantAction = true;
                break;
            default:
                if ($playerId == $activePlayerId) {
                    if ($privateTableauCardsCount != 1) {
                        $mustCommit = true;
                    }
                } else {
                    $mustCommit = true;
                }
        }

        $creator = \BX\Action\buildActionCommandCreator($playerId, $mustCommit);
        $creator->add(new \EA\Actions\Ability\PlaceSprout($playerId, $placedSproutList));
        $creator->add(new \EA\Actions\Ability\PlaceGrowth($playerId, $placedGrowthList));
        $creator->add(new \EA\Actions\Ability\PlaceCompostFromHand($playerId, $selectedCompostFromHandCardIds));
        $creator->add(new \EA\Actions\Activation\ClearCardActivation($playerId));
        if ($outsideMainPlantAction) {
            $this->addCommonActions($creator);
            $this->addNextConfirmEndPhaseOrExit($playerId, $creator);
        } else if ($playerId == $activePlayerId) {
            if ($privateTableauCardsCount == 1) {
                $this->addCommonActions($creator);
                $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'plantSecondCard'));
            } else {
                $creator->add(new \EA\Actions\Ability\GainDrawCardFromDeck($playerId, ACTIVE_PLAYER_DRAW_CARDS, true));
                $this->addCommonActions($creator);
                if ($cardMgr->playerHasHandChoosingCards($playerId)) {
                    $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'chooseCards'));
                } else {
                    $this->addNextConfirmEndPhaseOrExit($playerId, $creator);
                }
            }
        } else {
            $creator->add(new \EA\Actions\Ability\GainDrawCardFromDeck($playerId, INACTIVE_PLAYER_DRAW_CARDS));
            $this->addCommonActions($creator);
            $this->addNextConfirmEndPhaseOrExit($playerId, $creator);
        }
        $creator->saveOrCommit();
    }

    public function stActionPlantPreAdditional()
    {
        $this->revealTableauAndFauna();

        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');

        $plantMoreCard = false;
        $playerIdArray = $gameStateMgr->playerIdsInActiveOrder();
        foreach ($playerIdArray as $playerId) {
            foreach ($playerStateMgr->playerPlantedCardIds($playerId) as $cardId) {
                $cardDef = \EA\CardDefMgr::getByCardId($cardId);
                if ($cardDef === null) {
                    throw new \BgaSystemException("BUG! No card def for cardId $cardId");
                }
                $abilityBlack = $cardDef->abilityBlack();
                if ($abilityBlack === null) {
                    continue;
                }
                if ($abilityBlack->hasAbilityAllMayPlantMoreCard()) {
                    $plantMoreCard = true;
                    break;
                }
            }
            if ($plantMoreCard) {
                break;
            }
        }

        if ($plantMoreCard) {
            $this->notifyAllPlayers(
                \BX\Action\NTF_MESSAGE,
                clienttranslate('A player has planted a card that allows everyone to plant an additional card during this plant (green) action'),
                []
            );
            $this->gamestate->nextState('plant');
        } else {
            $this->gamestate->nextState('nextPhase');
        }
    }

    public function stActionPlantPreSpecialGain()
    {
        $this->revealTableauAndFauna();

        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');

        $hasSpecialGain = false;
        $playerIdArray = $gameStateMgr->playerIdsInActiveOrder();
        foreach ($playerIdArray as $playerId) {
            foreach ($playerStateMgr->playerPlantedCardIds($playerId) as $cardId) {
                $cardDef = \EA\CardDefMgr::getByCardId($cardId);
                if ($cardDef === null) {
                    throw new \BgaSystemException("BUG! No card def for cardId $cardId");
                }
                $abilityBlack = $cardDef->abilityBlack();
                if ($abilityBlack === null) {
                    continue;
                }
                if ($abilityBlack->hasConditionPerNeighbour()) {
                    $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
                    $creator->add(
                        new \BX\Action\SendMessage(
                            $playerId,
                            clienttranslate('${player_name} activates the special black ability of ${cardName}'),
                            [
                                'cardName' => $cardDef->name,
                                'i18n' => ['cardName'],
                            ]
                        )
                    );
                    $creator->add(new \EA\Actions\Activation\ForceCardActivation($playerId, $cardId));
                    $this->addPlacementGain($cardId, $creator, $abilityBlack, null);
                    $this->addInstantGain($cardId, $creator, $abilityBlack);
                    $creator->commit();
                    if ($abilityBlack->hasUserPlacementGain()) {
                        $hasSpecialGain = true;
                    }
                }
            }
        }

        if ($hasSpecialGain) {
            $this->gamestate->nextState('gain');
        } else {
            $this->gamestate->nextState('nextPhase');
        }
    }

    public function stActionPlantSpecialGain()
    {
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');

        $specialGainPlayerIds = [];
        $playerIdArray = $gameStateMgr->playerIdsInActiveOrder();
        foreach ($playerIdArray as $playerId) {
            foreach ($playerStateMgr->playerPlantedCardIds($playerId) as $cardId) {
                $cardDef = \EA\CardDefMgr::getByCardId($cardId);
                if ($cardDef === null) {
                    throw new \BgaSystemException("BUG! No card def for cardId $cardId");
                }
                $abilityBlack = $cardDef->abilityBlack();
                if ($abilityBlack === null) {
                    continue;
                }
                if ($abilityBlack->hasConditionPerNeighbour() && $abilityBlack->hasUserPlacementGain()) {
                    $specialGainPlayerIds[] = $playerId;
                    break;
                }
            }
        }
        $this->gamestate->setPlayersMultiactive($specialGainPlayerIds, null, true /*Exclusive*/);
        $this->gamestate->initializePrivateStateForPlayers($specialGainPlayerIds);
    }
}
