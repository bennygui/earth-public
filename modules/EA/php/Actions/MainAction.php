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

namespace EA\Actions\MainAction;

require_once(__DIR__ . '/.././../../BX/php/Action.php');

class Choose extends \BX\Action\BaseActionCommandNoUndo
{
    private $mainActionId;

    public function __construct(int $playerId, int $mainActionId)
    {
        parent::__construct($playerId);
        $this->mainActionId = $mainActionId;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if (array_search($this->mainActionId, self::getChoosableMainActionIds($this->playerId)) === false) {
            throw new \BgaUserException($notifier->_('This action is not available'));
        }
        $leafMgr = $this->getMgr('leaf_token');
        foreach ($leafMgr->getActionLeafTokenForAllPlayers() as $leafToken) {
            $leafToken->modifyAction();
            $leafToken->moveToAction($this->mainActionId);
            $notifier->notifyNoMessage(
                NTF_UPDATE_LEAF_TOKEN,
                [
                    'leafToken' => $leafToken->toPlayerUI($this->playerId),
                ]
            );
        }
        $notifier->notify(
            \BX\Action\NTF_MESSAGE,
            clienttranslate('${player_name} chooses action: ${mainActionName} (${mainActionColorName})'),
            [
                'mainActionName' => getMainActionName($this->mainActionId),
                'mainActionColorName' => getMainActionColorName($this->mainActionId),
                'i18n' => ['mainActionName'],
            ]
        );
        $this->getMgr('game_state')->actionActivateMainAction($this->mainActionId);
    }

    public static function getChoosableMainActionIds(int $playerId)
    {
        $cardMgr = self::getMgr('card');
        $actions = MAIN_ACTION_IDS;
        foreach ($cardMgr->getPlayerTableauCards($playerId, $playerId) as $card) {
            $abilityBrown = $card->getCardDef()->abilityBrown();
            if ($abilityBrown === null) {
                continue;
            }
            $abilityBrown->foreachGain(function ($abilityId, $count) use (&$actions, $abilityBrown) {
                if ($abilityId != \EA\ABILITY_CANNOT_CHOOSE_COLOR) {
                    return;
                }
                $abilityColor = $abilityBrown->getIfChooseColorCondition();
                $mainActionId = \EA\CardDef::abilityColorToMainAction($abilityColor);
                $index = array_search($mainActionId, $actions);
                if ($index !== false) {
                    unset($actions[$index]);
                }
            });
        }
        return array_values($actions);
    }
}

class PlantCard extends \BX\Action\BaseActionCommand
{
    use \EA\Actions\Traits\CardQueryTrait;

    private $cardId;
    private $posX;
    private $posY;
    private $undoCard;
    private $undoCardCounts;
    private $undoTableau;

    public function __construct(int $playerId, int $cardId, int $posX, int $posY)
    {
        parent::__construct($playerId);
        $this->cardId = $cardId;
        $this->posX = $posX;
        $this->posY = $posY;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $card = $this->cardFromHand($this->cardId);
        if (!$card->getCardDef()->isPlantable()) {
            throw new \BgaSystemException("BUG! cardId {$this->cardId} is not plantable");
        }
        $cardMgr = self::getMgr('card');
        if (!$cardMgr->canPlantCardAtPosition($this->playerId, $this->cardId, $this->posX, $this->posY)) {
            throw new \BgaSystemException("BUG! Cannot plant cardId {$this->cardId} at ({$this->posX}, {$this->posX}) for playerId {$this->playerId}");
        }
        $this->undoCard = \BX\META\deepClone($card);
        $this->undoCardCounts = \BX\Meta\deepClone($cardMgr->getCardCountsUIForPlayerId($this->playerId));
        $this->undoTableau = \BX\META\deepClone($cardMgr->getPlayerTableauCards($this->playerId, $this->playerId));

        $playerStateMgr = self::getMgr('player_state');
        $playerStateMgr->addPlayerPlantedCard($this->playerId, $this->cardId);

        $card->modifyAction();
        $card->moveToPlayerTableau($this->playerId, $this->posX, $this->posY);
        $notifier->notify(
            NTF_UPDATE_PLAYER_TABLEAU,
            $cardMgr->isTableauFilledForPlayer($this->playerId)
                ? clienttranslate('${player_name} plants a card from their hand and fills their tableau')
                : clienttranslate('${player_name} plants a card from their hand'),
            [
                'tableauCards' => \EA\cardsToCompactUI($cardMgr->getPlayerTableauCards($this->playerId, null)),
            ]
        );
        $notifier->notifyPrivateNoMessage(
            NTF_UPDATE_PLAYER_TABLEAU,
            [
                'tableauCards' => \EA\cardsToCompactUI($cardMgr->getPlayerTableauCards($this->playerId, $this->playerId)),
            ]
        );
        $notifier->notifyNoMessage(
            NTF_UPDATE_CARD_COUNTS,
            [
                'cardCounts' => $cardMgr->getCardCountsUIForPlayerId($this->playerId),
            ]
        );
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $notifier->notifyPrivateNoMessage(NTF_UPDATE_CARDS, ['cards' => [$this->undoCard]]);
        $notifier->notifyNoMessage(NTF_UPDATE_CARD_COUNTS, ['cardCounts' => $this->undoCardCounts]);
        $notifier->notifyNoMessage(NTF_UPDATE_PLAYER_TABLEAU, ['tableauCards' => \EA\cardsToCompactUI($this->undoTableau)]);
    }
}

