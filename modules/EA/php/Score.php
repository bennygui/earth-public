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

namespace EA\Score;

require_once(__DIR__ . '/../../BX/php/Action.php');
require_once('PlayerScore.php');

function commitFinalScores()
{
    $scores = getScores();
    foreach ($scores as $score) {
        $score->commitFinalScore();
    }
}

function getScores()
{
    $scores = [];
    $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
    $playerMgr = \BX\Action\ActionRowMgrRegister::getMgr('player');
    foreach ($playerMgr->getAllPlayerIds() as $playerId) {
        $scores[] = new ScoreCompost($playerId);
        foreach ($cardMgr->getPlayerIslandClimateTableauCards($playerId) as $card) {
            $scores[] = new ScoreCard($playerId, $card->cardId);
            $scores[] = new ScoreSprout($playerId, $card->cardId);
            $scores[] = new ScoreGrowth($playerId, $card->cardId);
            $scores[] = new ScoreTerrainBrown($playerId, $card->cardId);
        }
        foreach ($cardMgr->getPlayerBoardEventCards($playerId) as $card) {
            $scores[] = new ScoreEvent($playerId, $card->cardId);
        }
        foreach ($cardMgr->getFaunaCards() as $card) {
            $scores[] = new ScoreFauna($playerId, $card->cardId);
        }
        $scores[] = new ScoreFaunaBonus($playerId);
        foreach ($cardMgr->getPublicEcosystemCards() as $card) {
            $scores[] = new ScoreEcosystem($playerId, $card->cardId);
        }
        $playerEcosystemCard = $cardMgr->getPlayerEcosystemCard($playerId);
        if ($playerEcosystemCard !== null) {
            $scores[] = new ScoreEcosystem($playerId, $playerEcosystemCard->cardId);
        }
    }
    if (isGameSolo()) {
        foreach ($cardMgr->getGaiaTableauCards() as $card) {
            $scores[] = new ScoreCardGaia($card->cardId);
        }
        $scores[] = new ScoreSproutGaia();
        $scores[] = new ScoreGrowthGaia();
        $scores[] = new ScoreCompostGaia();
        foreach ($cardMgr->getFaunaCards() as $card) {
            $scores[] = new ScoreFauna(\EA\GAIA_PLAYER_ID, $card->cardId);
        }
        $scores[] = new ScoreFaunaBonus(\EA\GAIA_PLAYER_ID);
    }
    return $scores;
}

abstract class ScoreBase
{
    protected $playerId;
    protected $cardId;
    protected $scoreTypeId;
    protected $extraScore;

    public function __construct(int $playerId, ?int $cardId, int $scoreTypeId)
    {
        $this->playerId = $playerId;
        $this->cardId = $cardId;
        $this->scoreTypeId = $scoreTypeId;
        $this->extraScore = null;
    }

    protected static function getMgr(string $key)
    {
        return \BX\Action\ActionRowMgrRegister::getMgr($key);
    }

    protected static function cardMgr()
    {
        return self::getMgr('card');
    }

    protected static function gameStateMgr()
    {
        return self::getMgr('game_state');
    }

    public function commitFinalScore()
    {
        $score = $this->onScore();
        if ($score === null) {
            return;
        }

        $playerScoreMgr = self::getMgr('player_score');
        $playerScore = $playerScoreMgr->newPlayerScore();
        $playerScore->playerId = $this->playerId;
        $playerScore->cardId = $this->cardId;
        $playerScore->scoreTypeId = $this->scoreTypeId;
        $playerScore->score = $score;
        $playerScore->extraScore = $this->extraScore;
        $playerScoreMgr->commitNewScore($playerScore);
    }

    protected function setExtraScore(string $extra)
    {
        $this->extraScore = $extra;
    }

    abstract protected function onScore();
}

class ScoreCompost extends ScoreBase
{
    public function __construct(int $playerId)
    {
        parent::__construct($playerId, null, \EA\SCORE_TYPE_ID_COMPOST);
    }

    protected function onScore()
    {
        $cardMgr = self::cardMgr();
        return count($cardMgr->getPlayerCompostCards($this->playerId));
    }
}

class ScoreCompostGaia extends ScoreBase
{
    public function __construct()
    {
        parent::__construct(\EA\GAIA_PLAYER_ID, null, \EA\SCORE_TYPE_ID_COMPOST);
    }

    protected function onScore()
    {
        $cardMgr = self::cardMgr();
        return $cardMgr->getGaiaCompostCardCount();
    }
}

abstract class ScoreCardBase extends ScoreBase
{
    protected function onScore()
    {
        $cardMgr = self::cardMgr();
        $card = $cardMgr->getCardById($this->cardId);
        return $this->onScoreCard($card);
    }

    abstract protected function onScoreCard(\EA\Card $card);
}

class ScoreCard extends ScoreCardBase
{
    public function __construct(int $playerId, int $cardId)
    {
        parent::__construct($playerId, $cardId, \EA\SCORE_TYPE_ID_CARD);
    }

    protected function onScoreCard(\EA\Card $card)
    {
        return $card->getCardDef()->score;
    }
}

class ScoreCardGaia extends ScoreCardBase
{
    public function __construct(int $cardId)
    {
        parent::__construct(\EA\GAIA_PLAYER_ID, $cardId, \EA\SCORE_TYPE_ID_CARD);
    }

    protected function onScoreCard(\EA\Card $card)
    {
        return abs($card->getCardDef()->score);
    }
}

class ScoreEvent extends ScoreCardBase
{
    public function __construct(int $playerId, int $cardId)
    {
        parent::__construct($playerId, $cardId, \EA\SCORE_TYPE_ID_EVENT);
    }

    protected function onScoreCard(\EA\Card $card)
    {
        return $card->getCardDef()->score;
    }
}

class ScoreSprout extends ScoreCardBase
{
    public function __construct(int $playerId, int $cardId)
    {
        parent::__construct($playerId, $cardId, \EA\SCORE_TYPE_ID_SPROUT);
    }

    protected function onScoreCard(\EA\Card $card)
    {
        $max = $card->getCardDef()->sproutMax;
        if ($max === null || $max == 0) {
            return null;
        }
        return $card->sproutCount;
    }
}

class ScoreSproutGaia extends ScoreBase
{
    public function __construct()
    {
        parent::__construct(\EA\GAIA_PLAYER_ID, null, \EA\SCORE_TYPE_ID_SPROUT);
    }

    protected function onScore()
    {
        $gameStateMgr = self::gameStateMgr();
        return $gameStateMgr->getGaiaSprout();
    }
}

