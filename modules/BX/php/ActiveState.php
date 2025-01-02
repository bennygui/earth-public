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

namespace BX\ActiveState;

require_once('Action.php');

function argsActive(int $playerId, callable $argsCallback)
{
    \BX\Action\ActionCommandMgr::apply($playerId);
    $ret = \BX\UI\deepCopyToArray($argsCallback($playerId));
    $ret['undoLevel'] = \BX\Action\ActionCommandMgr::undoLevel($playerId);
    \BX\Action\ActionCommandMgr::clear();
    return $ret;
}

trait GameStatesTrait
{
    public function argsDefaultActive()
    {
        $ret = [];
        foreach (array_keys($this->loadPlayersBasicInfos()) as $playerId) {
            $ret['_private'][$playerId] = argsActive($playerId, function ($playerId) {
                return [];
            });
        }
        return $ret;
    }

    public function argsCustomActive(callable $argsCallback)
    {
        $ret = [];
        foreach (array_keys($this->loadPlayersBasicInfos()) as $playerId) {
            $ret['_private'][$playerId] = argsActive($playerId, $argsCallback);
        }
        return $ret;
    }
}

class NextStateActionCommand extends \BX\Action\BaseActionCommand
{
    protected $transition;
    protected $prevStateIdInit;
    protected $prevStateId;
    protected $stateChanged;

    public function __construct(int $playerId, string $transition = '')
    {
        parent::__construct($playerId);
        $this->transition = $transition;
        $this->prevStateIdInit = false;
        $this->stateChanged = false;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if (!$this->prevStateIdInit) {
            $this->prevStateIdInit = true;
            $this->prevStateId = $notifier->getCurrentGameStateId();
        }
        if (!$this->stateChanged && $notifier->canChangeState()) {
            $this->stateChanged = true;
            $notifier->registerOnNotifierEndSingle(function ($notifier) {
                $notifier->getBGAGameState()->nextState($this->transition);
            });
        }
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($notifier->canChangeState()) {
            $notifier->registerOnNotifierEndSingle(function ($notifier) {
                $notifier->getBGAGameState()->jumpToState($this->prevStateId);
            });
        }
    }

    public function getTransition()
    {
        return $this->transition;
    }
}

class JumpStateActionCommand extends \BX\Action\BaseActionCommand
{
    protected $jumpToState;
    protected $prevStateIdInit;
    protected $prevStateId;
    protected $stateChanged;

    public function __construct(int $playerId, int $jumpToState)
    {
        parent::__construct($playerId);
        $this->jumpToState = $jumpToState;
        $this->prevStateIdInit = false;
        $this->stateChanged = false;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if (!$this->prevStateIdInit) {
            $this->prevStateIdInit = true;
            $this->prevStateId = $notifier->getCurrentGameStateId();
        }
        if (!$this->stateChanged && $notifier->canChangeState()) {
            $this->stateChanged = true;
            $notifier->registerOnNotifierEndSingle(function ($notifier) {
                $notifier->getBGAGameState()->jumpToState($this->jumpToState);
            });
        }
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($notifier->canChangeState()) {
            $notifier->registerOnNotifierEndSingle(function ($notifier) {
                $notifier->getBGAGameState()->jumpToState($this->prevStateId);
            });
        }
    }

    public function getJumpToState()
    {
        return $this->jumpToState;
    }
}