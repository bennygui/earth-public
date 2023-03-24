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

namespace BX\MultiActiveState;

require_once('Action.php');

const NTF_MULTI_ACTIVE_ARGS = 'NTF_MULTI_ACTIVE_ARGS';

function argsMultiActive(int $playerId, callable $argsCallback)
{
    \BX\Action\ActionCommandMgr::apply($playerId);
    $ret = \BX\UI\deepCopyToArray($argsCallback($playerId));
    $ret['undoLevel'] = \BX\Action\ActionCommandMgr::count($playerId);
    \BX\Action\ActionCommandMgr::clear();
    return $ret;
}

trait GameStatesTrait
{
    public function argsDefaultMultiActive()
    {
        $ret = [];
        foreach (array_keys($this->loadPlayersBasicInfos()) as $playerId) {
            $ret['_private'][$playerId] = argsMultiActive($playerId, function ($playerId) {
                return [];
            });
        }
        return $ret;
    }

    public function argsCustomMultiActive(callable $argsCallback)
    {
        $ret = [];
        foreach (array_keys($this->loadPlayersBasicInfos()) as $playerId) {
            $ret['_private'][$playerId] = argsMultiActive($playerId, $argsCallback);
        }
        return $ret;
    }
}

class NextPrivateStateActionCommand extends \BX\Action\BaseActionCommand
{
    protected $transition;
    protected $prevPrivateStateIdInit;
    protected $prevPrivateStateId;

    public function __construct(int $playerId, string $transition = '')
    {
        parent::__construct($playerId);
        $this->transition = $transition;
        $this->prevPrivateStateIdInit = false;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if (!$this->prevPrivateStateIdInit) {
            $player = self::getMgr('player')->getByPlayerId($this->playerId);
            $this->prevPrivateStateIdInit = true;
            $this->prevPrivateStateId = $player->playerState;
        }
        if ($notifier->canChangeState()) {
            $notifier->registerOnNotifierEndSingle(function ($notifier) {
                $notifier->getBGAGameState()->nextPrivateState($this->playerId, $this->transition);
            });
        }
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($notifier->canChangeState()) {
            $notifier->registerOnNotifierEndSingle(function ($notifier) {
                if (!$notifier->getBGAGameState()->isPlayerActive($this->playerId)) {
                    $notifier->getBGAGameState()->setPlayersMultiactive([$this->playerId], null);
                }
                $notifier->getBGAGameState()->setPrivateState($this->playerId, $this->prevPrivateStateId);
            });
        }
    }

    public function getTransition()
    {
        return $this->transition;
    }
}

class JumpPrivateStateActionCommand extends \BX\Action\BaseActionCommand
{
    protected $jumpPrivateStateId;
    protected $prevPrivateStateIdInit;
    protected $prevPrivateStateId;

    public function __construct(int $playerId, ?int $jumpPrivateStateId)
    {
        parent::__construct($playerId);
        $this->jumpPrivateStateId = $jumpPrivateStateId;
        $this->prevPrivateStateIdInit = false;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if (!$this->prevPrivateStateIdInit) {
            if ($notifier->getBGAGameState()->isPlayerActive($this->playerId)) {
                $player = self::getMgr('player')->getByPlayerId($this->playerId);
                $this->prevPrivateStateId = $player->playerState;
            } else {
                $this->prevPrivateStateId = null;
            }
            $this->prevPrivateStateIdInit = true;
        }
        if ($notifier->canChangeState()) {
            $notifier->registerOnNotifierEndSingle(function ($notifier) {
                if ($this->jumpPrivateStateId === null) {
                    $activePlayers = $notifier->getBGAGameState()->getActivePlayerList();
                    $notifier->getBGAGameState()->setPlayerNonMultiactive($this->playerId, null);
                    if (count($activePlayers) > 1) {
                        // Resend args since the framework calculates it too soon
                        ExitPrivateStateActionCommand::sendStateArgs($this->playerId, $notifier);
                    }
                } else {
                    $notifier->getBGAGameState()->setPrivateState($this->playerId, $this->jumpPrivateStateId);
                    if (!$notifier->getBGAGameState()->isPlayerActive($this->playerId)) {
                        $notifier->getBGAGameState()->setPlayersMultiactive([$this->playerId], null);
                    }
                }
            });
        }
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($notifier->canChangeState()) {
            $notifier->registerOnNotifierEndSingle(function ($notifier) {
                if ($this->prevPrivateStateId === null) {
                    $notifier->getBGAGameState()->setPlayerNonMultiactive($this->playerId, null);
                    // Resend args since the framework calculates it too soon
                    ExitPrivateStateActionCommand::sendStateArgs($this->playerId, $notifier);
                } else {
                    if (!$notifier->getBGAGameState()->isPlayerActive($this->playerId)) {
                        $notifier->getBGAGameState()->setPlayersMultiactive([$this->playerId], null);
                    }
                    $notifier->getBGAGameState()->setPrivateState($this->playerId, $this->prevPrivateStateId);
                }
            });
        }
    }
}

class ExitPrivateStateActionCommand extends \BX\Action\BaseActionCommand
{
    protected $transition;
    protected $prevPrivateStateIdInit;
    protected $prevPrivateStateId;

    public function __construct(int $playerId, string $transition = '')
    {
        parent::__construct($playerId);
        $this->transition = $transition;
        $this->prevPrivateStateIdInit = false;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if (!$this->prevPrivateStateIdInit) {
            $player = self::getMgr('player')->getByPlayerId($this->playerId);
            $this->prevPrivateStateIdInit = true;
            $this->prevPrivateStateId = $player->playerState;
        }
        if ($notifier->canChangeState()) {
            $notifier->registerOnNotifierEndSingle(function ($notifier) {
                $activePlayers = $notifier->getBGAGameState()->getActivePlayerList();
                $notifier->getBGAGameState()->setPlayerNonMultiactive($this->playerId, $this->transition);
                if (count($activePlayers) > 1) {
                    // Resend args since the framework calculates it too soon
                    self::sendStateArgs($this->playerId, $notifier);
                }
            });
        }
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($notifier->canChangeState()) {
            $notifier->registerOnNotifierEndSingle(function ($notifier) {
                if (!$notifier->getBGAGameState()->isPlayerActive($this->playerId)) {
                    $notifier->getBGAGameState()->setPlayersMultiactive([$this->playerId], null);
                }
                $notifier->getBGAGameState()->setPrivateState($this->playerId, $this->prevPrivateStateId);
            });
        }
    }

    public function getTransition()
    {
        return $this->transition;
    }

    public static function sendStateArgs(int $playerId, \BX\Action\BaseActionCommandNotifier $notifier)
    {
        $stateId = $notifier->getCurrentGameStateId();
        $states = $notifier->getGameStates();
        if (array_key_exists($stateId, $states)) {
            $state = $states[$stateId];
            if (\array_key_exists('args', $state)) {
                $args = $state['args'];
                $argsValue = $notifier->getBGAGame()->$args();
                if (\array_key_exists('_private', $argsValue)) {
                    if (\array_key_exists($playerId, $argsValue['_private'])) {
                        $argsValue['_private'] = $argsValue['_private'][$playerId];
                    }
                }
                $notifier->notifyPrivateNoMessage(NTF_MULTI_ACTIVE_ARGS, $argsValue);
            }
        }
    }
}