class ScoreGrowth extends ScoreCardBase
{
    public function __construct(int $playerId, int $cardId)
    {
        parent::__construct($playerId, $cardId, \EA\SCORE_TYPE_ID_GROWTH);
    }

    protected function onScoreCard(\EA\Card $card)
    {
        $max = $card->getCardDef()->growthMax;
        if ($max === null || $max == 0) {
            return null;
        }
        if ($card->growthCount == $max) {
            return $card->getCardDef()->growthScore;
        } else {
            return $card->growthCount;
        }
    }
}

class ScoreGrowthGaia extends ScoreBase
{
    const GROWTH_HEIGHTS = [
        [5, 7],
        [4, 6],
        [3, 5],
        [2, 4],
        [1, 3],
    ];

    public function __construct()
    {
        parent::__construct(\EA\GAIA_PLAYER_ID, null, \EA\SCORE_TYPE_ID_GROWTH);
    }

    protected function onScore()
    {
        $gameStateMgr = self::gameStateMgr();
        $count = $gameStateMgr->getGaiaGrowth();
        $score = 0;
        $idx = 0;
        while ($count > 0) {
            if ($count < self::GROWTH_HEIGHTS[$idx][0]) {
                $score += $count;
                break;
            }
            $count -= self::GROWTH_HEIGHTS[$idx][0];
            $score += self::GROWTH_HEIGHTS[$idx][1];
            $idx += 1;
            if ($idx >= count(self::GROWTH_HEIGHTS)) {
                $idx = 0;
            }
        }
        return $score;
    }

    public static function getNbCanopies(int $count)
    {
        $nbCanopies = 0;
        $idx = 0;
        while ($count > 0) {
            if ($count < self::GROWTH_HEIGHTS[$idx][0]) {
                break;
            }
            $count -= self::GROWTH_HEIGHTS[$idx][0];
            $nbCanopies += 1;
            $idx += 1;
            if ($idx >= count(self::GROWTH_HEIGHTS)) {
                $idx = 0;
            }
        }
        return $nbCanopies;
    }
}

class ScoreFauna extends ScoreCardBase
{
    private const FAUNA_BEGINNER_SCORE = 10;
    private const FAUNA_SCORE = [15, 11, 8, 6, 5];

    public function __construct(int $playerId, int $cardId)
    {
        parent::__construct($playerId, $cardId, \EA\SCORE_TYPE_ID_FAUNA);
    }

    protected function onScoreCard(\EA\Card $card)
    {
        $leafTokenMgr = self::getMgr('leaf_token');
        $leafId = $leafTokenMgr->getLeafIdFromBoardLocation($card->locationX, $card->locationY);
        $token = $leafTokenMgr->getLeafTokenByLeafIdAndPlayerId($leafId, $this->playerId);
        if (!$token->isOnFaunaBoard()) {
            return null;
        }
        if (isGameModeBeginner()) {
            return self::FAUNA_BEGINNER_SCORE;
        } else {
            return self::FAUNA_SCORE[$token->locationOrder];
        }
    }
}

class ScoreFaunaBonus extends ScoreBase
{
    private const FAUNA_BONUS_SCORE = 7;

    public function __construct(int $playerId)
    {
        parent::__construct($playerId, null, \EA\SCORE_TYPE_ID_FAUNA);
    }

    protected function onScore()
    {
        $leafTokenMgr = self::getMgr('leaf_token');
        foreach ($leafTokenMgr->getAll() as $token) {
            if ($token->playerId == $this->playerId && $token->isOnFaunaBoardTableauBonus()) {
                return self::FAUNA_BONUS_SCORE;
            }
        }
        return null;
    }
}

class ScoreTerrainBrown extends ScoreCardBase
{
    private const SCORE_FUNCTIONS = [
        \EA\AB_SCORE_DIRECTION_CONDITION => 'scoreDirectionCondition',
        \EA\AB_SCORE_ANY_ONE_PLAYER_CONDITION => 'scoreAnyOnePlayerCondition',
        \EA\AB_SCORE_WITH_MORE_PIECES => 'scoreWithMorePieces',
        \EA\AB_SCORE_WITH_LESS_COST => 'scoreWithLessCost',
        \EA\AB_SCORE_WITH_MORE_COST => 'scoreWithMoreCost',
        \EA\AB_SCORE_WITH_LESS_SCORE => 'scoreWithLessScore',
        \EA\AB_SCORE_WITH_MORE_SCORE => 'scoreWithMoreScore',
        \EA\AB_SCORE_REMAINING_SOIL => 'scoreRemainingSoil',
        \EA\AB_SCORE_REMAINING_CARD_IN_HAND => 'scoreRemainingCardInHand',
        \EA\AB_SCORE_CARD_IN_COMPOST => 'scoreCardInCompost',
        \EA\AB_SCORE_PER_TERRAIN  => 'scorePerTerrain',
    ];

    public function __construct(int $playerId, int $cardId)
    {
        parent::__construct($playerId, $cardId, \EA\SCORE_TYPE_ID_TERRAIN_BROWN);
    }

    protected function onScoreCard(\EA\Card $card)
    {
        $abilityBrown = $card->getCardDef()->abilityBrown();
        if ($abilityBrown === null) {
            return null;
        }
        $abilityScores = $abilityBrown->getScores();
        if (count($abilityScores) == 0) {
            return null;
        }
        $scoreType = array_shift($abilityScores);
        if (!array_key_exists($scoreType, self::SCORE_FUNCTIONS)) {
            throw new \BgaSystemException("BUG! Card {$this->cardId} with brown ability $scoreType does not have a function");
        }
        $scoreFunction = self::SCORE_FUNCTIONS[$scoreType];
        return $this->$scoreFunction($card, $abilityScores, $abilityBrown->getDirection());
    }

