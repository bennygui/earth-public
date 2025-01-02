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

namespace BX\Player;

require_once('Action.php');
require_once('BGAGlobal.php');

class Player extends \BX\Action\BaseActionRow
{
    /** @dbcol @dbkey @ui(id) */
    public $playerId;
    /** @dbcol @dbdefault @ui(score) */
    public $playerScore;
    /** @dbcol @dbdefault @ui(score_aux) */
    public $playerScoreAux;
    /** @dbcol @ui(player_color) */
    public $playerColor;
    /** @dbcol @ui(player_name) */
    public $playerName;
    /** @dbcol @ui(player_canal) */
    public $playerCanal;
    /** @dbcol @ui(player_avatar) */
    public $playerAvatar;
    /** @dbcol @ui(player_no) */
    public $playerNo;
    /** @dbcol @ui(player_state) */
    public $playerState;
}

class PlayerMgr extends \BX\Action\BaseActionRowMgr
{
    public function __construct(string $playerClassId = '\BX\Player\Player')
    {
        parent::__construct('player', $playerClassId);
        $this->setUseCache(false);
    }

    public function setup(array $setupNewGamePlayers, array $colors)
    {
        $availableColors = $colors;
        foreach ($setupNewGamePlayers as $playerId => $setupPlayer) {
            $p = $this->db->newRow();
            $p->playerId = $playerId;
            $p->playerColor = array_shift($colors);
            $p->playerCanal = $setupPlayer['player_canal'];
            $p->playerName = $setupPlayer['player_name'];
            $p->playerAvatar = $setupPlayer['player_avatar'];
            $this->db->insertRow($p);
        }
        return $availableColors;
    }

    public function getAll()
    {
        return $this->getAllRowsByKey();
    }

    public function getAllForUI(bool $gameHasEnded)
    {
        return \BX\UI\deepCopyToArray($this->getAllRowsByKey());
    }

    public function getPlayerCount()
    {
        return count($this->getAll());
    }

    public function getByPlayerId(int $playerId)
    {
        return $this->getRowByKey($playerId);
    }

    public function getPlayerIdPlayerState(int $playerId)
    {
        $player = $this->getRowByKey($playerId);
        if ($player === null) {
            return null;
        }
        return $player->playerState;
    }

    public function getAllPlayerIds()
    {
        $players = $this->getAllRowsByKey();
        $playerIdArray = array_keys($players);
        usort($playerIdArray, function ($p1, $p2) use (&$players) {
            return ($players[$p1]->playerNo <=> $players[$p2]->playerNo);
        });
        return $playerIdArray;
    }

    public function getAllPlayerIdsByScore()
    {
        $players = $this->getAllRowsByKey();
        $playerIdArray = array_keys($players);
        usort($playerIdArray, function ($p1, $p2) use (&$players) {
            $cmp = ($players[$p2]->playerScore <=> $players[$p1]->playerScore);
            if ($cmp != 0) {
                return $cmp;
            }
            $cmp = ($players[$p2]->playerScoreAux <=> $players[$p1]->playerScoreAux);
            if ($cmp != 0) {
                return $cmp;
            }
            return ($players[$p1]->playerNo <=> $players[$p2]->playerNo);
        });
        return $playerIdArray;
    }

    public function getFirstPlayerId()
    {
        $playerIdArray = $this->getAllPlayerIds();
        if (count($playerIdArray) == 0) {
            return null;
        }
        return $playerIdArray[0];
    }

    public function updatePlayerScoreNow(int $playerId, int $score)
    {
        $p = $this->getByPlayerId($playerId);
        $p->playerScore = $score;
        $this->db->updateRow($p);
    }

    public function updatePlayerScoreAuxNow(int $playerId, int $aux)
    {
        $p = $this->getByPlayerId($playerId);
        $p->playerScoreAux = $aux;
        $this->db->updateRow($p);
    }

    public function debugResetScores()
    {
        foreach ($this->getAll() as $player) {
            $player->playerScore = 0;
            $player->playerScoreAux = 0;
            $this->db->updateRow($player);
        }
    }
}

const NTF_UPDATE_PLAYER_SCORE = 'NTF_UPDATE_PLAYER_SCORE';

class UpdatePlayerScoreActionCommand extends \BX\Action\BaseActionCommand
{
    private $scoreToAdd;
    private $undoPlayerScore;

    public function __construct(int $playerId, ?int $scoreToAdd = null)
    {
        parent::__construct($playerId);
        $this->scoreToAdd = $scoreToAdd;
        $this->undoPlayerScore = null;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier, ?int $scoreToAdd = null, ?string $notifLog = null, $additionalNotifParams = [])
    {
        if ($scoreToAdd === null) {
            $scoreToAdd = $this->scoreToAdd;
        }
        if ($scoreToAdd === null) {
            return;
        }
        $playerMgr = self::getMgr('player');
        $player = $playerMgr->getRowByKey($this->playerId);
        $this->undoPlayerScore = $player->playerScore;
        $player->modifyAction();
        $player->playerScore += $scoreToAdd;
        if ($notifLog === null) {
            if ($scoreToAdd >= 0) {
                $notifLog = clienttranslate('${player_name} scores ${scorePositive} point(s)');
            } else {
                $notifLog = clienttranslate('${player_name} loses ${scorePositive} point(s)');
            }
        }
        $notifier->notify(
            NTF_UPDATE_PLAYER_SCORE,
            $notifLog,
            array_merge($additionalNotifParams, [
                'score' => $scoreToAdd,
                'scorePositive' => abs($scoreToAdd),
                'playerScore' => $player->playerScore,
            ])
        );
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->undoPlayerScore != null) {
            $notifier->notifyNoMessage(NTF_UPDATE_PLAYER_SCORE, [
                'playerScore' => $this->undoPlayerScore
            ]);
        }
    }
}

class PlayerSetEndGameScoreActionCommand extends \BX\Action\BaseActionCommand
{
    private $score;
    private $scoreAux;
    private $undoScore;

    public function __construct(int $playerId, int $score, int $scoreAux = 0)
    {
        parent::__construct($playerId);
        $this->score = $score;
        $this->scoreAux = $scoreAux;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $playerMgr = self::getMgr('player');
        $player = $playerMgr->getRowByKey($this->playerId);
        $this->undoScore = $player->playerScore;

        $player->modifyAction();
        $player->playerScore = $this->score;
        $player->playerScoreAux = $this->scoreAux;
        $notifier->notifyNoMessage(
            NTF_UPDATE_PLAYER_SCORE,
            [
                'playerScore' => $player->playerScore,
            ]
        );
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->undoScore != null) {
            $notifier->notifyNoMessage(
                NTF_UPDATE_PLAYER_SCORE,
                [
                    'playerScore' => $this->undoScore
                ]
            );
        }
    }
}
