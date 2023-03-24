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

namespace EA\State\Confirm;

require_once(__DIR__ . '/.././../../BX/php/Action.php');
require_once(__DIR__ . '/.././../../BX/php/MultiActiveState.php');

trait GameStatesTrait
{
    public function confirmEndPhase()
    {
        \BX\Lock\Locker::lock();
        $playerId = $this->getCurrentPlayerId();
        $this->checkAction('confirmEndPhase');
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        // Commit if there are no undo so that there are no undo button
        $creator = \BX\Action\buildActionCommandCreator($playerId, \BX\Action\ActionCommandMgr::count($playerId) == 0);
        $creator->add(new \BX\MultiActiveState\ExitPrivateStateActionCommand($playerId));
        $creator->saveOrCommit();
    }
}
