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

namespace EA;

require_once(__DIR__ . '/../../BX/php/Action.php');

const SCORE_TYPE_ID_CARD = 0;
const SCORE_TYPE_ID_EVENT = 1;
const SCORE_TYPE_ID_COMPOST = 2;
const SCORE_TYPE_ID_SPROUT = 3;
const SCORE_TYPE_ID_GROWTH = 4;
const SCORE_TYPE_ID_TERRAIN_BROWN = 5;
const SCORE_TYPE_ID_ECOSYSTEM = 6;
const SCORE_TYPE_ID_FAUNA = 7;

class PlayerScore extends \BX\Action\BaseActionRow
{
    /** @dbcol @dbkey @dbautoincrement */
    public $scoreId;
    /** @dbcol */
    public $playerId;
    /** @dbcol */
    public $cardId;
    /** @dbcol */
    public $scoreTypeId;
    /** @dbcol */
    public $score;
    /** @dbcol @dboptional */
    public $extraScore;

    public function __construct()
    {
        $this->scoreId = null;
        $this->playerId = null;
        $this->cardId = null;
        $this->scoreTypeId = null;
        $this->score = null;
        $this->extraScore = null;
    }
}

class PlayerScorepadUI
{
    public $playerId;
    public $scoresInOrder;
    public $scoreTerrainPerCardId;
    public $scoreExtraPerCardId;

    public $scoreCard;
    public $scoreEvent;
    public $scoreCompost;
    public $scoreSprout;
    public $scoreGrowth;
    public $scoreTerrain;
    public $scorePlayerEcosystem;
    public $scorePublicEcosystem1;
    public $scorePublicEcosystem2;
    public $scoreFauna;
    public $scoreTotal;

    public function __construct(int $playerId)
    {
        $this->playerId = $playerId;
        $this->scoresInOrder = [];
        $this->scoreTerrainPerCardId = [];
        $this->scoreExtraPerCardId = [];

        $this->scoreCard = 0;
        $this->scoreEvent = 0;
        $this->scoreCompost = 0;
        $this->scoreSprout = 0;
        $this->scoreGrowth = 0;
        $this->scoreTerrain = 0;
        $this->scorePlayerEcosystem = 0;
        $this->scorePublicEcosystem1 = 0;
        $this->scorePublicEcosystem2 = 0;
        $this->scoreFauna = 0;
        $this->scoreTotal = 0;
    }

    public function addTerrainScoreCardId(int $cardId, int $score)
    {
        $this->scoreTerrainPerCardId[$cardId] = $score;
    }

    public function addExtaScoreCardId(?int $cardId, ?string $extraScore)
    {
        if ($cardId === null || $extraScore === null) {
            return;
        }
        $this->scoreExtraPerCardId[$cardId] = $extraScore;
    }

    public function build()
    {
        $this->scoreTotal = $this->scoreCard
            + $this->scoreEvent
            + $this->scoreCompost
            + $this->scoreSprout
            + $this->scoreGrowth
            + $this->scoreTerrain
            + $this->scorePlayerEcosystem
            + $this->scorePublicEcosystem1
            + $this->scorePublicEcosystem2
            + $this->scoreFauna;
        if (isGameModeBeginner()) {
            $this->scorePlayerEcosystem = null;
            $this->scorePublicEcosystem1 = null;
            $this->scorePublicEcosystem2 = null;
        }
        if ($this->playerId == \EA\GAIA_PLAYER_ID) {
            $this->scoreEvent = null;
            $this->scoreTerrain = null;
            $this->scorePlayerEcosystem = null;
            $this->scorePublicEcosystem1 = null;
            $this->scorePublicEcosystem2 = null;
        }
        $this->scoresInOrder = [
            $this->scoreCard,
            $this->scoreEvent,
            $this->scoreCompost,
            $this->scoreSprout,
            $this->scoreGrowth,
            $this->scoreTerrain,
            $this->scorePlayerEcosystem,
            $this->scorePublicEcosystem1,
            $this->scorePublicEcosystem2,
            $this->scoreFauna,
            $this->scoreTotal,
        ];
    }
}

class PlayerScoreMgr extends \BX\Action\BaseActionRowMgr
{
    public function __construct()
    {
        parent::__construct('player_score', \EA\PlayerScore::class);
    }

    public function getAll()
    {
        return $this->getAllRowsByKey();
    }

    public function hasScore()
    {
        return (count($this->getAll()) > 0);
    }

    public function newPlayerScore()
    {
        return $this->db->newRow();
    }

    public function commitNewScore(PlayerScore $playerScore)
    {
        $this->db->insertRow($playerScore);
    }

    public function getScorepadUI()
    {
        $allScores = $this->getAll();
        if (count($allScores) == 0) {
            return [];
        }
        $ret = [];
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $ecosystemCardIds = array_map(fn($c) => $c->cardId, $cardMgr->getPublicEcosystemCards());
        $playerMgr = \BX\Action\ActionRowMgrRegister::getMgr('player');
        $playerIdArray = $playerMgr->getAllPlayerIds();
        if (isGameSolo()) {
            $playerIdArray[] = \EA\GAIA_PLAYER_ID;
        }
        foreach ($playerIdArray as $playerId) {
            $pad = new PlayerScorepadUI($playerId);
            foreach ($allScores as $score) {
                if ($score->playerId != $playerId) {
                    continue;
                }
                $pad->addExtaScoreCardId($score->cardId, $score->extraScore);
                switch ($score->scoreTypeId) {
                    case SCORE_TYPE_ID_CARD:
                        $pad->scoreCard += $score->score;
                        break;
                    case SCORE_TYPE_ID_EVENT:
                        $pad->scoreEvent += $score->score;
                        break;
                    case SCORE_TYPE_ID_COMPOST:
                        $pad->scoreCompost += $score->score;
                        break;
                    case SCORE_TYPE_ID_SPROUT:
                        $pad->scoreSprout += $score->score;
                        break;
                    case SCORE_TYPE_ID_GROWTH:
                        $pad->scoreGrowth += $score->score;
                        break;
                    case SCORE_TYPE_ID_TERRAIN_BROWN:
                        $pad->scoreTerrain += $score->score;
                        $pad->addTerrainScoreCardId($score->cardId, $score->score);
                        break;
                    case SCORE_TYPE_ID_ECOSYSTEM:
                        $index = array_search($score->cardId, $ecosystemCardIds);
                        if ($index === false) {
                            $pad->scorePlayerEcosystem += $score->score;
                        } else if ($index === 0) {
                            $pad->scorePublicEcosystem1 += $score->score;
                        } else {
                            $pad->scorePublicEcosystem2 += $score->score;
                        }
                        break;
                    case SCORE_TYPE_ID_FAUNA:
                        $pad->scoreFauna += $score->score;
                        break;
                    default:
                        throw new \BgaSystemException("BUG! Unknown scoreTypeId {$score->scoreTypeId}");
                }
            }
            $pad->build();
            $ret[] = $pad;
        }
        return $ret;
    }
}
