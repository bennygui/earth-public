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
    private static $registedTables = [];
    private static $columnForTable = [];

    public static function registerTableColumn(string $tableName, $columNames)
    {
        if (!array_key_exists($tableName, self::$columnForTable)) {
            self::$registedTables[] = $tableName;
        }
        if (is_array($columNames)) {
            self::$columnForTable[$tableName] = $columNames;
        } else {
            self::$columnForTable[$tableName] = [$columNames];
        }
    }

    public static function lock()
    {
        $db = new class extends \APP_DbObject
        {
            public function executeSelect(string $sql)
            {
                return $this->getObjectListFromDB($sql);
            }
        };

        foreach (self::$registedTables as $tableName) {
            // For now, only lock the game_ table
            if ($tableName != 'game_state') {
                continue;
            }
            $colString = implode(', ', self::$columnForTable[$tableName]);
            $db->executeSelect("SELECT $colString FROM $tableName WHERE 1 ORDER BY $colString FOR UPDATE");
        }
        \BX\DB\RowMgrRegister::clearAllMgrCache();
    }
}

Locker::registerTableColumn('global', ['global_id', 'global_value']);
Locker::registerTableColumn('player', 'player_id');
Locker::registerTableColumn('stats', 'stats_id');
