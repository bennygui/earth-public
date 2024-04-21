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

namespace EA\State\Activation;

require_once(__DIR__ . '/.././../../BX/php/Action.php');
require_once(__DIR__ . '/.././../../BX/php/MultiActiveState.php');
require_once(__DIR__ . '/../Actions/Activation.php');

trait GameStatesTrait
{
    public function stPreActivation()
    {
        $hasActivation = false;
        $playerIdArray = $this->getPlayerIdArray();
        foreach ($playerIdArray as $playerId) {
            if (!\EA\Actions\Activation\MarkActivatingNextCard::playerHasActivatableCards($playerId)) {
                continue;
            }
            $hasActivation = true;
            if (!\EA\Actions\Activation\MarkActivatingNextCard::playerMustChooseDirection($playerId)) {
                $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
                $creator->add(new \EA\Actions\Activation\ChooseBoardOrTableau($playerId, \EA\ACTIVATION_DIRECTION_ISLAND_CLIMATE_TABLEAU, false));
                $creator->add(new \EA\Actions\Activation\MarkActivatingNextCard($playerId));
                $creator->commit();
            }
        }

        if ($hasActivation) {
            $this->gamestate->nextState('activation');
        } else {
            $this->gamestate->nextState('nextPhase');
        }
    }

    public function stActivation()
    {
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $playerIdArray = $this->getPlayerIdArray();
        foreach ($playerIdArray as $playerId) {
            if ($playerStateMgr->getPlayerActivationDirection($playerId) === null) {
                if (\EA\Actions\Activation\MarkActivatingNextCard::playerHasActivatableCards($playerId)) {
                    $this->gamestate->setPlayersMultiactive([$playerId], null);
                    $this->gamestate->initializePrivateStateForPlayers([$playerId]);
                } else {
                    // Nothing to activate
                    continue;
                }
            } else {
                $this->gamestate->setPlayersMultiactive([$playerId], null);
                $this->gamestate->setPrivateState($playerId, STATE_ACTIVATION_CHOOSE_ACTIVATE_OR_SKIP_ID);
            }
        }
    }

