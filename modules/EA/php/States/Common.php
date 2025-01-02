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

namespace EA\State\Common;

require_once(__DIR__ . '/.././../../BX/php/Action.php');
require_once(__DIR__ . '/.././../../BX/php/MultiActiveState.php');
require_once(__DIR__ . '/../Actions/Ability.php');
require_once(__DIR__ . '/../Actions/Fauna.php');

trait GameStatesTrait
{

    public function argsEarthDefaultMultiActive()
    {
        return $this->argsCustomMultiActive(function ($playerId) {
            return $this->argsEarthDefault($playerId);
        });
    }

    public function argsEarthDefault(int $playerId)
    {
        return \BX\MultiActiveState\argsMultiActive($playerId, function ($playerId) {
            $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
            return $this->argsMergeEarthBasic([
                'canPlayEvent' => $cardMgr->playerHasAnytimeEventInHand($playerId),
                'canPlayConversion' => $cardMgr->playerCanPlayConversion($playerId),
                'canUseSeed' => $this->playerCanUseSeed($playerId),
                'canCreateSeed' => $this->playerCanCreateSeed($playerId),
            ]);
        });
    }

    public function argsMergeEarthDefault(int $playerId, array $ret)
    {
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        return $this->argsMergeEarthBasic(
            array_merge(
                [
                    'canPlayEvent' => $cardMgr->playerHasAnytimeEventInHand($playerId),
                    'canPlayConversion' => $cardMgr->playerCanPlayConversion($playerId),
                    'canUseSeed' => $this->playerCanUseSeed($playerId),
                    'canCreateSeed' => $this->playerCanCreateSeed($playerId),
                ],
                $ret
            )
        );
    }

    public function argsMergeEarthBasic(array $ret)
    {
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $mainActionId = $gameStateMgr->getActiveMainActionId();
        $newRet = [];
        if ($mainActionId !== null) {
            $newRet['mainActionId'] = $mainActionId;
            $newRet['mainActionName'] = \getMainActionName($mainActionId);
            $newRet['mainActionColor'] = \getMainActionColorName($mainActionId);
            $newRet['i18n'][] = 'mainActionName';
            $newRet['i18n'][] = 'mainActionColor';
        }
        return array_merge($newRet, $ret);
    }

    private function addCommonActions(\BX\Action\ActionCommandCreatorInterface $creator, bool $considerPrivateVisibility = false)
    {
        // Test fauna cards
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $cards = $cardMgr->getFaunaCards();
        foreach ($cards as $card) {
            $creator->add(\EA\Actions\Fauna\getFaunaAction($creator->getPlayerId(), $card->cardId, $considerPrivateVisibility));
        }

        // If scores where to be calculated live, this is the place that they
        // would need to be calculated
    }

    private function getFaunaProgressForPlayers(array $playerIdArray, bool $considerPrivateVisibility)
    {
        $progress = [];
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $cards = $cardMgr->getFaunaCards();
        foreach ($playerIdArray as $playerId) {
            $progress[$playerId] = [];
            foreach ($cards as $card) {
                $action = \EA\Actions\Fauna\getFaunaAction($playerId, $card->cardId, $considerPrivateVisibility);
                $progress[$playerId][$card->cardId] = $action->getPlayerFaunaProgress();
            }
        }
        return $progress;
    }

    private function enableSendFaunaProgress()
    {
        \BX\Action\BaseActionCommandNotifier::setGlobalOnNotifierEndCallback(
            'sendFaunaProgress',
            fn ($notifier) => $this->sendFaunaProgress($notifier)
        );
    }

    private function disableSendFaunaProgress()
    {
        \BX\Action\BaseActionCommandNotifier::removeGlobalOnNotifierEndCallback('sendFaunaProgress');
    }

    private function sendFaunaProgress(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        \BX\Action\ActionCommandMgr::clear();
        $playerMgr = \BX\Action\ActionRowMgrRegister::getMgr('player');
        $progress = $this->getFaunaProgressForPlayers($playerMgr->getAllPlayerIds(), false);
        $notifier->notifyNoMessage(
            NTF_UPDATE_FAUNA_PROGRESS,
            ['faunaProgress' => $progress]
        );
        \BX\Action\ActionCommandMgr::apply($notifier->getPlayerId());
        $progress = $this->getFaunaProgressForPlayers([$notifier->getPlayerId()], true);
        $notifier->notifyPrivateNoMessage(
            NTF_UPDATE_FAUNA_PROGRESS,
            ['faunaProgress' => $progress]
        );
        \BX\Action\ActionCommandMgr::clear();
    }

