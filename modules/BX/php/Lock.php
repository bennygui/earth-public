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

namespace BX\Lock;

class Locker
{
    public static function lock()
    {
        $db = new class extends \APP_DbObject
        {
            public function executeSelect(string $sql)
            {
                return $this->getObjectListFromDB($sql);
            }
        };

        $db->executeSelect("SELECT game_state_id FROM game_state WHERE 1 ORDER BY game_state_id FOR UPDATE");
        \BX\DB\RowMgrRegister::clearAllMgrCache();
        \BX\Action\ActionRowMgrRegister::clearAllMgrCache();
    }
}
