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

namespace EA\Actions\Event;

require_once(__DIR__ . '/.././../../BX/php/Action.php');
require_once('Traits.php');

class KeepReturnFromEventState extends \BX\Action\BaseActionCommand
{
    protected $privateStateIdInit;
    protected $privateStateId;

    public function __construct(int $playerId)
    {
        parent::__construct($playerId);
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if (!$this->privateStateIdInit) {
            if ($notifier->getBGAGameState()->isPlayerActive($this->playerId)) {
                $player = self::getMgr('player')->getByPlayerId($this->playerId);
                $this->privateStateId = $player->playerState;
            } else {
                $this->privateStateId = null;
            }
            $this->privateStateIdInit = true;
        }

        if ($this->privateStateId !== null) {
            switch ($this->privateStateId) {
                case STATE_EVENT_CHOOSE_CARD_ID:
                case STATE_EVENT_SELECT_PAYMENT_ID:
                case STATE_EVENT_SELECT_GAIN_ID:
                case STATE_CONVERT_SELECT_PAYMENT_ID:
                    throw new \BgaUserException($notifier->_('You cannot play an event right now'));
            }
        }

        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        $ps->modifyAction();
        $ps->returnFromEventStateId = $this->privateStateId;
        $ps->stateEventBeforeCopyCardId = $ps->stateActivatedBeforeCopyCardId;
        $ps->stateEventAfterCopyCardId = $ps->stateActivatedAfterCopyCardId;
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
    }
}

class KeepReturnFromSoloEndTurnEventState extends \BX\Action\BaseActionCommand
{
    public function __construct(int $playerId)
    {
        parent::__construct($playerId);
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        $ps->modifyAction();
        $ps->returnFromEventStateId = STATE_END_TURN_CHOOSE_ID ;
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier) {}
}

class RestoreCardActivation extends \BX\Action\BaseActionCommand
{
    public function __construct(int $playerId)
    {
        parent::__construct($playerId);
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        $ps->modifyAction();
        $ps->stateActivatedAfterCopyCardId = $ps->stateEventAfterCopyCardId;
        $ps->stateActivatedBeforeCopyCardId = $ps->stateEventBeforeCopyCardId;
        $ps->stateEventBeforeCopyCardId = null;
        $ps->stateEventAfterCopyCardId = null;
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
    }
}

class PlayEventCard extends \BX\Action\BaseActionCommand
{
    use \EA\Actions\Traits\CardQueryTrait;

    private $cardId;
    private $undoCard;
    private $undoEventCards;
    private $undoCardCounts;

    public function __construct(int $playerId, int $cardId)
    {
        parent::__construct($playerId);
        $this->cardId = $cardId;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $card = $this->cardFromHand($this->cardId);
        if (!$card->getCardDef()->isEvent()) {
            throw new \BgaUserException($notifier->_('You can only play event cards'));
        }
        $cardMgr = self::getMgr('card');
        $this->undoCard = \BX\META\deepClone($card);
        $this->undoEventCards = \BX\Meta\deepClone($cardMgr->getPlayerBoardEventCards($this->playerId));
        $this->undoCardCounts = \BX\Meta\deepClone($cardMgr->getCardCountsUIForPlayerId($this->playerId));

        $card->modifyAction();
        $card->moveToPlayerBoard($this->playerId);
        $notifier->notify(
            NTF_UPDATE_CARDS,
            clienttranslate('${player_name} plays an event: ${cardName}'),
            [
                'cards' => [$card],
                'cardName' => $card->getCardDef()->name,
                'i18n' => ['cardName'],
            ]
        );
        $notifier->notifyNoMessage(
            NTF_UPDATE_PLAYER_EVENT,
            [
                'cards' => $cardMgr->getPlayerBoardEventCards($this->playerId),
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
        $notifier->notifyNoMessage(NTF_UPDATE_CARDS, ['cards' => [$this->undoCard]]);
        $notifier->notifyNoMessage(NTF_UPDATE_PLAYER_EVENT, ['cards' => $this->undoEventCards]);
        if ($this->undoCardCounts !== null) {
            $notifier->notifyNoMessage(NTF_UPDATE_CARD_COUNTS, ['cardCounts' => $this->undoCardCounts]);
        }
    }
}

class EventKeepOneCard extends \BX\Action\BaseActionCommandNoUndo
{
    use \EA\Actions\Traits\CardQueryTrait;

    private $cardIds;

    public function __construct(int $playerId, array $selectedHandChoosingCardIds)
    {
        parent::__construct($playerId);
        $this->cardIds = $selectedHandChoosingCardIds;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if (count($this->cardIds) == 0) {
            return;
        }
        if (count($this->cardIds) > 1) {
            throw new \BgaUserException($notifier->_('You can only select one card to keep'));
        }
        $notifier->notify(
            \BX\Action\NTF_MESSAGE,
            clienttranslate('${player_name} keeps one of the drawn cards'),
            []
        );

        $card = $this->cardFromHand($this->cardIds[0]);
        $card->modifyAction();
        $card->moveToPlayerHand($this->playerId);
        $cardMgr = self::getMgr('card');
        $compostCards = [];
        foreach ($cardMgr->getPlayerHandChoosingCards($this->playerId) as $card) {
            $card->modifyAction();
            $card->moveToPlayerCompost($this->playerId);
            $compostCards[] = $card;
        }
        $notifier->notifyPrivateNoMessage(NTF_UPDATE_CARDS, ['cards' => $compostCards]);
        $notifier->notifyNoMessage(
            NTF_UPDATE_CARD_COUNTS,
            [
                'cardCounts' => $cardMgr->getCardCountsUIForPlayerId($this->playerId),
            ]
        );
    }
}
