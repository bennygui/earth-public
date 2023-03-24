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

namespace EA\Actions\Activation;

require_once(__DIR__ . '/.././../../BX/php/Action.php');

class ChooseBoardOrTableau extends \BX\Action\BaseActionCommand
{
    private $activationDirection;
    private $playerHasChoice;

    public function __construct(int $playerId, int $activationDirection, bool $playerHasChoice)
    {
        parent::__construct($playerId);
        $this->activationDirection = $activationDirection;
        $this->playerHasChoice = $playerHasChoice;
        if (
            $activationDirection != \EA\ACTIVATION_DIRECTION_ISLAND_CLIMATE_TABLEAU
            &&
            $activationDirection != \EA\ACTIVATION_DIRECTION_TABLEAU_ISLAND_CLIMATE
        ) {
            throw new \BgaSystemException("BUG! Invalid activation direction: $activationDirection");
        }
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        $ps->modifyAction();
        $ps->stateActivationDirection = $this->activationDirection;
        if ($this->playerHasChoice) {
            if ($this->activationDirection == \EA\ACTIVATION_DIRECTION_ISLAND_CLIMATE_TABLEAU) {
                $notifier->notify(
                    \BX\Action\NTF_MESSAGE,
                    clienttranslate('${player_name} chooses activation order: Island - Climate - Tableau'),
                    []
                );
            } else {
                $notifier->notify(
                    \BX\Action\NTF_MESSAGE,
                    clienttranslate('${player_name} chooses activation order: Tableau - Island - Climate'),
                    []
                );
            }
        }
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
    }
}

class MarkActivatingNextCard extends \BX\Action\BaseActionCommand
{
    private $nextCardId;

    public function __construct(int $playerId)
    {
        parent::__construct($playerId);
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $playerStateMgr = self::getMgr('player_state');
        $direction = $playerStateMgr->getPlayerActivationDirection($this->playerId);
        $currentCardId = $playerStateMgr->stateActivatedBeforeCopyCardId($this->playerId);
        $this->nextCardId = self::getNextActivationCardId($this->playerId, $direction, $currentCardId);

        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        $ps->modifyAction();
        $ps->stateActivatedBeforeCopyCardId = $this->nextCardId;
        $ps->stateActivatedAfterCopyCardId = $this->nextCardId;
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
    }

    public function hasNextCard()
    {
        return ($this->nextCardId !== null);
    }

    public static function playerHasActivatableCards(int $playerId)
    {
        if (self::getNextActivationCardId($playerId, \EA\ACTIVATION_DIRECTION_ISLAND_CLIMATE_TABLEAU, null) === null) {
            return false;
        }
        return true;
    }

    public static function playerMustChooseDirection(int $playerId)
    {
        $dir1CardId = self::getNextActivationCardId($playerId, \EA\ACTIVATION_DIRECTION_ISLAND_CLIMATE_TABLEAU, null);
        $dir2CardId = self::getNextActivationCardId($playerId, \EA\ACTIVATION_DIRECTION_TABLEAU_ISLAND_CLIMATE, null);
        return ($dir1CardId != $dir2CardId);
    }

    private static function getNextActivationCardId(int $playerId, int $direction, ?int $currentCardId)
    {
        $gameStateMgr = self::getMgr('game_state');
        $mainActionId = $gameStateMgr->getActiveMainActionId();
        $activePlayerId = $gameStateMgr->activePlayerId();

        $cardMgr = self::getMgr('card');
        $cards = $cardMgr->getPlayerTableauCards($playerId, $playerId);
        if ($direction == \EA\ACTIVATION_DIRECTION_ISLAND_CLIMATE_TABLEAU) {
            array_unshift($cards, $cardMgr->getPlayerClimateCard($playerId));
            array_unshift($cards, $cardMgr->getPlayerIslandCard($playerId));
        } else {
            $cards[] = $cardMgr->getPlayerIslandCard($playerId);
            $cards[] = $cardMgr->getPlayerClimateCard($playerId);
        }
        $cards = $cardMgr->filterActivatableCards($cards, $playerId, $activePlayerId, $mainActionId);
        if ($currentCardId !== null) {
            while (count($cards) > 0) {
                $card = array_shift($cards);
                if ($card->cardId == $currentCardId) {
                    break;
                }
            }
        }
        if (count($cards) > 0) {
            return array_values($cards)[0]->cardId;
        } else {
            return null;
        }
    }
}

class MarkActivatingCopyCard extends \BX\Action\BaseActionCommand
{
    private $copyCardId;

    public function __construct(int $playerId, int $copyCardId)
    {
        parent::__construct($playerId);
        $this->copyCardId = $copyCardId;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $gameStateMgr = self::getMgr('game_state');
        $playerStateMgr = self::getMgr('player_state');
        $cardMgr = self::getMgr('card');

        $cardId = $playerStateMgr->stateActivatedAfterCopyCardId($this->playerId);
        $mainActionId = $gameStateMgr->getActiveMainActionId();
        $activePlayerId = $gameStateMgr->activePlayerId();
        $validCardIds = array_filter(
            array_map(
                fn ($c) => $c->cardId,
                $cardMgr->getIslandClimateTableauPlayerCardsWithAbilityMatchingMainAction(
                    $this->playerId,
                    $activePlayerId,
                    $mainActionId
                )
            ),
            fn ($cId) => $cId != $cardId
        );
        if (array_search($this->copyCardId, $validCardIds) === false) {
            throw new \BgaUserException($notifier->translate(clienttranslate('You must copy one of your cards')));
        }

        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        $ps->modifyAction();
        $ps->stateActivatedAfterCopyCardId = $this->copyCardId;
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
    }
}

class ForceCardActivation extends \BX\Action\BaseActionCommand
{
    private $cardId;

    public function __construct(int $playerId, int $cardId)
    {
        parent::__construct($playerId);
        $this->cardId = $cardId;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        $ps->modifyAction();
        $ps->stateActivatedAfterCopyCardId = $this->cardId;
        $ps->stateActivatedBeforeCopyCardId = $this->cardId;
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
    }
}

class ClearCardActivation extends \BX\Action\BaseActionCommand
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
        $ps->stateActivatedAfterCopyCardId = null;
        $ps->stateActivatedBeforeCopyCardId = null;
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
    }
}

class SkipCardNotification extends \BX\Action\BaseActionCommand
{
    private $cardId;

    public function __construct(int $playerId, int $cardId)
    {
        parent::__construct($playerId);
        $this->cardId = $cardId;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $cardDef = \EA\CardDefMgr::getByCardId($this->cardId);
        if ($cardDef === null) {
            throw new \BgaSystemException("BUG! Invalid cardId {$this->cardId}");
        }
        $notifier->notify(
            \BX\Action\NTF_MESSAGE,
            clienttranslate('${player_name} skips activating ${cardName}'),
            [
                'cardName' => $cardDef->name,
                'i18n' => ['cardName'],
            ]
        );
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
    }
}

class ActivatedCardNotification extends \BX\Action\BaseActionCommand
{
    private $cardId;

    public function __construct(int $playerId, int $cardId)
    {
        parent::__construct($playerId);
        $this->cardId = $cardId;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $cardDef = \EA\CardDefMgr::getByCardId($this->cardId);
        if ($cardDef === null) {
            throw new \BgaSystemException("BUG! Invalid cardId {$this->cardId}");
        }
        $notifier->notify(
            \BX\Action\NTF_MESSAGE,
            clienttranslate('${player_name} activates ${cardName}'),
            [
                'cardName' => $cardDef->name,
                'i18n' => ['cardName'],
            ]
        );
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
    }
}