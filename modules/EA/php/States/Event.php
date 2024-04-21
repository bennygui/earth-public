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

namespace EA\State\Event;

require_once(__DIR__ . '/.././../../BX/php/Action.php');
require_once(__DIR__ . '/.././../../BX/php/MultiActiveState.php');
require_once(__DIR__ . '/../Actions/Event.php');

trait GameStatesTrait
{
    public function eventPlay()
    {
        \BX\Lock\Locker::lock();
        $playerId = $this->getCurrentPlayerId();
        if ($this->gamestate->isPlayerActive($playerId)) {
            $this->checkAction('eventPlay');
        } else {
            $this->gamestate->checkPossibleAction('eventPlay');
        }
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        if (!$cardMgr->playerHasEventInHand($playerId)) {
            throw new \BgaUserException($this->_('You do not have any event card to play'));
        }

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \EA\Actions\Event\KeepReturnFromEventState($playerId));
        $creator->add(new \BX\MultiActiveState\JumpPrivateStateActionCommand($playerId, STATE_EVENT_CHOOSE_CARD_ID));
        $creator->save();
    }

    public function argsEventChooseCard(int $playerId)
    {
        return \BX\MultiActiveState\argsMultiActive($playerId, function ($playerId) {
            $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
            $ret['eventCardIds'] = array_keys($cardMgr->getPlayerEventHandCards($playerId));
            // Disable playing events
            $ret['canPlayEvent'] = false;
            return $this->argsMergeEarthDefault($playerId, $ret);
        });
    }

    public function eventChooseCard(int $cardId)
    {
        \BX\Lock\Locker::lock();
        $playerId = $this->getCurrentPlayerId();
        $this->checkAction('eventChooseCard');
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $cardDef = \EA\CardDefMgr::getByCardId($cardId);
        if ($cardDef === null) {
            throw new \BgaSystemException("BUG! No card def for cardId $cardId");
        }

        $ability = $cardDef->abilityBlack();
        $this->validateAbilityPayment($playerId, $ability);
        if ($ability->hasUserPlacementPayment()) {
            $creator = new \BX\Action\ActionCommandCreator($playerId);
            $creator->add(new \EA\Actions\Event\PlayEventCard($playerId, $cardId));
            $creator->add(new \EA\Actions\Activation\ForceCardActivation($playerId, $cardId));
            $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'eventSelectPayment'));
            $creator->save();
        } else if ($ability->hasUserPlacementGain()) {
            $creator = \BX\Action\buildActionCommandCreator($playerId, $ability->mustGainCommit() || $ability->mustPaymentCommit() || $this->eventWillDrawCard($playerId));
            $creator->add(new \EA\Actions\Event\PlayEventCard($playerId, $cardId));
            $creator->add(new \EA\Actions\Activation\ForceCardActivation($playerId, $cardId));
            $this->addInstantPayment($cardId, $creator, $ability);
            $this->addPlacementGain($cardId, $creator, $ability, null);
            $this->addInstantGain($cardId, $creator, $ability);
            $this->addInstantEventGain($creator);
            $this->addCommonActions($creator);
            $this->addCommonActions($creator, true);
            $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'eventSelectGain'));
            $creator->saveOrCommit();
        } else {
            $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
            $creator = \BX\Action\buildActionCommandCreator($playerId, $ability->mustGainCommit() || $ability->mustPaymentCommit() || $this->eventWillDrawCard($playerId));
            $creator->add(new \EA\Actions\Event\PlayEventCard($playerId, $cardId));
            $this->addInstantPayment($cardId, $creator, $ability);
            $this->addInstantGain($cardId, $creator, $ability);
            $this->addInstantEventGain($creator);
            $this->addCommonActions($creator);
            $this->addCommonActions($creator, true);
            $creator->add(new \BX\MultiActiveState\JumpPrivateStateActionCommand($playerId, $playerStateMgr->getPlayerReturnFromEventStateId($playerId)));
            $creator->saveOrCommit();
        }
    }

    public function eventGain(array $placedSproutList, array $placedGrowthList, array $selectedCompostFromHandCardIds, array $selectedHandChoosingCardIds)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction('eventGain');
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $cardId = $playerStateMgr->stateActivatedAfterCopyCardId($playerId);
        $ability = \EA\CardDefMgr::getByCardId($cardId)->abilityBlack();

        $creator = \BX\Action\buildActionCommandCreator($playerId, $ability->mustGainCommit());
        $creator->add(new \EA\Actions\Ability\PlaceSprout($playerId, $placedSproutList));
        $creator->add(new \EA\Actions\Ability\PlaceGrowth($playerId, $placedGrowthList));
        $creator->add(new \EA\Actions\Ability\PlaceCompostFromHand($playerId, $selectedCompostFromHandCardIds));
        $ability->foreachGain(function ($abilityId, $count) use ($playerId, $creator, $selectedHandChoosingCardIds) {
            if ($abilityId == \EA\ABILITY_DRAW_CARD_FROM_COMPOST) {
                $creator->add(new \EA\Actions\Event\EventKeepOneCard($playerId, $selectedHandChoosingCardIds));
            }
        });
        $creator->add(new \EA\Actions\Event\RestoreCardActivation($playerId));
        $this->addCommonActions($creator);
        $this->addCommonActions($creator, true);
        $creator->add(new \BX\MultiActiveState\JumpPrivateStateActionCommand($playerId, $playerStateMgr->getPlayerReturnFromEventStateId($playerId)));
        $creator->saveOrCommit();
    }

    public function eventPay(array $payedSproutList, array $payedGrowthList, array $payedCompostFromHandCardIds)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction('eventPay');
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $cardId = $playerStateMgr->stateActivatedAfterCopyCardId($playerId);
        $ability = \EA\CardDefMgr::getByCardId($cardId)->abilityBlack();

        $creator = \BX\Action\buildActionCommandCreator($playerId, $ability->mustGainCommit() || $ability->mustPaymentCommit() || $this->eventWillDrawCard($playerId));
        $this->addInstantPayment($cardId, $creator, $ability);
        $creator->add(new \EA\Actions\Ability\PaySprout($playerId, $ability, $payedSproutList));
        $creator->add(new \EA\Actions\Ability\PayGrowth($playerId, $ability, $payedGrowthList));
        $ability->foreachPayment(function ($abilityId, $count) use ($playerId, $creator, $payedCompostFromHandCardIds) {
            if ($abilityId == \EA\ABILITY_COMPOST_FROM_HAND) {
                $creator->add(new \EA\Actions\Ability\ExactCompostFromHand($playerId, $count, $payedCompostFromHandCardIds));
            }
        });
        $this->addInstantGain($cardId, $creator, $ability);
        $this->addInstantEventGain($creator);
        if ($ability->hasUserPlacementGain()) {
            $this->addPlacementGain($cardId, $creator, $ability, null);
            $this->addCommonActions($creator);
            $this->addCommonActions($creator, true);
            $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'eventSelectGain'));
        } else {
            $creator->add(new \EA\Actions\Event\RestoreCardActivation($playerId));
            $this->addCommonActions($creator);
            $this->addCommonActions($creator, true);
            $creator->add(new \BX\MultiActiveState\JumpPrivateStateActionCommand($playerId, $playerStateMgr->getPlayerReturnFromEventStateId($playerId)));
        }
        $creator->saveOrCommit();
    }

    private function eventWillDrawCard(int $playerId)
    {
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $card = $cardMgr->getPlayerIslandCard($playerId);
        $abilityBrown = $card->getCardDef()->abilityBrown();
        if ($abilityBrown === null) {
            return false;
        }
        $willDrawCard = false;
        $abilityBrown->foreachCondition(function ($conditionId, $conditionType) use (&$willDrawCard, $abilityBrown) {
            if ($conditionId != \EA\AB_COND_WHEN_PLAYING_EVENT) {
                return;
            }
            $abilityBrown->foreachGain(function ($abilityId, $count) use (&$willDrawCard) {
                if ($abilityId != \EA\ABILITY_DRAW_CARD_FROM_DECK) {
                    return;
                }
                $willDrawCard = true;
            });
        });
        return $willDrawCard;
    }

    private function addInstantEventGain(\BX\Action\ActionCommandCreatorInterface $creator)
    {
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $card = $cardMgr->getPlayerIslandCard($creator->getPlayerId());
        $cardId = $card->cardId;
        $abilityBrown = $card->getCardDef()->abilityBrown();
        if ($abilityBrown === null) {
            return;
        }
        $abilityBrown->foreachCondition(function ($conditionId, $conditionType) use ($creator, $abilityBrown, $cardId) {
            if ($conditionId != \EA\AB_COND_WHEN_PLAYING_EVENT) {
                return;
            }
            $abilityBrown->foreachGain(function ($abilityId, $count) use ($creator, $cardId) {
                switch ($abilityId) {
                    case \EA\ABILITY_DRAW_CARD_FROM_DECK:
                        $creator->add(new \EA\Actions\Ability\GainDrawCardFromDeck($creator->getPlayerId(), $count, false, true));
                        break;
                    case \EA\ABILITY_SOIL:
                        $creator->add(new \EA\Actions\Ability\GainSoil($creator->getPlayerId(), $count, $cardId));
                        break;
                    default:
                        throw new \BgaSystemException("BUG! Unsupported ability $abilityId");
                }
            });
        });
    }
}
