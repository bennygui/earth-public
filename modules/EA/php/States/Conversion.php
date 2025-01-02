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

namespace EA\State\Conversion;

require_once(__DIR__ . '/.././../../BX/php/Action.php');
require_once(__DIR__ . '/.././../../BX/php/MultiActiveState.php');
require_once(__DIR__ . '/../Actions/Conversion.php');

trait GameStatesTrait
{
    public function convertPlay()
    {
        \BX\Lock\Locker::lock();
        $playerId = $this->getCurrentPlayerId();
        if ($this->gamestate->isPlayerActive($playerId)) {
            $this->checkAction('convertPlay');
        } else {
            $this->gamestate->checkPossibleAction('convertPlay');
        }
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        if (!$cardMgr->playerCanPlayConversion($playerId)) {
            throw new \BgaUserException($this->_('You do not have enough sprouts to convert'));
        }

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \EA\Actions\Conversion\KeepReturnFromConversionState($playerId));
        $creator->add(new \BX\MultiActiveState\JumpPrivateStateActionCommand($playerId, STATE_CONVERT_SELECT_PAYMENT_ID));
        $creator->save();
    }

    public function argsConvertPayment(int $playerId)
    {
        return \BX\MultiActiveState\argsMultiActive($playerId, function ($playerId) {
            $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
            return $this->argsMergeEarthBasic(
                [
                    'sproutCount' => $cardMgr->getPlayerSproutCount($playerId),
                    'sproutCards' => $cardMgr->getPlayerTableauCountSprout($playerId),
                    'sproutIcon' => clienttranslate('sprouts'),
                    'soilIcon' => clienttranslate('soil'),
                ]
            );
        });
    }

    public function convertSelectPayment(array $payedSproutList)
    {
        \BX\Lock\Locker::lock();
        $playerId = $this->getCurrentPlayerId();
        $this->checkAction('convertSelectPayment');
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $convert = new \EA\Actions\Conversion\ConvertSprout($playerId, $payedSproutList);
        $creator->add($convert);
        $creator->add(new \EA\Actions\Ability\GainSoil($playerId, $convert->getNbGainedSoil(), null));
        $this->addCommonActions($creator);
        $this->addCommonActions($creator, true);
        $creator->add(new \BX\MultiActiveState\JumpPrivateStateActionCommand($playerId, $playerStateMgr->getPlayerReturnFromConversionStateId($playerId)));
        $creator->save();
    }

    public function convertUseSeed()
    {
        \BX\Lock\Locker::lock();
        $playerId = $this->getCurrentPlayerId();
        if ($this->gamestate->isPlayerActive($playerId)) {
            $this->checkAction('convertUseSeed');
        } else {
            $this->gamestate->checkPossibleAction('convertUseSeed');
        }
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        if (!$this->playerCanUseSeed($playerId)) {
            throw new \BgaUserException($this->_('You do not have seed to convert'));
        }

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \EA\Actions\Conversion\KeepReturnFromConversionState($playerId));
        $creator->add(new \BX\MultiActiveState\JumpPrivateStateActionCommand($playerId, STATE_CONVERT_SELECT_USE_SEED_ID));
        $creator->save();
    }

    public function convertCreateSeed()
    {
        \BX\Lock\Locker::lock();
        $playerId = $this->getCurrentPlayerId();
        if ($this->gamestate->isPlayerActive($playerId)) {
            $this->checkAction('convertCreateSeed');
        } else {
            $this->gamestate->checkPossibleAction('convertCreateSeed');
        }
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        if (!$this->playerCanCreateSeed($playerId)) {
            throw new \BgaUserException($this->_('You do not have leaf or sprouts to convert'));
        }

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \EA\Actions\Conversion\KeepReturnFromConversionState($playerId));
        $creator->add(new \BX\MultiActiveState\JumpPrivateStateActionCommand($playerId, STATE_CONVERT_SELECT_CREATE_SEED_ID));
        $creator->save();
    }

    public function argsConvertSelectUseSeed(int $playerId)
    {
        return \BX\MultiActiveState\argsMultiActive($playerId, function ($playerId) {
            return $this->argsMergeEarthBasic([
                'seedIcon' => clienttranslate('seed'),
            ]);
        });
    }

    public function convertSelectUseSeed(int $abilityId)
    {
        \BX\Lock\Locker::lock();
        $playerId = $this->getCurrentPlayerId();
        $this->checkAction('convertSelectUseSeed');
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');

        switch ($abilityId) {
            case \EA\ABILITY_GERMINATE:
                throw new \BgaSystemException("abilityId ABILITY_GERMINATE has its own function");
                break;
            case \EA\ABILITY_SOIL:
                $creator = new \BX\Action\ActionCommandCreator($playerId);
                $creator->add(new \EA\Actions\Conversion\PaySeed($playerId));
                $creator->add(new \EA\Actions\Ability\GainSoil($playerId, 2, null));
                $this->addCommonActions($creator);
                $this->addCommonActions($creator, true);
                $creator->add(new \BX\MultiActiveState\JumpPrivateStateActionCommand($playerId, $playerStateMgr->getPlayerReturnFromConversionStateId($playerId)));
                $creator->save();
                break;
            case \EA\ABILITY_GROWTH:
                $creator = new \BX\Action\ActionCommandCreator($playerId);
                $creator->add(new \EA\Actions\Conversion\PaySeed($playerId));
                $creator->add(new \EA\Actions\Ability\GainGrowth($playerId, 2));
                $this->addCommonActions($creator);
                $this->addCommonActions($creator, true);
                $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'gain'));
                $creator->save();
                break;
            case \EA\ABILITY_SPROUT:
                $creator = new \BX\Action\ActionCommandCreator($playerId);
                $creator->add(new \EA\Actions\Conversion\PaySeed($playerId));
                $creator->add(new \EA\Actions\Ability\GainSprout($playerId, 3));
                $this->addCommonActions($creator);
                $this->addCommonActions($creator, true);
                $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'gain'));
                $creator->save();
                break;
            case \EA\ABILITY_COMPOST_FROM_DECK:
                $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
                $creator->add(new \EA\Actions\Conversion\PaySeed($playerId));
                $creator->add(new \EA\Actions\Ability\CompostFromDeck($playerId, 3));
                $this->addCommonActions($creator);
                $this->addCommonActions($creator, true);
                $creator->add(new \BX\MultiActiveState\JumpPrivateStateActionCommand($playerId, $playerStateMgr->getPlayerReturnFromConversionStateId($playerId)));
                $creator->commit();
                break;
            case \EA\ABILITY_COMPOST_FROM_HAND:
                $creator = new \BX\Action\ActionCommandCreator($playerId);
                $creator->add(new \EA\Actions\Conversion\PaySeed($playerId));
                $creator->add(new \EA\Actions\Ability\GainCompostFromHand($playerId, 4));
                $this->addCommonActions($creator);
                $this->addCommonActions($creator, true);
                $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'gain'));
                $creator->save();
                break;
            default:
                throw new \BgaSystemException("Unknown abilityId: $abilityId");
        }
    }

    public function convertSelectUseSeedGerminate(int $germinateId)
    {
        \BX\Lock\Locker::lock();
        $playerId = $this->getCurrentPlayerId();
        $this->checkAction('convertSelectUseSeedGerminate');
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');

        $germinateFilter = \EA\CardDef::germinateIdToFilter($germinateId);
        $card = $cardMgr->findFirstDeckDiscardCardMatchingFilterNow($germinateFilter);

        // Send deck and discard count since they might have been shuffled
        $this->notifyAllPlayers(
            NTF_UPDATE_CARD_COUNTS,
            '',
            [
                'cardCounts' => $cardMgr->getCardCountsUIDeckDiscard(),
            ]
        );

        $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
        if ($card === null) {
            $creator->add(new \EA\Actions\Conversion\GerminateFindsNoCard($playerId, $germinateId));
        } else {
            $creator->add(new \EA\Actions\Conversion\PaySeed($playerId));
            $creator->add(new \EA\Actions\Conversion\Germinate($playerId, $germinateId, $card->cardId));
            $this->addCommonActions($creator);
            $this->addCommonActions($creator, true);
        }
        $creator->add(new \BX\MultiActiveState\JumpPrivateStateActionCommand($playerId, $playerStateMgr->getPlayerReturnFromConversionStateId($playerId)));
        $creator->commit();
    }

    public function convertSelectUseSeedGain(array $placedSproutList, array $placedGrowthList, array $selectedCompostFromHandCardIds)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction('convertSelectUseSeedGain');
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \EA\Actions\Ability\PlaceSprout($playerId, $placedSproutList));
        $creator->add(new \EA\Actions\Ability\PlaceGrowth($playerId, $placedGrowthList));
        $creator->add(new \EA\Actions\Ability\PlaceCompostFromHand($playerId, $selectedCompostFromHandCardIds));
        $this->addCommonActions($creator);
        $this->addCommonActions($creator, true);
        $creator->add(new \BX\MultiActiveState\JumpPrivateStateActionCommand($playerId, $playerStateMgr->getPlayerReturnFromConversionStateId($playerId)));
        $creator->save();
    }

    public function argsConvertSelectCreateSeed(int $playerId)
    {
        return \BX\MultiActiveState\argsMultiActive($playerId, function ($playerId) {
            $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
            $leafTokenMgr = \BX\Action\ActionRowMgrRegister::getMgr('leaf_token');
            $leafs = $leafTokenMgr->getDiscardableLeafInOrder($playerId);
            $leafIdsOnFaunaBoard = [];
            foreach ($leafs as $t) {
                $leafIdsOnFaunaBoard[$t->tokenId] = $t->isOnFaunaBoardFauna();
            }
            return $this->argsMergeEarthBasic([
                'sproutCount' => $cardMgr->getPlayerSproutCount($playerId),
                'sproutCards' => $cardMgr->getPlayerTableauCountSprout($playerId),
                'leafIds' => array_map(fn ($t) => $t->tokenId, $leafs),
                'leafIdsOnFaunaBoard' => $leafIdsOnFaunaBoard,
                'seedIcon' => clienttranslate('seed'),
            ]);
        });
    }

    public function convertSelectCreateSeedFromSprouts(array $payedSproutList)
    {
        \BX\Lock\Locker::lock();
        $playerId = $this->getCurrentPlayerId();
        $this->checkAction('convertSelectCreateSeedFromSprouts');
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $convert = new \EA\Actions\Conversion\ConvertSproutToSeed($playerId, $payedSproutList);
        $creator->add($convert);
        $creator->add(new \EA\Actions\Ability\GainSeed($playerId, $convert->getNbGainedSeed()));
        $this->addCommonActions($creator);
        $this->addCommonActions($creator, true);
        $creator->add(new \BX\MultiActiveState\JumpPrivateStateActionCommand($playerId, $playerStateMgr->getPlayerReturnFromConversionStateId($playerId)));
        $creator->save();
    }

    public function convertSelectCreateSeedFromLeaf(int $tokenId)
    {
        \BX\Lock\Locker::lock();
        $playerId = $this->getCurrentPlayerId();
        $this->checkAction('convertSelectCreateSeedFromLeaf');
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \EA\Actions\Conversion\ConvertLeafToSeed($playerId, $tokenId));
        $creator->add(new \EA\Actions\Ability\GainSeed($playerId, 1));
        $this->addCommonActions($creator);
        $this->addCommonActions($creator, true);
        $creator->add(new \BX\MultiActiveState\JumpPrivateStateActionCommand($playerId, $playerStateMgr->getPlayerReturnFromConversionStateId($playerId)));
        $creator->save();
    }
}