    private function scoreDirectionCondition(\EA\Card $card, array $scores, ?int $direction)
    {
        $scorePerCard = array_shift($scores);
        $scoreCondition = array_shift($scores);
        $scoreConditionType = array_shift($scores);
        $cards = null;
        $filterFunction = null;
        switch ($scoreCondition) {
            case \EA\AB_COND_PER_TYPE:
                $filterFunction = fn ($c) => $c->getCardDef()->hasCardType($scoreConditionType);
                break;
            case \EA\AB_COND_PER_HABITAT:
                $filterFunction = fn ($c) => $c->getCardDef()->hasHabitat($scoreConditionType);
                break;
            case \EA\AB_COND_PER_EMPTY:
                // Nothing to filter 
                $filterFunction = fn ($c) => true;
                break;
            default:
                throw new \BgaSystemException("BUG! Unknown brown ability score condition: $scoreCondition");
        }
        switch ($direction) {
            case \EA\AB_DIRECTION_COLUMN:
                $cards = self::cardMgr()->getPlayerTableauCardsInCardColumn($this->playerId, $card->cardId);
                break;
            case \EA\AB_DIRECTION_ROW:
                $cards = self::cardMgr()->getPlayerTableauCardsInCardRow($this->playerId, $card->cardId);
                break;
            case \EA\AB_DIRECTION_ALL_ADJACENT:
                $cards = self::cardMgr()->getPlayerTableauCardsInCardAdjacent($this->playerId, $card->cardId);
                break;
            case \EA\AB_DIRECTION_ORTHO_ADJACENT:
                $cards = self::cardMgr()->getPlayerTableauCardsInCardOrthoAdjacent($this->playerId, $card->cardId);
                break;
            case \EA\AB_DIRECTION_DIAG_ADJACENT:
                $cards = self::cardMgr()->getPlayerTableauCardsInCardDiagAdjacent($this->playerId, $card->cardId);
                break;
            case \EA\AB_DIRECTION_ORTHO_LINE:
                $cards = self::cardMgr()->getPlayerTableauCardsInCardOrthoLine(
                    $this->playerId,
                    $card->cardId,
                    $filterFunction
                );
                break;
            default:
                throw new \BgaSystemException("BUG! Unknown brown ability direction: $direction");
        }
        $cards = array_filter($cards, $filterFunction);
        if ($scoreCondition == \EA\AB_COND_PER_EMPTY) {
            // The only case is AB_DIRECTION_ORTHO_ADJACENT so a maximum of 4 empty places
            return max(0, 4 - count($cards)) * $scorePerCard;
        } else {
            if ($direction == \EA\AB_DIRECTION_ORTHO_LINE) {
                $this->setExtraScore(implode('-', array_map(fn ($c) => $c->cardId, $cards)));
            }
            return (count($cards) * $scorePerCard);
        }
    }

    private function scoreAnyOnePlayerCondition(\EA\Card $card, array $scores, ?int $direction)
    {
        $scorePerCard = array_shift($scores);
        $scoreCondition = array_shift($scores);
        $scoreConditionType = array_shift($scores);
        $playerMgr = self::getMgr('player');
        $max = 0;
        foreach ($playerMgr->getAllPlayerIds() as $playerId) {
            $playerMax = 0;
            foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($playerId) as $otherCard) {
                switch ($scoreCondition) {
                    case \EA\AB_COND_PER_HABITAT:
                        if ($otherCard->getCardDef()->hasHabitat($scoreConditionType)) {
                            $playerMax += 1;
                        }
                        break;
                    case \EA\AB_COND_PER_COLOR:
                        if ($otherCard->getCardDef()->hasAbilityMatchingColor($scoreConditionType)) {
                            $playerMax += 1;
                        }
                        break;
                    default:
                        throw new \BgaSystemException("BUG! Unknown brown ability scoreCondition: $scoreCondition");
                }
            }
            if ($playerMax > $max) {
                $max = $playerMax;
            }
        }
        return ($max * $scorePerCard);
    }

    private function scoreWithMorePieces(\EA\Card $card, array $scores, ?int $direction)
    {
        $scorePerCard = array_shift($scores);
        $scorePieceType = array_shift($scores);
        $scorePieceCount = array_shift($scores);
        $cardCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            $pieceCount = null;
            switch ($scorePieceType) {
                case \EA\ABILITY_SPROUT:
                    $pieceCount = $otherCard->sproutCount;
                    break;
                case \EA\ABILITY_GROWTH:
                    $pieceCount = $otherCard->growthCount;
                    break;
                default:
                    throw new \BgaSystemException("BUG! Unknown brown ability scorePieceType: $scorePieceType");
            }
            if ($pieceCount >= $scorePieceCount) {
                $cardCount += 1;
            }
        }
        return ($cardCount * $scorePerCard);
    }

    private function scoreWithLessCost(\EA\Card $card, array $scores, ?int $direction)
    {
        $scorePerCard = array_shift($scores);
        $scoreCost = array_shift($scores);
        $cardCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            $cardSoil = $otherCard->getCardDef()->soil;
            if ($cardSoil !== null && $cardSoil <= $scoreCost) {
                $cardCount += 1;
            }
        }
        return ($cardCount * $scorePerCard);
    }

    private function scoreWithMoreCost(\EA\Card $card, array $scores, ?int $direction)
    {
        $scorePerCard = array_shift($scores);
        $scoreCost = array_shift($scores);
        $cardCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            $cardSoil = $otherCard->getCardDef()->soil;
            if ($cardSoil !== null && $cardSoil >= $scoreCost) {
                $cardCount += 1;
            }
        }
        return ($cardCount * $scorePerCard);
    }

    private function scoreWithLessScore(\EA\Card $card, array $scores, ?int $direction)
    {
        $scorePerCard = array_shift($scores);
        $scoreScore = array_shift($scores);
        $cardCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            if ($otherCard->getCardDef()->score <= $scoreScore) {
                $cardCount += 1;
            }
        }
        return ($cardCount * $scorePerCard);
    }

    private function scoreWithMoreScore(\EA\Card $card, array $scores, ?int $direction)
    {
        $scorePerCard = array_shift($scores);
        $scoreScore = array_shift($scores);
        $cardCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            if ($otherCard->getCardDef()->score >= $scoreScore) {
                $cardCount += 1;
            }
        }
        return ($cardCount * $scorePerCard);
    }

    private function scoreRemainingSoil(\EA\Card $card, array $scores, ?int $direction)
    {
        $scorePerSoil = array_shift($scores);
        $scoreMax = array_shift($scores);
        return min($scoreMax, $scorePerSoil * self::getMgr('player_state')->getPlayerSoilCount($this->playerId));
    }

    private function scoreRemainingCardInHand(\EA\Card $card, array $scores, ?int $direction)
    {
        $scorePerCard = array_shift($scores);
        $scoreMax = array_shift($scores);
        return min($scoreMax, $scorePerCard * count(self::cardMgr()->getPlayerHandCards($this->playerId)));
    }

    private function scoreCardInCompost(\EA\Card $card, array $scores, ?int $direction)
    {
        $scorePerCard = array_shift($scores);
        $scoreMax = array_shift($scores);
        return min($scoreMax, $scorePerCard * count(self::cardMgr()->getPlayerCompostCards($this->playerId)));
    }

    private function scorePerTerrain(\EA\Card $card, array $scores, ?int $direction)
    {
        $scorePerCard = array_shift($scores);
        $cardCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            if ($otherCard->getCardDef()->isTerrain()) {
                $cardCount += 1;
            }
        }
        return ($cardCount * $scorePerCard);
    }
}

