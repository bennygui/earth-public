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

namespace EA\State\SeenLeafToken;

require_once(__DIR__ . '/.././../../BX/php/Action.php');

trait GameStatesTrait
{
    public function seeFaunaObjective()
    {
        \BX\Lock\Locker::lock();
        $playerId = $this->getCurrentPlayerId();
        if (array_search($playerId, $this->getPlayerIdArray()) === false) {
            throw new \BgaSystemException('BUG! Only players can see fauna objective');
        }
        \BX\Action\ActionCommandMgr::apply($playerId);

        $this->updateSeenFaunaObjective($playerId);
    }
}
