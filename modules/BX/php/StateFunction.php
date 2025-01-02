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

namespace BX\StateFunction;

require_once('Action.php');
require_once('ActiveState.php');

const STATE_FUNCTION_TABLE_NAME = 'bx_state_function';

class StateFunctionRow extends \BX\Action\BaseActionRow
{
    /** @dbcol @dbkey */
    public $playerId;
    /** @dbcol */
    public $functionCalls;

    public function __construct()
    {
        $this->playerId = null;
        $this->functionCalls = null;
        $this->setFunctionCalls([]);
    }

    public function setFunctionCalls(array $functionCalls)
    {
        $this->functionCalls = \BX\DB\convertFromValueToJsonForColumn($functionCalls);
    }

    public function getFunctionCalls()
    {
        return \BX\DB\convertFromJsonToValueForColumn($this->functionCalls);
    }

    public function pushFunctionCallAction(\BX\StateFunction\StateFunctionBase $function)
    {
        $this->modifyAction();
        $calls = $this->getFunctionCalls();
        array_push($calls, $function);
        $this->setFunctionCalls($calls);
    }

    public function popFunctionCallAction()
    {
        $this->modifyAction();
        $calls = $this->getFunctionCalls();
        $function = array_pop($calls);
        $this->setFunctionCalls($calls);
        return $function;
    }
}

class StateFunctionMgr extends \BX\Action\BaseActionRowMgr
{
    public function __construct()
    {
        parent::__construct(STATE_FUNCTION_TABLE_NAME, \BX\StateFunction\StateFunctionRow::class);
    }

    public function setup(array $playerIdArray)
    {
        foreach ($playerIdArray as $playerId) {
            $sf = $this->db->newRow();
            $sf->playerId = $playerId;
            $this->db->insertRow($sf);
        }
    }

    public function getByPlayerId(int $playerId)
    {
        return $this->getRowByKey($playerId);
    }

    function zombieRemoveAll(int $playerId)
    {
        $row = $this->getByPlayerId($playerId);
        $row->setFunctionCalls([]);
        $this->db->updateRow($row);
    }
}

function registerStateFunctionMgr()
{
    \BX\Action\ActionRowMgrRegister::registerMgr(STATE_FUNCTION_TABLE_NAME, \BX\StateFunction\StateFunctionMgr::class);
}

function stateFunctionSetup(array $playerIdArray)
{
    \BX\Action\ActionRowMgrRegister::getMgr(STATE_FUNCTION_TABLE_NAME)->setup($playerIdArray);
}

abstract class StateFunctionBase
{
    protected $enterStateId;
    protected $returnStateId;

    public function __construct(int $enterStateId, int $returnStateId)
    {
        $this->enterStateId = $enterStateId;
        $this->returnStateId = $returnStateId;
    }

    public function getEnterStateId()
    {
        return $this->enterStateId;
    }

    public function getReturnStateId()
    {
        return $this->returnStateId;
    }
}

class StateFunctionVoid extends StateFunctionBase
{
}

class StateFunctionCall extends \BX\Action\BaseActionCommand
{
    private $function;
    private $jumpAction;
    private $alwaysUndo;

    public function __construct(int $playerId, \BX\StateFunction\StateFunctionBase $function, bool $alwaysUndo = true)
    {
        parent::__construct($playerId);
        $this->function = $function;
        $this->jumpAction = new \BX\ActiveState\JumpStateActionCommand(
            $playerId,
            $function->getEnterStateId()
        );
        $this->alwaysUndo = $alwaysUndo;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $mgr = self::getMgr(STATE_FUNCTION_TABLE_NAME);
        $sf = $mgr->getByPlayerId($this->playerId);
        $sf->pushFunctionCallAction($this->function);
        $this->jumpAction->do($notifier);
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $this->jumpAction->undo($notifier);
    }

    public function mustAlwaysUndoAction()
    {
        return $this->alwaysUndo;
    }
}

class StateFunctionReturn extends \BX\Action\BaseActionCommand
{
    private $jumpAction;
    private $alwaysUndo;

    public function __construct(int $playerId, string $matchClass, bool $alwaysUndo = false)
    {
        parent::__construct($playerId);
        $fct = getLatestFunctionCall($playerId, $matchClass);
        $this->jumpAction = new \BX\ActiveState\JumpStateActionCommand(
            $playerId,
            $fct->getReturnStateId()
        );
        $this->alwaysUndo = $alwaysUndo;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $mgr = self::getMgr(STATE_FUNCTION_TABLE_NAME);
        $sf = $mgr->getByPlayerId($this->playerId);
        $sf->popFunctionCallAction();
        $this->jumpAction->do($notifier);
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $this->jumpAction->undo($notifier);
    }

    public function mustAlwaysUndoAction()
    {
        return $this->alwaysUndo;
    }
}

function getLatestFunctionCall(int $playerId, string $matchClass)
{
    $mgr = \BX\Action\ActionRowMgrRegister::getMgr(STATE_FUNCTION_TABLE_NAME);
    $sf = $mgr->getByPlayerId($playerId);
    $functions = $sf->getFunctionCalls();
    if (count($functions) == 0) {
        throw new \BgaSystemException('BUG! No function calls in getLatestFunctionCall');
    }
    $last = $functions[count($functions) - 1];
    if (!($last instanceof $matchClass)) {
        throw new \BgaSystemException("BUG! Last function call is not a subclass of $matchClass - " . get_class($last));
    }
    return $last;
}

function getAllFunctionCall(int $playerId, string $matchClass = null)
{
    $mgr = \BX\Action\ActionRowMgrRegister::getMgr(STATE_FUNCTION_TABLE_NAME);
    $sf = $mgr->getByPlayerId($playerId);
    $functions = array_reverse($sf->getFunctionCalls());
    return array_values(array_filter($functions, fn ($f) => $matchClass === null || $f instanceof $matchClass));
}

function updateLatestFunctionCallAction(int $playerId, string $matchClass, callable $callback)
{
    $mgr = \BX\Action\ActionRowMgrRegister::getMgr(STATE_FUNCTION_TABLE_NAME);
    $sf = $mgr->getByPlayerId($playerId);
    $functions = $sf->getFunctionCalls();
    if (count($functions) == 0) {
        throw new \BgaSystemException('BUG! No function calls in updateLatestFunctionCallAction ' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
    }
    $last = $functions[count($functions) - 1];
    if (!($last instanceof $matchClass)) {
        throw new \BgaSystemException("BUG! Last function call is not a subclass of $matchClass - " . get_class($last));
    }

    $callback($last);

    $sf->popFunctionCallAction();
    $sf->pushFunctionCallAction($last);
}

function zombieRemoveAll(int $playerId)
{
    $mgr = \BX\Action\ActionRowMgrRegister::getMgr(STATE_FUNCTION_TABLE_NAME);
    $mgr->zombieRemoveAll($playerId);
}
