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

namespace EA\Actions\EndTurn;

require_once(__DIR__ . '/.././../../BX/php/Action.php');
require_once('Traits.php');

class EndTurnPass extends \BX\Action\BaseActionCommand
{
    private $lastSeenExchangeSproutCount;

    public function __construct(int $playerId)
    {
        parent::__construct($playerId);
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $playerStateMgr = self::getMgr('player_state');
        $playerExchangeMgr = self::getMgr('player_exchange');
        
        if ($this->lastSeenExchangeSproutCount === null) {
            $this->lastSeenExchangeSproutCount = $playerExchangeMgr->getPlayerSproutCount($this->playerId);
        }

        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        $ps->modifyAction();
        $ps->lastSeenExchangeSproutCount = $this->lastSeenExchangeSproutCount;
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
    }
}

class ChooseEndTurnEventCard extends \BX\Action\BaseActionCommandNoUndo
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
        $card = $this->cardFromHand($this->cardId);
        if (!$card->getCardDef()->isEndTurnEvent()) {
            throw new \BgaUserException($notifier->_('You can only play end turn event cards'));
        }

        $card->modifyAction();
        $card->moveToEndTurn();
        $notifier->notify(
            NTF_UPDATE_CARDS,
            clienttranslate('${player_name} plays an end turn event: ${cardName}'),
            [
                'cards' => [$card],
                'cardName' => $card->getCardDef()->name,
                'i18n' => ['cardName'],
            ]
        );
    }
}

class KeepReturnFromEndTurnEventState extends \BX\Action\BaseActionCommand
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
        $ps->returnFromEventStateId = STATE_CONFIRM_END_PHASE_ID;
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
    }
}

class MoveEndTurnEventCardToPlayer extends \BX\Action\BaseActionCommandNoUndo
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
        $cardMgr = self::getMgr('card');
        $card = $cardMgr->getCardById($this->cardId);

        $card->modifyAction();
        $card->moveToPlayerBoard($this->playerId);
        $notifier->notify(
            NTF_UPDATE_CARDS,
            clienttranslate('${player_name} moves their end turn event to their player board: ${cardName}'),
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
    }
}

class ConfirmDoNotSkipEndTurn extends \BX\Action\BaseActionCommand
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
        $ps->skipEndOfTurn = false;
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
    }
}


class ConfirmSkipEndTurn extends \BX\Action\BaseActionCommand
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
        $ps->skipEndOfTurn = true;

        $notifier->notify(
            \BX\Action\NTF_MESSAGE,
            clienttranslate('${player_name} does not want to be activated for the End of Turn phase'),
            []
        );
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
    }
}