class ScoreEcosystem extends ScoreCardBase
{
    private const SCORE_FUNCTIONS = [
        \EA\AB_ECO_PER_TOTAL_CARD_TYPE => 'scorePerTotalCardType',
        \EA\AB_ECO_DIAG_LINE_CARD_TYPE => 'scoreDiagLineCardType',
        \EA\AB_ECO_PER_HABITAT => 'scorePerHabitat',
        \EA\AB_ECO_PER_ABILITY_COLOR => 'scorePerAbilityColor',
        \EA\AB_ECO_REMAINING_SOIL => 'scoreRemainingSoil',
        \EA\AB_ECO_REMAINING_CARD_IN_HAND => 'scoreRemainingCardInHand',
        \EA\AB_ECO_CARD_IN_COMPOST => 'scoreCardInCompost',
        \EA\AB_ECO_CARDS_WITH_LESS_COST => 'scoreCardsWithLessCost',
        \EA\AB_ECO_CARDS_WITH_MORE_COST => 'scoreCardsWithMoreCost',
        \EA\AB_ECO_CARDS_WITH_MORE_SPROUTS => 'scoreCardsWithMoreSprouts',
        \EA\AB_ECO_CARDS_WITH_MORE_GROWTH  => 'scoreCardsWithMoreGrowth',
        \EA\AB_ECO_PER_EVENT  => 'scorePerEvent',
        \EA\AB_ECO_DIRECTION_WITH_DIFFERENT_ABILITY_COLOR  => 'scoreDirectionWithDifferentAbilityColor',
        \EA\AB_ECO_DIRECTION_WITH_SAME_ABILITY_COLOR  => 'scoreDirectionWithSameAbilityColor',
        \EA\AB_ECO_CARDS_WITH_LESS_SCORE  => 'scoreCardsWithLessScore',
        \EA\AB_ECO_CARDS_WITH_MORE_SCORE  => 'scoreCardsWithMoreScore',
        \EA\AB_ECO_CARDS_WITH_DIRECTIONS  => 'scoreCardsWithDirections',
        \EA\AB_ECO_DIRECTION_WITH_DIFFERENT_SCORE  => 'scoreDirectionWithDifferentScore',
        \EA\AB_ECO_CARDS_WITH_LESS_HABITAT  => 'scoreCardsWithLessHabitat',
        \EA\AB_ECO_CARDS_WITH_MORE_HABITAT  => 'scoreCardsWithMoreHabitat',
        \EA\AB_ECO_DIRECTION_WITH_DIFFERENT_SPROUT_COUNT  => 'scoreDirectionWithDifferentSproutCount',
        \EA\AB_ECO_DIRECTION_WITH_DIFFERENT_GROWTH_COUNT  => 'scoreDirectionWithDifferentGrowthCount',
        \EA\AB_ECO_PER_SPROUT  => 'scorePerSprout',
        \EA\AB_ECO_PER_GROWTH  => 'scorePerGrowth',
        \EA\AB_ECO_PER_CARDS_SETS  => 'scorePerCardsSets',
        \EA\AB_ECO_PER_CARDS_WITH_TWO_ABILITIES  => 'scorePerCardsWithTwoAbilities',
        \EA\AB_ECO_DIRECTION_WITH_DIFFERENT_CARD_TYPE  => 'scoreDirectionWithDifferentCardType',
        \EA\AB_ECO_DIRECTION_WITH_SAME_CARD_TYPE  => 'scoreDirectionWithSameCardType',
        \EA\AB_ECO_DIRECTION_WITH_DIFFERENT_HABITAT  => 'scoreDirectionWithDifferentHabitat',
        \EA\AB_ECO_DIRECTION_WITH_SAME_HABITAT  => 'scoreDirectionWithSameHabitat',
        \EA\AB_ECO_PER_CARD_WITH_BOLD_GEOGRAPHY  => 'scorePerCardWithBoldGeography',
        \EA\AB_ECO_PER_CARD_WITH_ITALIC_COLOR  => 'scorePerCardWithItalicColor',
        \EA\AB_ECO_PER_CARD_WITH_UNDERLINE_ANIMAL  => 'scorePerCardWithUnderlineAnimal',
        \EA\AB_ECO_PER_CARD_WITH_EXACT_PIECE_SPOT  => 'scorePerCardWithExactPieceSpot',
        \EA\AB_ECO_PER_CARD_WITH_LESS_PIECE_SPOT  => 'scorePerCardWithLessPieceSpot',
        \EA\AB_ECO_PER_CARD_WITH_MORE_PIECE_SPOT  => 'scorePerCardWithMorePieceSpot',
        \EA\AB_ECO_PER_CARD_WITH_EVEN_SCORE  => 'scorePerCardWithEvenScore',
        \EA\AB_ECO_PER_CARD_WITH_ODD_SCORE  => 'scorePerCardWithOddScore',
        \EA\AB_ECO_PER_CARD_WITH_LESS_GROWTH_MAX_SCORE  => 'scorePerCardWithLessGrowthMaxScore',
        \EA\AB_ECO_PER_CARD_WITH_MORE_GROWTH_MAX_SCORE  => 'scorePerCardWithMoreGrowthMaxScore',
        \EA\AB_ECO_PER_CARD_WITH_FILLED_FIECES  => 'scorePerCardWithFilledFieces',
    ];

    public function __construct(int $playerId, int $cardId)
    {
        parent::__construct($playerId, $cardId, \EA\SCORE_TYPE_ID_ECOSYSTEM);
    }

    protected function onScoreCard(\EA\Card $card)
    {
        $abilityEcosystem = $card->getCardDef()->getFirstAbility();
        if ($abilityEcosystem === null) {
            throw new \BgaSystemException("BUG! Ecosystem cardId {$this->cardId} has no ability");
        }
        $abilityScores = $abilityEcosystem->getScores();
        if (count($abilityScores) == 0) {
            throw new \BgaSystemException("BUG! Ecosystem cardId {$this->cardId} has no scores");
        }
        $scoreType = array_shift($abilityScores);
        if (!array_key_exists($scoreType, self::SCORE_FUNCTIONS)) {
            throw new \BgaSystemException("BUG! Ecosystem cardId {$this->cardId} with ability $scoreType does not have a function");
        }
        $scoreFunction = self::SCORE_FUNCTIONS[$scoreType];
        return $this->$scoreFunction($card, $abilityScores);
    }