class SkipPlantingCard extends \BX\Action\BaseActionCommand
{
    private $undoTableau;

    public function __construct(int $playerId)
    {
        parent::__construct($playerId);
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $cardMgr = self::getMgr('card');
        $this->undoTableau = \BX\META\deepClone($cardMgr->getPlayerTableauCards($this->playerId, $this->playerId));
        $notifier->notify(
            \BX\Action\NTF_MESSAGE,
            clienttranslate('${player_name} skips planting a card'),
            []
        );
        $notifier->notifyPrivateNoMessage(
            NTF_UPDATE_PLAYER_TABLEAU,
            [
                'tableauCards' => \EA\cardsToCompactUI($cardMgr->getPlayerTableauCards($this->playerId, $this->playerId)),
            ]
        );
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $notifier->notifyNoMessage(NTF_UPDATE_PLAYER_TABLEAU, ['tableauCards' => \EA\cardsToCompactUI($this->undoTableau)]);
    }
}

class PlantKeepCard extends \BX\Action\BaseActionCommandNoUndo
{
    use \EA\Actions\Traits\CardQueryTrait;

    private $cardId;

    public function __construct(int $playerId, int $cardId)
    {
        parent::__construct($playerId);
        $this->cardId = $cardId;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $notifier->notify(
            \BX\Action\NTF_MESSAGE,
            clienttranslate('${player_name} keeps one of the 4 drawn cards'),
            []
        );

        $isSolo = isGameSolo();
        $card = $this->cardFromHand($this->cardId);
        $card->modifyAction();
        $card->moveToPlayerHand($this->playerId);
        $cardMgr = self::getMgr('card');
        $discardCards = [];
        foreach ($cardMgr->getPlayerHandChoosingCards($this->playerId) as $card) {
            $card->modifyAction();
            if ($isSolo) {
                $card->moveToGaiaTableau();
            } else {
                $card->moveToDiscard();
            }
            $discardCards[] = $card;
        }
        $notifier->notifyPrivateNoMessage(NTF_UPDATE_CARDS, ['cards' => $discardCards]);
        $notifier->notifyNoMessage(
            NTF_UPDATE_CARD_COUNTS,
            [
                'cardCounts' => $cardMgr->getCardCountsUIDeckDiscard(),
            ]
        );
        $notifier->notifyNoMessage(
            NTF_UPDATE_CARD_COUNTS,
            [
                'cardCounts' => $cardMgr->getCardCountsUIForPlayerId($this->playerId),
            ]
        );
        if ($isSolo) {
            $notifier->notifyNoMessage(
                NTF_UPDATE_GAIA,
                [
                    'gaiaCount' => self::getMgr('game_state')->getGaiaCount(),
                    'gaiaTableauCards' => $cardMgr->getGaiaTableauCards(),
                    'gaiaDiscardCards' => $cardMgr->getGaiaDiscardCards(),
                ]
            );
        }
    }
}

class RevealTableau extends \BX\Action\BaseActionCommandNoUndo
{
    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $cardMgr = self::getMgr('card');
        $privateTableauCards = $cardMgr->getPlayerPrivateTableauCards($this->playerId);
        if (count($privateTableauCards) > 0) {
            foreach ($privateTableauCards as $card) {
                $card->modifyAction();
                $card->markPublic();
                $notifier->notify(
                    \BX\Action\NTF_MESSAGE,
                    clienttranslate('${player_name} reveals their tableau: ${cardName} ${cardImage}'),
                    [
                        'cardName' => $card->getCardDef()->name,
                        'cardImage' => $card->cardId,
                        'i18n' => ['cardName'],
                    ]
                );
            }
            $notifier->notifyNoMessage(
                NTF_UPDATE_PLAYER_TABLEAU,
                [
                    'tableauCards' => \EA\cardsToCompactUI($cardMgr->getPlayerTableauCards($this->playerId, null)),
                ]
            );
        }
    }
}