    public function activationChooseActivationDirection(int $activationDirection)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction('activationChooseActivationDirection');
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \EA\Actions\Activation\ChooseBoardOrTableau($playerId, $activationDirection, false));
        $creator->add(new \EA\Actions\Activation\MarkActivatingNextCard($playerId));
        $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'activateOrSkip'));
        $creator->save();
    }

    private function getActivationAbilityString(bool $isGain, int $abilityId, int $count, bool $hasCondition = false)
    {
        if ($hasCondition) {
            $count = 'X';
        }
        $pos = ($isGain ? '+' : '-');
        switch ($abilityId) {
            case \EA\ABILITY_DRAW_CARD_FROM_DECK:
                return $pos . $count . ' ${drawFromDeckIcon}';
            case \EA\ABILITY_GROWTH:
                return $pos . $count . ' ${growthIcon}';
            case \EA\ABILITY_SOIL:
                return $pos . $count . ' ${soilIcon}';
            case \EA\ABILITY_SPROUT:
                return $pos . $count . ' ${sproutIcon}';
            case \EA\ABILITY_COMPOST_FROM_HAND:
                return '+' . $count . ' ${compostFromHandIcon}';
            case \EA\ABILITY_COMPOST_FROM_DECK:
                return $pos . $count . ' ${compostFromDeckIcon}';
            case \EA\ABILITY_COMPOST_DESTROY:
                return $pos . $count . ' ${compostDestroyIcon}';
        }
        return '';
    }

    public function argsActivationChooseActivateOrSkip(int $playerId)
    {
        return \BX\MultiActiveState\argsMultiActive($playerId, function ($playerId) {
            $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
            $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
            $mainActionId = $gameStateMgr->getActiveMainActionId();
            $afterCopyCardId = $playerStateMgr->stateActivatedAfterCopyCardId($playerId);

            if ($afterCopyCardId === null) {
                return $this->argsMergeEarthDefault($playerId, []);
            }
            $ability = \EA\CardDefMgr::getByCardId($afterCopyCardId)->getAbilityMatchingMainAction($mainActionId);
            if ($ability === null) {
                return $this->argsMergeEarthDefault($playerId, []);
            }

            $payment = '';
            $ability->foreachPayment(function ($abilityId, $count) use (&$payment) {
                $payment .= $this->getActivationAbilityString(false, $abilityId, $count);
                $payment .= ' ';
            });
            $payment = trim($payment);

            $gain = '';
            $ability->foreachGain(function ($abilityId, $count) use (&$gain, $ability) {
                $gain .= $this->getActivationAbilityString(true, $abilityId, $count, $ability->hasConditionForCount() || $ability->getDirection() !== null);
                $gain .= ' ';
            });
            $gain = trim($gain);

            $activationString = null;
            if (strlen($payment) > 0) {
                $activationString = $payment . ' : ' . $gain;
            } else {
                $activationString = $gain;
            }

            $ret = [
                'activatedBeforeCopyCardId' => $playerStateMgr->stateActivatedBeforeCopyCardId($playerId),
                'activatedAfterCopyCardId' => $afterCopyCardId,
                'mainActionId' => $gameStateMgr->getActiveMainActionId(),
                'activationString' => $activationString,
            ];
            return $this->argsMergeEarthDefault($playerId, $ret);
        });
    }

    public function activationSkipCard()
    {
        \BX\Lock\Locker::lock();
        $this->checkAction('activationSkipCard');
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $cardId = $playerStateMgr->stateActivatedAfterCopyCardId($playerId);

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \EA\Actions\Activation\SkipCardNotification($playerId, $cardId));
        $marker = new \EA\Actions\Activation\MarkActivatingNextCard($playerId);
        $creator->add($marker);

        if ($marker->hasNextCard()) {
            $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'nextCard'));
        } else {
            $this->addNextConfirmEndPhaseOrExit($playerId, $creator);
        }
        $creator->save();
    }

    public function activationActivateCard()
    {
        \BX\Lock\Locker::lock();
        $this->checkAction('activationActivateCard');
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $cardId = $playerStateMgr->stateActivatedAfterCopyCardId($playerId);
        $beforeCopyCardId = $playerStateMgr->stateActivatedBeforeCopyCardId($playerId);
        $mainActionId = $gameStateMgr->getActiveMainActionId();
        $ability = \EA\CardDefMgr::getByCardId($cardId)->getAbilityMatchingMainAction($mainActionId);

        $this->validateAbilityPayment($playerId, $ability);
        if ($ability->hasCopy()) {
            $creator = new \BX\Action\ActionCommandCreator($playerId);
            $creator->add(new \EA\Actions\Activation\ActivatedCardNotification($playerId, $cardId));
            $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'activateCopyCard'));
            $creator->save();
        } else if ($ability->hasUserPlacementPayment()) {
            $creator = new \BX\Action\ActionCommandCreator($playerId);
            $creator->add(new \EA\Actions\Activation\ActivatedCardNotification($playerId, $cardId));
            $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'activateSelectPayment'));
            $creator->save();
        } else if ($ability->hasUserPlacementGain()) {
            $creator = \BX\Action\buildActionCommandCreator($playerId, $ability->mustGainCommit() || $ability->mustPaymentCommit());
            $creator->add(new \EA\Actions\Activation\ActivatedCardNotification($playerId, $cardId));
            $this->addInstantPayment($cardId, $creator, $ability);
            $this->addPlacementGain($cardId, $creator, $ability, $beforeCopyCardId);
            $this->addInstantGain($cardId, $creator, $ability);
            $this->addCommonActions($creator);
            $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'activateSelectGain'));
            $creator->saveOrCommit();
        } else {
            $creator = \BX\Action\buildActionCommandCreator($playerId, $ability->mustGainCommit() || $ability->mustPaymentCommit());
            $creator->add(new \EA\Actions\Activation\ActivatedCardNotification($playerId, $cardId));
            $this->addInstantPayment($cardId, $creator, $ability);
            $this->addInstantGain($cardId, $creator, $ability);
            $this->addCommonActions($creator);
            $marker = new \EA\Actions\Activation\MarkActivatingNextCard($playerId);
            $creator->add($marker);
            if ($marker->hasNextCard()) {
                $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'nextCard'));
            } else {
                $this->addNextConfirmEndPhaseOrExit($playerId, $creator);
            }
            $creator->saveOrCommit();
        }
    }

    public function activationGain(array $placedSproutList, array $placedGrowthList, array $selectedCompostFromHandCardIds)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction('activationGain');
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $cardId = $playerStateMgr->stateActivatedAfterCopyCardId($playerId);
        $mainActionId = $gameStateMgr->getActiveMainActionId();
        $ability = \EA\CardDefMgr::getByCardId($cardId)->getAbilityMatchingMainAction($mainActionId);

        $creator = \BX\Action\buildActionCommandCreator($playerId, $ability->mustGainCommit());
        $creator->add(new \EA\Actions\Ability\PlaceSprout($playerId, $placedSproutList));
        $creator->add(new \EA\Actions\Ability\PlaceGrowth($playerId, $placedGrowthList));
        $creator->add(new \EA\Actions\Ability\PlaceCompostFromHand($playerId, $selectedCompostFromHandCardIds));
        $this->addCommonActions($creator);
        $marker = new \EA\Actions\Activation\MarkActivatingNextCard($playerId);
        $creator->add($marker);
        if ($marker->hasNextCard()) {
            $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'nextCard'));
        } else {
            $this->addNextConfirmEndPhaseOrExit($playerId, $creator);
        }
        $creator->saveOrCommit();
    }

    public function activationPay(array $payedSproutList, array $payedGrowthList, array $payedCompostFromHandCardIds)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction('activationPay');
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $cardId = $playerStateMgr->stateActivatedAfterCopyCardId($playerId);
        $beforeCopyCardId = $playerStateMgr->stateActivatedBeforeCopyCardId($playerId);
        $mainActionId = $gameStateMgr->getActiveMainActionId();
        $ability = \EA\CardDefMgr::getByCardId($cardId)->getAbilityMatchingMainAction($mainActionId);

        $creator = \BX\Action\buildActionCommandCreator($playerId, $ability->mustGainCommit() || $ability->mustPaymentCommit());
        $this->addInstantPayment($cardId, $creator, $ability);
        $creator->add(new \EA\Actions\Ability\PaySprout($playerId, $ability, $payedSproutList));
        $creator->add(new \EA\Actions\Ability\PayGrowth($playerId, $ability, $payedGrowthList));
        $ability->foreachPayment(function ($abilityId, $count) use ($playerId, $creator, $payedCompostFromHandCardIds) {
            if ($abilityId == \EA\ABILITY_COMPOST_FROM_HAND) {
                $creator->add(new \EA\Actions\Ability\ExactCompostFromHand($playerId, $count, $payedCompostFromHandCardIds));
            }
        });
        $this->addInstantGain($cardId, $creator, $ability);
        if ($ability->hasUserPlacementGain()) {
            $this->addPlacementGain($cardId, $creator, $ability, $beforeCopyCardId);
            $this->addCommonActions($creator);
            $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'activateSelectGain'));
        } else {
            $this->addCommonActions($creator);
            $marker = new \EA\Actions\Activation\MarkActivatingNextCard($playerId);
            $creator->add($marker);
            if ($marker->hasNextCard()) {
                $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'nextCard'));
            } else {
                $this->addNextConfirmEndPhaseOrExit($playerId, $creator);
            }
        }
        $creator->saveOrCommit();
    }

    public function argsActivationChooseCardToCopy(int $playerId)
    {
        return \BX\MultiActiveState\argsMultiActive($playerId, function ($playerId) {
            $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
            $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
            $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');

            $cardId = $playerStateMgr->stateActivatedAfterCopyCardId($playerId);
            $mainActionId = $gameStateMgr->getActiveMainActionId();
            $activePlayerId = $gameStateMgr->activePlayerId();
            $ret = [
                'activatedBeforeCopyCardId' => $playerStateMgr->stateActivatedBeforeCopyCardId($playerId),
                'activatedAfterCopyCardId' => $playerStateMgr->stateActivatedAfterCopyCardId($playerId),
                'mainActionId' => $gameStateMgr->getActiveMainActionId(),
                'cardIds' => array_values(array_filter(
                    array_map(
                        fn ($c) => $c->cardId,
                        $cardMgr->getTableauPlayerCardsWithAbilityMatchingMainAction(
                            $playerId,
                            $activePlayerId,
                            $mainActionId
                        )
                    ),
                    fn ($cId) => $cId != $cardId
                )),
            ];
            return $this->argsMergeEarthBasic($ret);
        });
    }

    public function activationSelectCardToCopy(int $cardId)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction('activationSelectCardToCopy');
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $mainActionId = $gameStateMgr->getActiveMainActionId();
        $activePlayerId = $gameStateMgr->activePlayerId();
        $cardIdsThatCanBeCopied = array_map(fn($c) => $c->cardId, $cardMgr->getTableauPlayerCardsWithAbilityMatchingMainAction(
            $playerId,
            $activePlayerId,
            $mainActionId
        ));
        if (array_search($cardId, $cardIdsThatCanBeCopied) === false) {
            throw new \BgaSystemException("BUG! Card cannot be copied: $cardId for $mainActionId");
        }
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $beforeCopyCardId = $playerStateMgr->stateActivatedBeforeCopyCardId($playerId);
        $ability = \EA\CardDefMgr::getByCardId($cardId)->getAbilityMatchingMainAction($mainActionId);

        if ($ability->hasCopy()) {
            throw new \BgaSystemException("BUG! Copying a copying ability for $cardId");
        } else if ($ability->hasUserPlacementPayment()) {
            $creator = new \BX\Action\ActionCommandCreator($playerId);
            $creator->add(new \EA\Actions\Activation\ActivatedCardNotification($playerId, $cardId));
            $creator->add(new \EA\Actions\Activation\MarkActivatingCopyCard($playerId, $cardId));
            $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'activateSelectPayment'));
            $creator->save();
        } else if ($ability->hasUserPlacementGain()) {
            $creator = \BX\Action\buildActionCommandCreator($playerId, $ability->mustGainCommit() || $ability->mustPaymentCommit());
            $creator->add(new \EA\Actions\Activation\ActivatedCardNotification($playerId, $cardId));
            $creator->add(new \EA\Actions\Activation\MarkActivatingCopyCard($playerId, $cardId));
            $this->addInstantPayment($cardId, $creator, $ability);
            $this->addPlacementGain($cardId, $creator, $ability, $beforeCopyCardId);
            $this->addInstantGain($cardId, $creator, $ability);
            $this->addCommonActions($creator);
            $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'activateSelectGain'));
            $creator->saveOrCommit();
        } else {
            $creator = \BX\Action\buildActionCommandCreator($playerId, $ability->mustGainCommit() || $ability->mustPaymentCommit());
            $creator->add(new \EA\Actions\Activation\ActivatedCardNotification($playerId, $cardId));
            $creator->add(new \EA\Actions\Activation\MarkActivatingCopyCard($playerId, $cardId));
            $this->addInstantPayment($cardId, $creator, $ability);
            $this->addInstantGain($cardId, $creator, $ability);
            $this->addCommonActions($creator);
            $marker = new \EA\Actions\Activation\MarkActivatingNextCard($playerId);
            $creator->add($marker);
            if ($marker->hasNextCard()) {
                $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'nextCard'));
            } else {
                $this->addNextConfirmEndPhaseOrExit($playerId, $creator);
            }
            $creator->saveOrCommit();
        }
    }
}
