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

namespace BX\Debug;

const NTF_DEBUG_RELOAD = 'NTF_DEBUG_RELOAD';

trait GameStatesTrait
{
    public function loadBugReportSQL(int $reportId, array $studioPlayers)
    {
        $this->debugLoadBug($studioPlayers);
    }

    private function debugLoadBugInternal(array $studioPlayers, callable $getSqlFct)
    {
        $playerIdArray = array_values(self::getObjectListFromDb("SELECT player_id FROM player", true));

        $studioCount = count($studioPlayers);
        if ($studioCount == 0) {
            $studioPlayerId = self::getCurrentPlayerId();
            foreach ($playerIdArray as $pId) {
                $studioPlayers[] = $studioPlayerId;
                // This could be improved, it assumes you had sequential studio accounts before loading
                // e.g., quietmint0, quietmint1, quietmint2, etc.
                $studioPlayerId++;
            }
            $studioCount = count($studioPlayers);
        }

        $prodCount = count($playerIdArray);
        if ($prodCount != $studioCount) {
            throw new \BgaVisibleSystemException("Incorrect player count (bug report has $prodCount players, studio table has $studioCount players)");
        }

        $sql = [];
        foreach ($playerIdArray as $i => $pId) {
            $studioPlayerId = $studioPlayers[$i];
            // All games can keep this SQL
            $sql[] = "UPDATE player SET player_id=$studioPlayerId WHERE player_id=$pId";
            $sql[] = "UPDATE global SET global_value=$studioPlayerId WHERE global_value=$pId";
            $sql[] = "UPDATE stats SET stats_player_id=$studioPlayerId WHERE stats_player_id=$pId";

            // Add game-specific SQL update the tables for your game
            $sql = array_merge($sql, $getSqlFct($studioPlayerId, $pId));
        }
        $this->notifyAllPlayers('message', 'DONE', []);

        foreach ($sql as $q) {
            self::DbQuery($q);
        }
        self::reloadPlayersBasicInfos();
    }

    private function debugGetSqlForActionCommand($studioPlayerId, $replacePlayerId)
    {
        return [
            "UPDATE action_command SET action_json = REPLACE(action_json, '\"playerId\":$replacePlayerId', '\"playerId\":$studioPlayerId') WHERE action_json like '%\"playerId\":$replacePlayerId%'"
        ];
    }

    private function debugGetSqlForPrivateState($studioPlayerId, $replacePlayerId)
    {
        return [
            "UPDATE private_state SET player_id = $studioPlayerId WHERE player_id = $replacePlayerId"
        ];
    }

    private function debugGetSqlForStateFunction($studioPlayerId, $replacePlayerId)
    {
        return [
            "UPDATE bx_state_function SET player_id = $studioPlayerId WHERE player_id = $replacePlayerId"
        ];
    }

    public function debugSendReload()
    {
        $this->notifyAllPlayers(NTF_DEBUG_RELOAD, 'DEBUG: RELOAD', []);
    }
}