    private function updateSeenFaunaObjective(int $playerId)
    {
        if (array_search($playerId, $this->getPlayerIdArray()) === false) {
            return;
        }

        $playerSeenLeafTokenMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_seen_leaf_token');
        $playerSeenLeafTokenMgr->updateSeenLeafTokenForPlayerId($playerId);

        $this->notifyPlayer(
            $playerId,
            NTF_SEEN_FAUNA_OBJECTIVE,
            '',
            []
        );
    }

    private function mustGotoConfirmEndPhase(int $playerId, ?\BX\Action\ActionCommandCreatorInterface $creator)
    {
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $canUndo = false;
        if ($creator === null) {
            $canUndo = (\BX\Action\ActionCommandMgr::count($playerId) > 0);
        } else {
            $canUndo = (!$creator->willCommit());
        }
        $canPlayEvent = $cardMgr->playerHasAnytimeEventInHand($playerId);
        $canPlayConversion = $cardMgr->playerCanPlayConversion($playerId);
        $canUseSeed = $this->playerCanUseSeed($playerId);
        $canCreateSeed = $this->playerCanCreateSeed($playerId);
        return ($canUndo || $canPlayEvent || $canPlayConversion || $canUseSeed || $canCreateSeed);
    }

    private function addNextConfirmEndPhaseOrExit(int $playerId, \BX\Action\ActionCommandCreatorInterface $creator)
    {
        if ($this->mustGotoConfirmEndPhase($playerId, $creator)) {
            $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'confirmEndPhase'));
        } else {
            $creator->add(new \BX\MultiActiveState\ExitPrivateStateActionCommand($playerId));
        }
    }


    private function addMainActionMoveToActivation(int $playerId, \BX\Action\ActionCommandCreatorInterface $creator)
    {
        if (!gameVersionHasFalltroughActivation()) {
            $this->addNextConfirmEndPhaseOrExit($playerId, $creator);
            return;
        }
        if (\EA\Actions\Activation\MarkActivatingNextCard::playerHasActivatableCards($playerId)) {
            if (\EA\Actions\Activation\MarkActivatingNextCard::playerMustChooseDirection($playerId)) {
                $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'activationChooseBoardOrTableau'));
            } else {
                $creator->add(new \EA\Actions\Activation\ChooseBoardOrTableau($playerId, \EA\ACTIVATION_DIRECTION_ISLAND_CLIMATE_TABLEAU, false));
                $creator->add(new \EA\Actions\Activation\MarkActivatingNextCard($playerId));
                $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'activationActivateOrSkip'));
            }
        } else {
            $this->addNextConfirmEndPhaseOrExit($playerId, $creator);
        }
    }

    private function validateAbilityPayment(int $playerId, \EA\Ability $ability)
    {
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $ability->foreachPayment(function ($abilityId, $count) use ($playerId, $playerStateMgr, $cardMgr) {
            $canPay = true;
            switch ($abilityId) {
                case \EA\ABILITY_SOIL:
                    $canPay = ($playerStateMgr->getPlayerSoilCount($playerId) >= $count);
                    break;
                case \EA\ABILITY_GROWTH:
                    $canPay = ($cardMgr->getPlayerGrowthCount($playerId) >= $count);
                    break;
                case \EA\ABILITY_SPROUT:
                    $canPay = ($cardMgr->getPlayerSproutCount($playerId) >= $count);
                    break;
                case \EA\ABILITY_COMPOST_FROM_HAND:
                    $canPay = (count($cardMgr->getPlayerHandCards($playerId)) >= $count);
                    break;
                case \EA\ABILITY_COMPOST_DESTROY:
                    $canPay = (count($cardMgr->getPlayerCompostCards($playerId)) >= $count);
                    break;
            }
            if (!$canPay) {
                throw new \BgaUserException($this->_('You cannot activate this card: you do not have enough to pay'));
            }
        });
    }

    private function countAbilityGain(int $cardId, \BX\Action\ActionCommandCreatorInterface $creator, \EA\Ability $ability, int $count, ?int $beforeCopyCardId)
    {
        if (!$ability->hasCondition()) {
            return $count;
        }
        if ($ability->hasConditionAddToTypeInDirection()) {
            if ($beforeCopyCardId === null) {
                return $count;
            }
            return $count * $this->countPlayerTableauCardsConditionDirection($beforeCopyCardId, $creator, $ability);
        } else if ($ability->hasConditionPerNeighbour()) {
            $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
            $divisor = $ability->getConditionPerNeighbourDivisor();
            $neighbourPlayerIds = $this->neighbourPlayerIds($creator->getPlayerId());
            $neighbourCount = 0;
            foreach ($neighbourPlayerIds as $npId) {
                if ($ability->getPerTypeCondition() == \EA\CARD_TYPE_EVENT) {
                    $neighbourCount = max($neighbourCount, count($cardMgr->getPlayerBoardEventCards($npId)));
                } else {
                    $neighbourCount = max($neighbourCount, $this->countIslandClimateTableauCardsRespectingCondition($npId, $ability));
                }
            }
            return $count * intdiv($neighbourCount, $divisor);
        } else if ($ability->isCountForAllCards()) {
            return $count * $this->countIslandClimateTableauCardsRespectingCondition($creator->getPlayerId(), $ability);
        } else {
            $ability->foreachCondition(function ($conditionId, $conditionType) use (&$count, $creator, $ability) {
                switch ($conditionId) {
                    case \EA\AB_COND_PER_TYPE:
                    case \EA\AB_COND_PER_HABITAT:
                    case \EA\AB_COND_PER_COLOR:
                    case \EA\AB_COND_PER_GERMINATE:
                        $count = $count * $this->countPlantedCardsRespectingCondition($creator->getPlayerId(), $ability);
                        break;
                }
            });
            return $count;
        }
    }

    private function addInstantGain(int $cardId, \BX\Action\ActionCommandCreatorInterface $creator, \EA\Ability $ability)
    {
        $ability->foreachGain(function ($abilityId, $count) use ($cardId, $creator, $ability) {
            $isEvent = \EA\CardDefMgr::getByCardId($cardId)->isEvent();
            $count = $this->countAbilityGain($cardId, $creator, $ability, $count, null);
            switch ($abilityId) {
                case \EA\ABILITY_DRAW_CARD_FROM_DECK:
                    $creator->add(new \EA\Actions\Ability\GainDrawCardFromDeck($creator->getPlayerId(), $count, false, $isEvent));
                    break;
                case \EA\ABILITY_SOIL:
                    $creator->add(new \EA\Actions\Ability\GainSoil($creator->getPlayerId(), $count, $cardId));
                    break;
                case \EA\ABILITY_COMPOST_FROM_DECK:
                    $creator->add(new \EA\Actions\Ability\CompostFromDeck($creator->getPlayerId(), $count));
                    break;
                case \EA\ABILITY_SEED:
                    $creator->add(new \EA\Actions\Ability\GainSeed($creator->getPlayerId(), $count, $cardId));
                    break;
                case \EA\ABILITY_SPROUT_ALL_OTHERS:
                    if ($this->getPlayerCount() == 1) {
                        $creator->add(new \EA\Actions\Gaia\PlaceSprout($creator->getPlayerId(), $count));
                    } else {
                        $creator->add(new \EA\Actions\Ability\SproutAllOthers($creator->getPlayerId(), $count, $cardId));
                    }
                    break;
                case \EA\ABILITY_SPROUT_CHOOSE_ONE:
                    if ($this->getPlayerCount() == 1) {
                        $creator->add(new \EA\Actions\Gaia\PlaceSprout($creator->getPlayerId(), $count));
                    } else if ($this->getPlayerCount() == 2) {
                        $creator->add(new \EA\Actions\Ability\SproutChooseOne(
                            $creator->getPlayerId(),
                            $this->otherPlayerId($creator->getPlayerId()),
                            $count,
                            $cardId
                        ));
                    }
                    break;
            }
        });
    }

    private function addPlacementGain(int $cardId, \BX\Action\ActionCommandCreatorInterface $creator, \EA\Ability $ability, ?int $beforeCopyCardId)
    {
        $ability->foreachGain(function ($abilityId, $count) use ($cardId, $creator, $ability, $beforeCopyCardId) {
            $count = $this->countAbilityGain($cardId, $creator, $ability, $count, $beforeCopyCardId);
            if ($ability->getDirection() !== null && $ability->getDirection() == \EA\AB_DIRECTION_ALL_ADJACENT) {
                $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
                $cards = $cardMgr->getPlayerTableauCardsInCardAdjacent($creator->getPlayerId(), $beforeCopyCardId ?? $cardId);
                $creator->add(new \EA\Actions\Ability\GainGainedCardIdList($creator->getPlayerId(), array_map(fn ($c) => $c->cardId, $cards), false));
            }
            switch ($abilityId) {
                case \EA\ABILITY_DRAW_CARD_FROM_COMPOST:
                    $creator->add(new \EA\Actions\Ability\GainDrawCardFromCompost($creator->getPlayerId()));
                    break;
                case \EA\ABILITY_GROWTH:
                    $creator->add(new \EA\Actions\Ability\GainGrowth($creator->getPlayerId(), $count));
                    break;
                case \EA\ABILITY_SPROUT:
                    $creator->add(new \EA\Actions\Ability\GainSprout($creator->getPlayerId(), $count));
                    break;
                case \EA\ABILITY_COMPOST_FROM_HAND:
                    $creator->add(new \EA\Actions\Ability\GainCompostFromHand($creator->getPlayerId(), $count));
                    break;
                case \EA\ABILITY_SPROUT_CHOOSE_ONE:
                    if ($this->getPlayerCount() > 2) {
                        $creator->add(new \EA\Actions\Ability\GainSproutChooseOne($creator->getPlayerId(), $count));
                    }
                    break;
            }
        });
    }

    private function addInstantPayment(int $cardId, \BX\Action\ActionCommandCreatorInterface $creator, \EA\Ability $ability)
    {
        $ability->foreachPayment(function ($abilityId, $count) use ($cardId, $creator) {
            switch ($abilityId) {
                case \EA\ABILITY_SOIL:
                    $creator->add(new \EA\Actions\Ability\PaySoil($creator->getPlayerId(), $count, $cardId));
                    break;
                case \EA\ABILITY_COMPOST_DESTROY:
                    $creator->add(new \EA\Actions\Ability\CompostDestroy($creator->getPlayerId(), $count));
                    break;
            }
        });
    }

    private function countPlantedCardsRespectingCondition(int $playerId, \EA\Ability $ability)
    {
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $cardIds = $playerStateMgr->playerPlantedCardIds($playerId);
        return $this->countCardsRespectingCondition($cardIds, $ability);
    }

    private function countIslandClimateTableauCardsRespectingCondition(int $playerId, \EA\Ability $ability)
    {
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $cardIds = array_map(fn ($c) => $c->cardId, $cardMgr->getPlayerIslandClimateTableauCards($playerId, $playerId));
        return $this->countCardsRespectingCondition($cardIds, $ability);
    }

    private function countCardsRespectingCondition(array $cardIds, \EA\Ability $ability)
    {
        if (!$ability->hasCondition()) {
            throw new \BgaSystemException('BUG! Should not be called, ability has no condition');
        }
        $count = 0;
        foreach ($cardIds as $cardId) {
            $cardDef = \EA\CardDefMgr::getByCardId($cardId);
            $ability->foreachCondition(function ($conditionId, $conditionType) use (&$count, $cardDef) {
                switch ($conditionId) {
                    case \EA\AB_COND_PER_TYPE:
                        if ($cardDef->hasCardType($conditionType)) {
                            $count += 1;
                        }
                        break;
                    case \EA\AB_COND_PER_HABITAT:
                        if ($cardDef->hasHabitat($conditionType)) {
                            $count += 1;
                        }
                        break;
                    case \EA\AB_COND_PER_COLOR:
                        if ($conditionType == \EA\AB_COLOR_MULTICOLOR) {
                            if ($cardDef->getAbilityForColor($conditionType)) {
                                $count += 1;
                            }
                        } else {
                            if ($cardDef->hasAbilityMatchingColor($conditionType)) {
                                $count += 1;
                            }
                        }
                        break;
                    case \EA\AB_COND_PER_GERMINATE:
                        $filter = \EA\CardDef::germinateIdToFilter($conditionType);
                        if ($filter($cardDef)) {
                            $count += 1;
                        }
                        break;
                    case \EA\AB_COND_PER_NEIGHBOUR:
                        break;
                    default:
                        throw new \BgaSystemException('BUG! Should not be called for unsupported condition');
                }
            });
        }
        return $count;
    }

    public function countPlayerTableauCardsConditionDirection(int $cardId, \BX\Action\ActionCommandCreatorInterface $creator, \EA\Ability $ability)
    {
        if (!$ability->hasConditionAddToTypeInDirection()) {
            throw new \BgaSystemException('BUG! Should not be called, ability has no add to type condition');
        }
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $cards = null;
        switch ($ability->getDirection()) {
            case \EA\AB_DIRECTION_COLUMN:
                $cards = $cardMgr->getPlayerTableauCardsInCardColumn($creator->getPlayerId(), $cardId);
                break;
            case \EA\AB_DIRECTION_ROW:
                $cards = $cardMgr->getPlayerTableauCardsInCardRow($creator->getPlayerId(), $cardId);
                break;
            case \EA\AB_DIRECTION_DIAG_ADJACENT:
                $cards = $cardMgr->getPlayerTableauCardsInCardDiagAdjacent($creator->getPlayerId(), $cardId);
                $cards[] = $cardMgr->getCardById($cardId);
                break;
            default:
                throw new \BgaSystemException('BUG! Unknown direction');
        }
        $cardIds = [];
        foreach ($cards as $card) {
            $conditionType = $ability->getPerTypeCondition();
            if ($conditionType === null) {
                throw new \BgaSystemException('BUG! Ability has no per type condition');
            }
            if (!$card->getCardDef()->hasCardType($conditionType)) {
                continue;
            }
            if ($ability->hasGainSprout()) {
                $max = $card->getCardDef()->sproutMax;
                if ($max === null || $max == 0 || $card->sproutCount >= $max) {
                    continue;
                }
            } else if ($ability->hasGainGrowth()) {
                $max = $card->getCardDef()->growthMax;
                if ($max === null || $max == 0 || $card->growthCount >= $max) {
                    continue;
                }
            } else {
                throw new \BgaSystemException('BUG! Ability gain must be Sprout or Growth');
            }
            $cardIds[] = $card->cardId;
        }
        $creator->add(new \EA\Actions\Ability\GainGainedCardIdList($creator->getPlayerId(), $cardIds, true));
        return count($cardIds);
    }

    private function resetMainActionLeafToken()
    {
        $leafTokenMgr = \BX\Action\ActionRowMgrRegister::getMgr('leaf_token');
        foreach ($leafTokenMgr->resetMainActionNow() as $leafToken) {
            $notifier = new \BX\Action\ActionCommandNotifierPublic($leafToken->playerId);
            $notifier->notifyNoMessage(
                NTF_UPDATE_LEAF_TOKEN,
                [
                    'leafToken' => $leafToken->toPlayerUI($leafToken->playerId),
                ]
            );
        }
    }

    private function playerCanUseSeed(int $playerId)
    {
        if (!gameHasExpansionAbundance()) {
            return false;
        }

        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        return ($playerStateMgr->getPlayerSeedCount($playerId) > 0);
    }

    private function playerCanCreateSeed(int $playerId)
    {
        if (!gameHasExpansionAbundance()) {
            return false;
        }

        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        if ($cardMgr->getPlayerSproutCount($playerId) >= 4) {
            return true;
        }

        $leafTokenMgr = \BX\Action\ActionRowMgrRegister::getMgr('leaf_token');
        if (count($leafTokenMgr->getDiscardableLeafInOrder($playerId)) > 0) {
            return true;
        }

        return false;
    }

    private function otherPlayerId(int $playerId)
    {
        $playerIdArray = $this->getPlayerIdArray();
        if (count($playerIdArray) != 2) {
            throw new \BgaSystemException('BUG! otherPlayer called but not a 2 player game');
        }
        foreach ($playerIdArray as $otherPlayerId) {
            if ($otherPlayerId != $playerId) {
                return $otherPlayerId;
            }
        }
        throw new \BgaSystemException('BUG! otherPlayer called but could not find other player');
    }

    private function neighbourPlayerIds(int $playerId)
    {
        switch ($this->getPlayerCount()) {
            case 1:
                return [$playerId];
            case 2:
                return [$this->otherPlayerId($playerId)];
            default:
                $playerIdArray = $this->getPlayerIdArray();
                $playerIdArray = \BX\Collection\rotateValueToFront($playerIdArray, $playerId);
                return [$playerIdArray[1], $playerIdArray[count($playerIdArray) - 1]];
        }
    }

    private function revealTableauAndFauna()
    {
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');

        // Commit all players
        $playerIdArray = $gameStateMgr->playerIdsInActiveOrder();
        foreach ($playerIdArray as $playerId) {
            \BX\Action\ActionCommandMgr::commit($playerId);
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
    }

    private function updateAllPlayersCardCounts()
    {
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        foreach ($this->getPlayerIdArray() as $playerId) {
            $notifier = new \BX\Action\ActionCommandNotifierPublic($playerId);
            $notifier->notifyNoMessage(
                NTF_UPDATE_CARD_COUNTS,
                [
                    'cardCounts' => $cardMgr->getCardCountsUIForPlayerId($playerId),
                ]
            );
        }
    }
}
