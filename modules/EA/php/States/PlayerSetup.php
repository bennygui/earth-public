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

namespace EA\State\PlayerSetup;

require_once(__DIR__ . '/../../../BX/php/Action.php');
require_once(__DIR__ . '/../../../BX/php/MultiActiveState.php');
require_once(__DIR__ . '/../Actions/PlayerSetup.php');
require_once(__DIR__ . '/../Actions/Ability.php');
require_once(__DIR__ . '/../CardDefMgr.php');

trait GameStatesTrait
{
    public function stPlayerSetup()
    {
        $this->gamestate->setAllPlayersMultiactive();
        $this->gamestate->initializePrivateStateForAllActivePlayers();
    }

    public function argsPlayerSetupChooseInitialCards(int $playerId)
    {
        return \BX\MultiActiveState\argsMultiActive($playerId, function ($playerId) {
            $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
            $cardsHand = $cardMgr->getPlayerHandCards($playerId);
            $groups = [];
            foreach ($cardsHand as $card) {
                $groups[\EA\CardDefMgr::getByCardId($card->cardId)->type][] = $card->cardId;
            }
            $ret = ['cardIdGroups' => []];
            foreach ($cardsHand as $card) {
                $ret['cardIdGroups'][$card->cardId] = $groups[\EA\CardDefMgr::getByCardId($card->cardId)->type];
            }
            $ret['nbCardsToSelect'] = (isGameModeBeginner() ? 2 : 3);
            $ret['message'] = isGameModeBeginner()
                ? clienttranslate('one Island (blue) and one Climate (orange)')
                : clienttranslate('one Island (blue), one Climate (orange) and one Ecosystem (green)');
            $ret['i18n'] = ['message'];
            return $ret;
        });
    }

    public function playerSetupChoose(array $cardIds)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction('playerSetupChoose');
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
        $chooseAction = new \EA\Actions\PlayerSetup\Choose($playerId, $cardIds);
        $creator->add($chooseAction);
        $creator->add(new \EA\Actions\Ability\GainDrawCardFromDeck($playerId, $chooseAction->nbCardsToDraw()));
        $creator->add(new \EA\Actions\Ability\GainSoil($playerId, $chooseAction->nbSoilToGain(), $chooseAction->getIslandCardId()));
        $this->addCommonActions($creator);
        if ($chooseAction->hasCardsToCompost()) {
            $creator->add(new \BX\MultiActiveState\NextPrivateStateActionCommand($playerId, 'hasCardToCompost'));
        } else {
            $this->addNextConfirmEndPhaseOrExit($playerId, $creator);
        }
        $creator->commit();
    }

    public function argsPlayerSetupCompostCards(int $playerId)
    {
        return \BX\MultiActiveState\argsMultiActive($playerId, function ($playerId) {
            $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
            $card = $cardMgr->getPlayerIslandCard($playerId);
            $ret = [
                'compostFromHandCount' => $card === null ? 0 : $card->getCardDef()->abilityBlack()->paymentCountCompostFromHand(),
                'handCardIds' => array_keys($cardMgr->getPlayerHandCards($playerId)),
            ];
            return $ret;
        });
    }

    public function playerSetupCompost(array $cardIds)
    {
        \BX\Lock\Locker::lock();
        $this->checkAction('playerSetupCompost');
        $playerId = $this->getCurrentPlayerId();
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $card = $cardMgr->getPlayerIslandCard($playerId);
        $compostFromHandCount = $card->getCardDef()->abilityBlack()->paymentCountCompostFromHand();

        $creator = new \BX\Action\ActionCommandCreator($playerId);
        $creator->add(new \EA\Actions\Ability\ExactCompostFromHand($playerId, $compostFromHandCount, $cardIds));
        $this->addCommonActions($creator);
        $this->addNextConfirmEndPhaseOrExit($playerId, $creator);
        $creator->save();
    }
}