    private function scorePerTotalCardType(\EA\Card $card, array $scores)
    {
        $scoreCardType = array_shift($scores);
        $scores = array_values($scores);
        $cardCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            if ($otherCard->getCardDef()->hasCardType($scoreCardType)) {
                $cardCount += 1;
            }
        }
        if ($cardCount <= 0) {
            return 0;
        }
        $cardCount = min($cardCount, count($scores));
        return $scores[$cardCount - 1];
    }

    private function scoreDiagLineCardType(\EA\Card $card, array $scores)
    {
        $scoreCardType = array_shift($scores);
        $scoreScorePerCard = array_shift($scores);
        $scoreMaxCardCount = array_shift($scores);
        $cardList = [];
        $diagAdjacentForCard = [];
        foreach ($this->cardMgr()->getPlayerTableauCards($this->playerId, $this->playerId) as $otherCard) {
            if (!$otherCard->getCardDef()->hasCardType($scoreCardType)) {
                continue;
            }
            $diagAdjacentForCard[$otherCard->cardId] = array_filter(
                $this->cardMgr()->getPlayerTableauCardsInCardDiagAdjacent($this->playerId, $otherCard->cardId),
                fn ($c) => $c->getCardDef()->hasCardType($scoreCardType)
            );
        }
        foreach ($this->cardMgr()->getPlayerTableauCards($this->playerId, $this->playerId) as $otherCard) {
            if (!$otherCard->getCardDef()->hasCardType($scoreCardType)) {
                continue;
            }
            $newCardList = $this->cardMgr()->getPlayerTableauCardsInCardDiagLine(
                $this->playerId,
                $otherCard->cardId,
                fn ($c) => $c->getCardDef()->hasCardType($scoreCardType),
                $diagAdjacentForCard
            );
            if (count($newCardList) > count($cardList)) {
                $cardList = $newCardList;
            }
        }
        $this->setExtraScore(implode('-', array_map(fn ($c) => $c->cardId, $cardList)));
        return ($scoreScorePerCard * min(count($cardList), $scoreMaxCardCount));
    }

    private function scorePerHabitat(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreCountDivider = array_shift($scores);
        $scoreHabitat = array_shift($scores);
        $cardCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            if ($otherCard->getCardDef()->hasHabitat($scoreHabitat)) {
                $cardCount += 1;
            }
        }
        return $scorePerCount * intdiv($cardCount, $scoreCountDivider);
    }

    private function scorePerAbilityColor(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreCountDivider = array_shift($scores);
        $scoreColor = array_shift($scores);
        $cardCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            if ($scoreColor == \EA\AB_COLOR_MULTICOLOR) {
                if ($otherCard->getCardDef()->getAbilityForColor($scoreColor) !== null) {
                    $cardCount += 1;
                }
            } else {
                if ($otherCard->getCardDef()->hasAbilityMatchingColor($scoreColor)) {
                    $cardCount += 1;
                }
            }
        }
        return $scorePerCount * intdiv($cardCount, $scoreCountDivider);
    }

    private function scoreRemainingSoil(\EA\Card $card, array $scores)
    {
        $scorePerSoil = array_shift($scores);
        $scoreMax = array_shift($scores);
        return min($scoreMax, $scorePerSoil * self::getMgr('player_state')->getPlayerSoilCount($this->playerId));
    }

    private function scoreRemainingCardInHand(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreCountDivider = array_shift($scores);
        $scoreMax = array_shift($scores);
        return min($scoreMax, $scorePerCount * intdiv(count(self::cardMgr()->getPlayerHandCards($this->playerId)), $scoreCountDivider));
    }

    private function scoreCardInCompost(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreCountDivider = array_shift($scores);
        $scoreMax = array_shift($scores);
        return min($scoreMax, $scorePerCount * intdiv(count(self::cardMgr()->getPlayerCompostCards($this->playerId)), $scoreCountDivider));
    }

    private function scoreCardsWithLessCost(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreCountDivider = array_shift($scores);
        $scoreCost = array_shift($scores);
        $cardCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            $cardSoil = $otherCard->getCardDef()->soil;
            if ($cardSoil !== null && $cardSoil <= $scoreCost) {
                $cardCount += 1;
            }
        }
        return ($scorePerCount * intdiv($cardCount, $scoreCountDivider));
    }

    private function scoreCardsWithMoreCost(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreCountDivider = array_shift($scores);
        $scoreCost = array_shift($scores);
        $cardCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            $cardSoil = $otherCard->getCardDef()->soil;
            if ($cardSoil !== null && $cardSoil >= $scoreCost) {
                $cardCount += 1;
            }
        }
        return ($scorePerCount * intdiv($cardCount, $scoreCountDivider));
    }

    private function scoreCardsWithMoreSprouts(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreCountDivider = array_shift($scores);
        $scoreSprout = array_shift($scores);
        $cardCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            if ($otherCard->sproutCount >= $scoreSprout) {
                $cardCount += 1;
            }
        }
        return ($scorePerCount * intdiv($cardCount, $scoreCountDivider));
    }

    private function scoreCardsWithMoreGrowth(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreGrowth = array_shift($scores);
        $cardCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            if (!$otherCard->getCardDef()->isFlora()) {
                continue;
            }
            if ($otherCard->growthCount >= $scoreGrowth) {
                $cardCount += 1;
            }
        }
        return ($scorePerCount * $cardCount);
    }

    private function scorePerEvent(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreCountDivider = array_shift($scores);
        $scoreMax = array_shift($scores);
        return min($scoreMax, $scorePerCount * intdiv(count(self::cardMgr()->getPlayerBoardEventCards($this->playerId)), $scoreCountDivider));
    }

    private function getCardsForDirection(int $scoreDirection)
    {
        switch ($scoreDirection) {
            case \EA\AB_DIRECTION_ROW:
                return self::cardMgr()->getPlayerTableauCardsPerRow($this->playerId);
                break;
            case \EA\AB_DIRECTION_COLUMN:
                return self::cardMgr()->getPlayerTableauCardsPerColumn($this->playerId);
                break;
            default:
                throw new \BgaSystemException("BUG! Ecosystem has unknown direction: $scoreDirection");
        }
    }

    private function scoreDirectionWithDifferentAbilityColor(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreDirection = array_shift($scores);
        $cardsPerDirection = $this->getCardsForDirection($scoreDirection);
        $directionCount = 0;
        foreach ($cardsPerDirection as $cards) {
            if (count($cards) != \EA\MAX_TABLEAU_SIZE) {
                continue;
            }
            if ($this->hasDifferentAbilityColorSet(array_values($cards), [])) {
                $directionCount += 1;
            }
        }
        return ($scorePerCount * $directionCount);
    }

    private function hasDifferentAbilityColorSet(array $cards, array $seenColorSet)
    {
        if (count($cards) == 0) {
            return true;
        }
        $firstCardDef = $cards[0]->getCardDef();
        foreach ($firstCardDef->getAllAbilities() as $ability) {
            if ($ability->color == \EA\AB_COLOR_MULTICOLOR) {
                foreach (\EA\MULTICOLOR_COLORS as $color) {
                    if (array_key_exists($color, $seenColorSet)) {
                        continue;
                    }
                    if ($this->hasDifferentAbilityColorSet(array_slice($cards, 1), array_replace($seenColorSet, [$color => true]))) {
                        return true;
                    }
                }
            } else {
                if (array_key_exists($ability->color, $seenColorSet)) {
                    continue;
                }
                if ($this->hasDifferentAbilityColorSet(array_slice($cards, 1), array_replace($seenColorSet, [$ability->color => true]))) {
                    return true;
                }
            }
        }
        return false;
    }

    private function scoreDirectionWithSameAbilityColor(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreDirection = array_shift($scores);
        $cardsPerDirection = $this->getCardsForDirection($scoreDirection);
        $directionCount = 0;
        foreach ($cardsPerDirection as $cards) {
            if (count($cards) != \EA\MAX_TABLEAU_SIZE) {
                continue;
            }
            if ($this->hasSameAbilityColor(array_values($cards), null)) {
                $directionCount += 1;
            }
        }
        return ($scorePerCount * $directionCount);
    }

    private function hasSameAbilityColor(array $cards, ?int $matchColor)
    {
        if (count($cards) == 0) {
            return true;
        }
        $firstCardDef = $cards[0]->getCardDef();
        foreach ($firstCardDef->getAllAbilities() as $ability) {
            if ($ability->color == \EA\AB_COLOR_MULTICOLOR) {
                foreach (\EA\MULTICOLOR_COLORS as $color) {
                    if ($matchColor !== null && $matchColor != $color) {
                        continue;
                    }
                    if ($this->hasSameAbilityColor(array_slice($cards, 1), $color)) {
                        return true;
                    }
                }
            } else {
                if ($matchColor !== null && $matchColor != $ability->color) {
                    continue;
                }
                if ($this->hasSameAbilityColor(array_slice($cards, 1), $ability->color)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function scoreCardsWithLessScore(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreCountDivider = array_shift($scores);
        $scoreScore = array_shift($scores);
        $cardCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            if ($otherCard->getCardDef()->score <= $scoreScore) {
                $cardCount += 1;
            }
        }
        return ($scorePerCount * intdiv($cardCount, $scoreCountDivider));
    }

    private function scoreCardsWithMoreScore(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreCountDivider = array_shift($scores);
        $scoreScore = array_shift($scores);
        $cardCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            if ($otherCard->getCardDef()->score >= $scoreScore) {
                $cardCount += 1;
            }
        }
        return ($scorePerCount * intdiv($cardCount, $scoreCountDivider));
    }

    private function scoreCardsWithDirections(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreCountDivider = array_shift($scores);
        $cardCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            foreach ($otherCard->getCardDef()->getAllAbilities() as $ability) {
                if ($ability->getDirection() !== null) {
                    $cardCount += 1;
                    break;
                }
            }
        }
        return ($scorePerCount * intdiv($cardCount, $scoreCountDivider));
    }

    private function scoreDirectionWithDifferentScore(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreDirection = array_shift($scores);
        $cardsPerDirection = $this->getCardsForDirection($scoreDirection);
        $directionCount = 0;
        foreach ($cardsPerDirection as $cards) {
            if (count($cards) != \EA\MAX_TABLEAU_SIZE) {
                continue;
            }
            $scores = array_map(fn ($c) => $c->getCardDef()->score, $cards);
            if (count(array_unique($scores)) == \EA\MAX_TABLEAU_SIZE) {
                $directionCount += 1;
            }
        }
        return ($scorePerCount * $directionCount);
    }

    private function scoreCardsWithLessHabitat(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreCountDivider = array_shift($scores);
        $scoreHabitatCount = array_shift($scores);
        $cardCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            if ($otherCard->getCardDef()->habitatCount() <= $scoreHabitatCount) {
                $cardCount += 1;
            }
        }
        return $scorePerCount * intdiv($cardCount, $scoreCountDivider);
    }

    private function scoreCardsWithMoreHabitat(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreCountDivider = array_shift($scores);
        $scoreHabitatCount = array_shift($scores);
        $cardCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            if ($otherCard->getCardDef()->habitatCount() >= $scoreHabitatCount) {
                $cardCount += 1;
            }
        }
        return $scorePerCount * intdiv($cardCount, $scoreCountDivider);
    }

    private function scoreDirectionWithDifferentSproutCount(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreDirection = array_shift($scores);
        $cardsPerDirection = $this->getCardsForDirection($scoreDirection);
        $directionCount = 0;
        foreach ($cardsPerDirection as $cards) {
            if (count($cards) != \EA\MAX_TABLEAU_SIZE) {
                continue;
            }
            $sprouts = array_map(fn ($c) => $c->sproutCount, $cards);
            if (count(array_unique($sprouts)) == \EA\MAX_TABLEAU_SIZE) {
                $directionCount += 1;
            }
        }
        return ($scorePerCount * $directionCount);
    }

    private function scoreDirectionWithDifferentGrowthCount(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreDirection = array_shift($scores);
        $cardsPerDirection = $this->getCardsForDirection($scoreDirection);
        $directionCount = 0;
        foreach ($cardsPerDirection as $cards) {
            if (count($cards) != \EA\MAX_TABLEAU_SIZE) {
                continue;
            }
            $growths = array_map(fn ($c) => $c->growthCount, $cards);
            if (count(array_unique($growths)) == \EA\MAX_TABLEAU_SIZE) {
                $directionCount += 1;
            }
        }
        return ($scorePerCount * $directionCount);
    }

    private function scorePerSprout(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreCountDivider = array_shift($scores);
        $sproutCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            $sproutCount += $otherCard->sproutCount;
        }
        return $scorePerCount * intdiv($sproutCount, $scoreCountDivider);
    }

    private function scorePerGrowth(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreCountDivider = array_shift($scores);
        $growthCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            $growthCount += $otherCard->growthCount;
        }
        return $scorePerCount * intdiv($growthCount, $scoreCountDivider);
    }

    private function scorePerCardsSets(\EA\Card $card, array $scores)
    {
        $scoreNbSet = array_shift($scores);

        $countEvent = count(self::cardMgr()->getPlayerBoardEventCards($this->playerId));
        $countTerrain = 0;
        foreach (self::cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $card) {
            if ($card->getCardDef()->isTerrain()) {
                $countTerrain += 1;
            }
        }
        $countColor = [];
        foreach (\EA\MULTICOLOR_COLORS as $color) {
            $countColor[$color] = 0;
        }

        $cards = array_values(self::cardMgr()->getPlayerIslandClimateTableauCards($this->playerId));
        // Filter to keep only color cards
        $cards = array_values(array_filter($cards, function ($c) {
            foreach (\EA\MULTICOLOR_COLORS as $color) {
                if ($c->getCardDef()->getAbilityMatchingColor($color) !== null) {
                    return true;
                }
            }
            return false;
        }));
        $countColor = $this->countColorSets($cards, $countColor);
        $minCountColor = 0;
        if (count($countColor) > 0) {
            $minCountColor = min($countColor);
        }
        return ($scoreNbSet * min($countEvent, $countTerrain, $minCountColor));
    }

    private function countColorSets(array $cards, array $countColor)
    {
        if (count($cards) == 0) {
            return $countColor;
        }
        $cardDef = $cards[0]->getCardDef();
        $newSets = [];
        foreach (\EA\MULTICOLOR_COLORS as $color) {
            if ($cardDef->getAbilityMatchingColor($color) !== null) {
                $countColor[$color] += 1;
                $newSets[] = $this->countColorSets(array_slice($cards, 1), $countColor);
                $countColor[$color] -= 1;
            }
        }
        $smallestNumber = 0;
        $smallestSet = [];
        foreach ($newSets as $set) {
            $small = 0;
            if (count($set) > 0) {
                $small = min($set);
            };
            if ($small > $smallestNumber) {
                $smallestNumber = $small;
                $smallestSet = $set;
            }
        }
        return $smallestSet;
    }

    private function scorePerCardsWithTwoAbilities(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreCountDivider = array_shift($scores);
        $cardCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            if (count($otherCard->getCardDef()->getAllAbilities()) >= 2) {
                $cardCount += 1;
            }
        }
        return $scorePerCount * intdiv($cardCount, $scoreCountDivider);
    }

    private function scoreDirectionWithDifferentCardType(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreDirection = array_shift($scores);
        $cardsPerDirection = $this->getCardsForDirection($scoreDirection);
        $directionCount = 0;
        foreach ($cardsPerDirection as $cards) {
            if (count($cards) != \EA\MAX_TABLEAU_SIZE) {
                continue;
            }
            if ($this->hasDifferentCardTypeSet(array_values($cards), [])) {
                $directionCount += 1;
            }
        }
        return ($scorePerCount * $directionCount);
    }

    private function hasDifferentCardTypeSet(array $cards, array $seenCardTypeSet)
    {
        if (count($cards) == 0) {
            return true;
        }
        $firstCardDef = $cards[0]->getCardDef();
        $cardTypes = null;
        if ($firstCardDef->type == \EA\CARD_TYPE_JOKER) {
            $cardTypes = \EA\CARD_TYPES_FOR_JOKER;
        } else {
            $cardTypes = [$firstCardDef->type];
        }
        foreach ($cardTypes as $type) {
            if (array_key_exists($type, $seenCardTypeSet)) {
                continue;
            }
            if ($this->hasDifferentCardTypeSet(array_slice($cards, 1), array_replace($seenCardTypeSet, [$type => true]))) {
                return true;
            }
        }
        return false;
    }

    private function scoreDirectionWithSameCardType(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreDirection = array_shift($scores);
        $cardsPerDirection = $this->getCardsForDirection($scoreDirection);
        $directionCount = 0;
        foreach ($cardsPerDirection as $cards) {
            if (count($cards) != \EA\MAX_TABLEAU_SIZE) {
                continue;
            }
            if ($this->hasSameCardTypeSet(array_values($cards), null)) {
                $directionCount += 1;
            }
        }
        return ($scorePerCount * $directionCount);
    }

    private function hasSameCardTypeSet(array $cards, ?int $matchCardType)
    {
        if (count($cards) == 0) {
            return true;
        }
        $firstCardDef = $cards[0]->getCardDef();
        $cardTypes = null;
        if ($firstCardDef->type == \EA\CARD_TYPE_JOKER) {
            $cardTypes = \EA\CARD_TYPES_FOR_JOKER;
        } else {
            $cardTypes = [$firstCardDef->type];
        }
        foreach ($cardTypes as $type) {
            if ($matchCardType !== null && $type != $matchCardType) {
                continue;
            }
            if ($this->hasSameCardTypeSet(array_slice($cards, 1), $type)) {
                return true;
            }
        }
        return false;
    }

    private function scoreDirectionWithDifferentHabitat(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreDirection = array_shift($scores);
        $cardsPerDirection = $this->getCardsForDirection($scoreDirection);
        $directionCount = 0;
        foreach ($cardsPerDirection as $cards) {
            if (count($cards) != \EA\MAX_TABLEAU_SIZE) {
                continue;
            }
            if ($this->hasDifferentHabitatSet(array_values($cards), [])) {
                $directionCount += 1;
            }
        }
        return ($scorePerCount * $directionCount);
    }

    private function hasDifferentHabitatSet(array $cards, array $seenHabitatSet)
    {
        if (count($cards) == 0) {
            return true;
        }
        $firstCardDef = $cards[0]->getCardDef();
        foreach (\EA\CARD_HABITATS as $habitat) {
            if (!$firstCardDef->hasHabitat($habitat)) {
                continue;
            }
            if (array_key_exists($habitat, $seenHabitatSet)) {
                continue;
            }
            if ($this->hasDifferentHabitatSet(array_slice($cards, 1), array_replace($seenHabitatSet, [$habitat => true]))) {
                return true;
            }
        }
        return false;
    }

    private function scoreDirectionWithSameHabitat(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreDirection = array_shift($scores);
        $cardsPerDirection = $this->getCardsForDirection($scoreDirection);
        $directionCount = 0;
        foreach ($cardsPerDirection as $cards) {
            if (count($cards) != \EA\MAX_TABLEAU_SIZE) {
                continue;
            }
            $allSameHabitat = true;
            foreach (\EA\CARD_HABITATS as $habitat) {
                $allSameHabitat = true;
                foreach ($cards as $otherCard) {
                    if (!$otherCard->getCardDef()->hasHabitat($habitat)) {
                        $allSameHabitat = false;
                        break;
                    }
                }
                if ($allSameHabitat) {
                    break;
                }
            }
            if ($allSameHabitat) {
                $directionCount += 1;
            }
        }
        return ($scorePerCount * $directionCount);
    }

    private function scorePerCardWithBoldGeography(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreCountDivider = array_shift($scores);
        $cardCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            if (!$otherCard->getCardDef()->isFlora()) {
                continue;
            }
            if ($otherCard->getCardDef()->isBoldGeography) {
                $cardCount += 1;
            }
        }
        return $scorePerCount * intdiv($cardCount, $scoreCountDivider);
    }

    private function scorePerCardWithItalicColor(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreCountDivider = array_shift($scores);
        $cardCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            if (!$otherCard->getCardDef()->isFlora()) {
                continue;
            }
            if ($otherCard->getCardDef()->isItalicColor) {
                $cardCount += 1;
            }
        }
        return $scorePerCount * intdiv($cardCount, $scoreCountDivider);
    }

    private function scorePerCardWithUnderlineAnimal(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreCountDivider = array_shift($scores);
        $cardCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            if (!$otherCard->getCardDef()->isFlora()) {
                continue;
            }
            if ($otherCard->getCardDef()->isUnderlineAnimal) {
                $cardCount += 1;
            }
        }
        return $scorePerCount * intdiv($cardCount, $scoreCountDivider);
    }

    private function scorePerCardWithExactPieceSpot(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreCountDivider = array_shift($scores);
        $scoreSpot = array_shift($scores);
        $cardCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            if (!$otherCard->getCardDef()->isFlora()) {
                continue;
            }
            if (
                $otherCard->getCardDef()->sproutMax !== null
                && $otherCard->getCardDef()->sproutMax == $scoreSpot
            ) {
                $cardCount += 1;
            }
        }
        return $scorePerCount * intdiv($cardCount, $scoreCountDivider);
    }

    private function scorePerCardWithLessPieceSpot(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreCountDivider = array_shift($scores);
        $scoreSpot = array_shift($scores);
        $scorePieceType = array_shift($scores);
        $cardCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            if (!$otherCard->getCardDef()->isFlora()) {
                continue;
            }
            $spotCount = null;
            switch ($scorePieceType) {
                case \EA\ABILITY_SPROUT:
                    $spotCount = $otherCard->getCardDef()->sproutMax;
                    break;
                case \EA\ABILITY_GROWTH:
                    $spotCount = $otherCard->getCardDef()->growthMax;
                    break;
                default:
                    throw new \BgaSystemException("BUG! Unknown ecosystem scorePieceType: $scorePieceType");
            }
            if ($spotCount === null || $spotCount <= $scoreSpot) {
                $cardCount += 1;
            }
        }
        return $scorePerCount * intdiv($cardCount, $scoreCountDivider);
    }

    private function scorePerCardWithMorePieceSpot(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreCountDivider = array_shift($scores);
        $scoreSpot = array_shift($scores);
        $scorePieceType = array_shift($scores);
        $cardCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            if (!$otherCard->getCardDef()->isFlora()) {
                continue;
            }
            $spotCount = null;
            switch ($scorePieceType) {
                case \EA\ABILITY_SPROUT:
                    $spotCount = $otherCard->getCardDef()->sproutMax;
                    break;
                case \EA\ABILITY_GROWTH:
                    $spotCount = $otherCard->getCardDef()->growthMax;
                    break;
                default:
                    throw new \BgaSystemException("BUG! Unknown ecosystem scorePieceType: $scorePieceType");
            }
            if ($spotCount !== null && $spotCount >= $scoreSpot) {
                $cardCount += 1;
            }
        }
        return $scorePerCount * intdiv($cardCount, $scoreCountDivider);
    }

    private function scorePerCardWithEvenScore(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreCountDivider = array_shift($scores);
        $cardCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            if (($otherCard->getCardDef()->score % 2) == 0) {
                $cardCount += 1;
            }
        }
        return $scorePerCount * intdiv($cardCount, $scoreCountDivider);
    }

    private function scorePerCardWithOddScore(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreCountDivider = array_shift($scores);
        $cardCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            if (($otherCard->getCardDef()->score % 2) != 0) {
                $cardCount += 1;
            }
        }
        return $scorePerCount * intdiv($cardCount, $scoreCountDivider);
    }

    private function scorePerCardWithLessGrowthMaxScore(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreCountDivider = array_shift($scores);
        $scoreGrowthScore = array_shift($scores);
        $cardCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            if (!$otherCard->getCardDef()->isFlora()) {
                continue;
            }
            $growth = $otherCard->getCardDef()->growthScore;
            if ($growth === null || $growth <= $scoreGrowthScore) {
                $cardCount += 1;
            }
        }
        return $scorePerCount * intdiv($cardCount, $scoreCountDivider);
    }

    private function scorePerCardWithMoreGrowthMaxScore(\EA\Card $card, array $scores)
    {
        $scorePerCount = array_shift($scores);
        $scoreCountDivider = array_shift($scores);
        $scoreGrowthScore = array_shift($scores);
        $cardCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            if (!$otherCard->getCardDef()->isFlora()) {
                continue;
            }
            $growth = $otherCard->getCardDef()->growthScore;
            if ($growth !== null && $growth >= $scoreGrowthScore) {
                $cardCount += 1;
            }
        }
        return $scorePerCount * intdiv($cardCount, $scoreCountDivider);
    }

    private function scorePerCardWithFilledFieces(\EA\Card $card, array $scores)
    {
        $scorePerCard = array_shift($scores);
        $scorePieceType = array_shift($scores);
        $cardCount = 0;
        foreach ($this->cardMgr()->getPlayerIslandClimateTableauCards($this->playerId) as $otherCard) {
            if (!$otherCard->getCardDef()->isFlora()) {
                continue;
            }
            $pieceCount = null;
            $pieceMax = null;
            switch ($scorePieceType) {
                case \EA\ABILITY_SPROUT:
                    $pieceCount = $otherCard->sproutCount;
                    $pieceMax = $otherCard->getCardDef()->sproutMax;
                    break;
                case \EA\ABILITY_GROWTH:
                    $pieceCount = $otherCard->growthCount;
                    $pieceMax = $otherCard->getCardDef()->growthMax;
                    break;
                default:
                    throw new \BgaSystemException("BUG! Unknown brown ability scorePieceType: $scorePieceType");
            }
            if ($pieceMax !== null && $pieceMax > 0 && $pieceCount == $pieceMax) {
                $cardCount += 1;
            }
        }
        return ($cardCount * $scorePerCard);
    }
}
