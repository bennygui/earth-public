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

namespace EA\Actions\Fauna;

require_once(__DIR__ . '/.././../../BX/php/Action.php');
require_once(__DIR__ . '/../Ability.php');

class MoveLeafTokenToFinalPosition extends \BX\Action\BaseActionCommandNoUndo
{
    public function __construct(int $playerId)
    {
        parent::__construct($playerId);
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $cardMgr = self::getMgr('card');
        $leafTokenMgr = self::getMgr('leaf_token');
        foreach ($leafTokenMgr->getFaunaLeafTokenByPlayerId($this->playerId) as $token) {
            if (!$token->isOnFaunaBoardFauna() || $token->locationOrder !== null) {
                continue;
            }

            $max = -1;
            foreach ($leafTokenMgr->getFaunaLeafTokenAtFaunaForLocation($token->locationX, $token->locationY) as $otherToken) {
                if ($otherToken->locationOrder !== null && $otherToken->locationOrder > $max) {
                    $max = $otherToken->locationOrder;
                }
            }
            $token->modifyAction();
            $token->moveToFaunaBoardFaunaFinalOrder($max + 1);

            $card = $cardMgr->getFaunaCardAtLocation($token->locationX, $token->locationY);
            $message = '';
            if (!isGameModeBeginner()) {
                $message = clienttranslate('${player_name} takes position ${position} for a Fauna objective: ${cardName} ${cardImage}');
            }
            $notifier->notify(
                NTF_UPDATE_LEAF_TOKEN,
                $message,
                [
                    'leafToken' => $token->toPlayerUI($this->playerId),
                    'position' => $token->locationOrder + 1,
                    'cardName' => $card->getCardDef()->name,
                    'cardImage' => $card->cardId,
                    'i18n' => ['cardName'],
                ]
            );
        }
    }
}

class RevealPrivateFauna extends \BX\Action\BaseActionCommandNoUndo
{
    public function __construct(int $playerId)
    {
        parent::__construct($playerId);
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $cardMgr = self::getMgr('card');
        $gameStateMgr = self::getMgr('game_state');
        $leafTokenMgr = self::getMgr('leaf_token');
        foreach ($leafTokenMgr->getFaunaLeafTokenByPlayerId($this->playerId) as $token) {
            if (!$token->hasPrivateLocation()) {
                continue;
            }

            $token->modifyAction();
            $token->moveToPublicLocation();

            $card = $cardMgr->getFaunaCardAtLocation($token->locationX, $token->locationY);
            if (isGameModeBeginner()) {
                $notifText = clienttranslate('${player_name} claims a Fauna objective: ${cardName} ${cardImage}');
            } else {
                $notifText = clienttranslate('${player_name} claims a Fauna objective: ${cardName} ${cardImage} The exact score will be known only at the end of the current turn.');
            }
            $notifier->notify(
                NTF_UPDATE_LEAF_TOKEN,
                $notifText,
                [
                    'isPublicReveal' => true,
                    'leafToken' => $token->toPlayerUI($this->playerId),
                    'playerActiveOrder' => $gameStateMgr->playerIdsWithActiveOrder(),
                    'cardName' => $card->getCardDef()->name,
                    'cardImage' => $card->cardId,
                    'i18n' => ['cardName'],
                ]
            );
        }
    }
}

const FAUNA_ABILITY_TO_ACTION_CLASS = [
    \EA\AB_FAUNA_FLORA_WITH_PIECES => 'FaunaActionFloraWithPieces',
    \EA\AB_FAUNA_SOIL_COUNT => 'FaunaActionSoilCount',
    \EA\AB_FAUNA_COMPOST_COUNT => 'FaunaActionCompostCount',
    \EA\AB_FAUNA_HAND_COUNT => 'FaunaActionHandCount',
    \EA\AB_FAUNA_EVENT_COUNT => 'FaunaActionEventCount',
    \EA\AB_FAUNA_CARDS_WITH_HABITAT => 'FaunaActionCardsWithHabitat',
    \EA\AB_FAUNA_FLORA_WITH_TYPE => 'FaunaActionFloraWithType',
    \EA\AB_FAUNA_CARDS_WITH_ABILITY_COLOR => 'FaunaActionCardsWithAbilityColor',
    \EA\AB_FAUNA_COLUMNS => 'FaunaActionColumns',
    \EA\AB_FAUNA_ROWS => 'FaunaActionRows',
    \EA\AB_FAUNA_DIAGONALS => 'FaunaActionDiagonals',
    \EA\AB_FAUNA_WITH_DIRECTIONS => 'FaunaActionWithDirections',
    \EA\AB_FAUNA_CARDS_WITH_LESS_HABITAT => 'FaunaActionCardsWithLessHabitat',
    \EA\AB_FAUNA_CARDS_WITH_MORE_HABITAT => 'FaunaActionCardsWithMoreHabitat',
    \EA\AB_FAUNA_CARDS_WITH_LESS_SCORE => 'FaunaActionCardsWithLessScore',
    \EA\AB_FAUNA_CARDS_WITH_MORE_SCORE => 'FaunaActionCardsWithMoreScore',
    \EA\AB_FAUNA_CARDS_WITH_LESS_COST => 'FaunaActionCardsWithLessCost',
    \EA\AB_FAUNA_CARDS_WITH_MORE_COST => 'FaunaActionCardsWithMoreCost',
    \EA\AB_FAUNA_CARDS_SETS => 'FaunaActionCardsSets',
    \EA\AB_FAUNA_CARDS_WITH_MORE_ABILITY => 'FaunaActionCardsWithMoreAbility',
    \EA\AB_FAUNA_FLORA_WITH_BOLD_GEOGRAPHY => 'FaunaActionFloraWithBoldGeography',
    \EA\AB_FAUNA_FLORA_WITH_ITALIC_COLOR => 'FaunaActionFloraWithItalicColor',
    \EA\AB_FAUNA_FLORA_WITH_UNDERLINE_ANIMAL => 'FaunaActionFloraWithUnderlineAnimal',
    \EA\AB_FAUNA_FLORA_WITH_EXACT_PIECE_SPOT => 'FaunaActionFloraWithExactPieceSpot',
    \EA\AB_FAUNA_FLORA_WITH_LESS_PIECE_SPOT => 'FaunaActionFloraWithLessPieceSpot',
    \EA\AB_FAUNA_FLORA_WITH_MORE_PIECE_SPOT => 'FaunaActionFloraWithMorePieceSpot',
    \EA\AB_FAUNA_FLORA_FILLED_FIECES => 'FaunaActionFloraFilledFieces',
    \EA\AB_FAUNA_FLORA_EMPTY_FIECES => 'FaunaActionFloraEmptyFieces',
    \EA\AB_FAUNA_CARDS_WITH_EVEN_SCORE => 'FaunaActionCardsWithEvenScore',
    \EA\AB_FAUNA_CARDS_WITH_ODD_SCORE => 'FaunaActionCardsWithOddScore',
];

function getFaunaAction(int $playerId, int $cardId, bool $considerPrivateVisibility)
{
    $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
    $card = $cardMgr->getCardById($cardId);
    if ($card === null || !$card->getCardDef()->isFauna()) {
        throw new \BgaSystemException("BUG! Card {$cardId} is not a fauna card");
    }
    $scores = $card->getCardDef()->getFirstAbility()->getScores();
    $faunaAbility = $scores[0];
    if (!array_key_exists($faunaAbility, FAUNA_ABILITY_TO_ACTION_CLASS)) {
        throw new \BgaSystemException("BUG! Card {$cardId} with faunaAbility $faunaAbility that does have a class");
    }
    $faunaAbilityClass = "\\EA\\Actions\\Fauna\\" . FAUNA_ABILITY_TO_ACTION_CLASS[$faunaAbility];
    return new $faunaAbilityClass($playerId, $cardId, $considerPrivateVisibility);
}

class FaunaProgress extends \BX\UI\UISerializable
{
    public $progress;
    public $objective;

    public function __construct(int $progress, int $objective)
    {
        $this->progress = $progress;
        $this->objective = $objective;
    }

    public static function newWithRequirements()
    {
        return new FaunaProgress(0, 0);
    }

    public function hasRequirements()
    {
        return $this->progress >= $this->objective;
    }

    public function jsonSerialize()
    {
        $ret = parent::jsonSerialize();
        $ret['hasRequirements'] = $this->hasRequirements();
        return $ret;
    }
}

abstract class FaunaActionBase extends \BX\Action\BaseActionCommand
{
    protected $cardId;
    protected $considerPrivateVisibility;
    protected $leafId;
    protected $hasFaunaRequirements;
    protected $undoToken;

    public function __construct(int $playerId, int $cardId, bool $considerPrivateVisibility)
    {
        parent::__construct($playerId);
        $this->cardId = $cardId;
        $this->considerPrivateVisibility = $considerPrivateVisibility;
        $this->hasFaunaRequirements = null;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->considerPrivateVisibility === null) {
            $this->considerPrivateVisibility = false;
        }
        $cardMgr = self::getMgr('card');
        $card = $cardMgr->getCardById($this->cardId);
        if ($card === null || !$card->getCardDef()->isFauna()) {
            throw new \BgaSystemException("BUG! Card {$this->cardId} is not a fauna card");
        }
        $leafTokenMgr = self::getMgr('leaf_token');
        $this->leafId = $leafTokenMgr->getLeafIdFromBoardLocation($card->locationX, $card->locationY);
        $token = $leafTokenMgr->getLeafTokenByLeafIdAndPlayerId($this->leafId, $this->playerId);
        if ($token->isOnFaunaBoard()) {
            return;
        }
        if ($this->hasFaunaRequirements === null) {
            $scores = $card->getCardDef()->getFirstAbility()->getScores();
            $this->hasFaunaRequirements = $this->playerGetFaunaProgress($this->playerId, $scores)->hasRequirements();
        }
        if (!$this->hasFaunaRequirements) {
            return;
        }
        $this->undoToken = \BX\Meta\deepClone($token);
        $token->modifyAction();
        if (!isGameSolo() && $this->considerPrivateVisibility) {
            $token->moveToFaunaBoardFaunaPrivate($card->locationX, $card->locationY);
        } else {
            $token->moveToFaunaBoardFauna($card->locationX, $card->locationY);
        }
        $gameStateMgr = self::getMgr('game_state');
        $notifText = clienttranslate('${player_name} claims a Fauna objective: ${cardName} ${cardImage}');
        if (isGameSolo()) {
            $max = -1;
            foreach ($leafTokenMgr->getFaunaLeafTokenAtFaunaForLocation($token->locationX, $token->locationY) as $otherToken) {
                if ($otherToken->locationOrder !== null && $otherToken->locationOrder > $max) {
                    $max = $otherToken->locationOrder;
                }
            }
            $token->moveToFaunaBoardFaunaFinalOrder($max + 1);
        } else if (isGameModeBeginner()) {
            $notifText = clienttranslate('${player_name} claims a Fauna objective: ${cardName} ${cardImage}');
        } else {
            $notifText = clienttranslate('${player_name} claims a Fauna objective: ${cardName} ${cardImage} The exact score will be known only at the end of the current turn.');
        }
        if (!isGameSolo() && $this->considerPrivateVisibility) {
            $notifier->notifyPrivate(
                NTF_UPDATE_LEAF_TOKEN,
                $notifText,
                [
                    'leafToken' => $token->toPlayerUI($this->playerId),
                    'playerActiveOrder' => $gameStateMgr->playerIdsWithActiveOrder(),
                    'cardName' => $card->getCardDef()->name,
                    'cardImage' => $card->cardId,
                    'i18n' => ['cardName'],
                ]
            );
        } else {
            $notifier->notify(
                NTF_UPDATE_LEAF_TOKEN,
                $notifText,
                [
                    'leafToken' => $token->toPlayerUI($this->playerId),
                    'playerActiveOrder' => $gameStateMgr->playerIdsWithActiveOrder(),
                    'cardName' => $card->getCardDef()->name,
                    'cardImage' => $card->cardId,
                    'i18n' => ['cardName'],
                ]
            );
        }
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->undoToken !== null) {
            $gameStateMgr = self::getMgr('game_state');
            $notifier->notifyNoMessage(
                NTF_UPDATE_LEAF_TOKEN,
                [
                    'leafToken' => $this->undoToken->toPlayerUI($this->playerId),
                    'playerActiveOrder' => $gameStateMgr->playerIdsWithActiveOrder(),
                ]
            );
        }
    }

    public function getPlayerFaunaProgress()
    {
        $leafTokenMgr = self::getMgr('leaf_token');
        $cardMgr = self::getMgr('card');
        $card = $cardMgr->getCardById($this->cardId);
        if ($card === null || !$card->getCardDef()->isFauna()) {
            throw new \BgaSystemException("BUG! Card {$this->cardId} is not a fauna card");
        }
        $leafId = $leafTokenMgr->getLeafIdFromBoardLocation($card->locationX, $card->locationY);
        $token = $leafTokenMgr->getLeafTokenByLeafIdAndPlayerId($leafId, $this->playerId);
        if (
            ($this->considerPrivateVisibility && $token->isOnFaunaBoard())
            ||
            (!$this->considerPrivateVisibility && $token->isOnFaunaBoardFaunaPublic())
        ) {
            return FaunaProgress::newWithRequirements();
        }
        $scores = $card->getCardDef()->getFirstAbility()->getScores();
        return $this->playerGetFaunaProgress($this->playerId, $scores);
    }

    private function playerGetFaunaProgress(int $playerId, array $scores)
    {
        $faunaAbility = array_shift($scores);
        if (!array_key_exists($faunaAbility, FAUNA_ABILITY_TO_ACTION_CLASS)) {
            throw new \BgaSystemException("BUG! Card {$this->cardId} with faunaAbility $faunaAbility that does have a class");
        }
        $faunaAbilityClass = FAUNA_ABILITY_TO_ACTION_CLASS[$faunaAbility];
        $currentClass = get_class($this);
        $currentClass = substr($currentClass, strrpos($currentClass, '\\') + 1);
        if ($currentClass != $faunaAbilityClass) {
            throw new \BgaSystemException("BUG! Card {$this->cardId} with faunaAbility $faunaAbility and $faunaAbilityClass is wrong class $currentClass");
        }
        return $this->onGetPlayerFaunaProgress($playerId, $scores);
    }

    protected function getPlayerTableauCards()
    {
        $cardMgr = self::getMgr('card');
        return array_filter(
            $cardMgr->getPlayerTableauCards($this->playerId, $this->considerPrivateVisibility ? $this->playerId : null),
            fn ($c) => $c !== null && $c->cardId !== null
        );
    }

    protected function getPlayerIslandClimateTableauCards()
    {
        $cardMgr = self::getMgr('card');
        return array_filter(
            $cardMgr->getPlayerIslandClimateTableauCards($this->playerId, $this->considerPrivateVisibility),
            fn ($c) => $c !== null && $c->cardId !== null
        );
    }

    abstract protected function onGetPlayerFaunaProgress(int $playerId, array $scores);
}

class FaunaActionFloraWithPieces extends FaunaActionBase
{
    protected function onGetPlayerFaunaProgress(int $playerId, array $scores)
    {
        $scoreNbFlora = array_shift($scores);
        $scoreType = array_shift($scores);
        $scoreNbType = array_shift($scores);
        $countNbFlora = 0;
        foreach ($this->getPlayerTableauCards() as $card) {
            if (!$card->getCardDef()->isFlora()) {
                continue;
            }
            $countNbType = null;
            switch ($scoreType) {
                case \EA\ABILITY_GROWTH:
                    $countNbType = $card->growthCount;
                    break;
                case \EA\ABILITY_SPROUT:
                    $countNbType = $card->sproutCount;
                    break;
                default:
                    throw new \BgaSystemException("BUG! Unsupported fauna scoreType $scoreType");
            }
            if ($countNbType >= $scoreNbType) {
                $countNbFlora += 1;
            }
        }
        return new FaunaProgress($countNbFlora, $scoreNbFlora);
    }
}

class FaunaActionSoilCount extends FaunaActionBase
{
    protected function onGetPlayerFaunaProgress(int $playerId, array $scores)
    {
        $scoreNbSoil = array_shift($scores);
        $playerStateMgr = self::getMgr('player_state');
        return new FaunaProgress($playerStateMgr->getPlayerSoilCount($playerId), $scoreNbSoil);
    }
}

class FaunaActionCompostCount extends FaunaActionBase
{
    protected function onGetPlayerFaunaProgress(int $playerId, array $scores)
    {
        $scoreNbCompost = array_shift($scores);
        $cardMgr = self::getMgr('card');
        return new FaunaProgress(count($cardMgr->getPlayerCompostCards($playerId)), $scoreNbCompost);
    }
}

class FaunaActionHandCount extends FaunaActionBase
{
    protected function onGetPlayerFaunaProgress(int $playerId, array $scores)
    {
        $scoreNbHand = array_shift($scores);
        $cardMgr = self::getMgr('card');
        return new FaunaProgress(count(array_filter($cardMgr->getPlayerHandCards($playerId), fn ($c) => !$c->isHandChoosing())), $scoreNbHand);
    }
}

class FaunaActionEventCount extends FaunaActionBase
{
    protected function onGetPlayerFaunaProgress(int $playerId, array $scores)
    {
        $scoreNbEvent = array_shift($scores);
        $cardMgr = self::getMgr('card');
        return new FaunaProgress(count($cardMgr->getPlayerBoardEventCards($playerId)), $scoreNbEvent);
    }
}

class FaunaActionCardsWithHabitat extends FaunaActionBase
{
    protected function onGetPlayerFaunaProgress(int $playerId, array $scores)
    {
        $scoreNbCard = array_shift($scores);
        $scoreHabitat = array_shift($scores);
        $countNbCard = 0;
        foreach ($this->getPlayerIslandClimateTableauCards() as $card) {
            if ($card->getCardDef()->hasHabitat($scoreHabitat)) {
                $countNbCard += 1;
            }
        }
        return new FaunaProgress($countNbCard, $scoreNbCard);
    }
}

class FaunaActionFloraWithType extends FaunaActionBase
{
    protected function onGetPlayerFaunaProgress(int $playerId, array $scores)
    {
        $scoreNbFlora = array_shift($scores);
        $scoreCardType = array_shift($scores);
        $countNbFlora = 0;
        foreach ($this->getPlayerTableauCards() as $card) {
            if (!$card->getCardDef()->isFlora()) {
                continue;
            }
            if ($card->getCardDef()->hasCardType($scoreCardType)) {
                $countNbFlora += 1;
            }
        }
        return new FaunaProgress($countNbFlora, $scoreNbFlora);
    }
}

class FaunaActionCardsWithAbilityColor extends FaunaActionBase
{
    protected function onGetPlayerFaunaProgress(int $playerId, array $scores)
    {
        $scoreNbCard = array_shift($scores);
        $scoreColor = array_shift($scores);
        $countNbCard = 0;
        foreach ($this->getPlayerIslandClimateTableauCards() as $card) {
            if ($scoreColor == \EA\AB_COLOR_MULTICOLOR) {
                if ($card->getCardDef()->getAbilityForColor($scoreColor) !== null) {
                    $countNbCard += 1;
                }
            } else {
                if ($card->getCardDef()->getAbilityMatchingColor($scoreColor) !== null) {
                    $countNbCard += 1;
                }
            }
        }
        return new FaunaProgress($countNbCard, $scoreNbCard);
    }
}

class FaunaActionColumns extends FaunaActionBase
{
    protected function onGetPlayerFaunaProgress(int $playerId, array $scores)
    {
        $scoreNbColumn = array_shift($scores);
        $countPerColumn = [];
        foreach ($this->getPlayerTableauCards() as $card) {
            if (!array_key_exists($card->locationX, $countPerColumn)) {
                $countPerColumn[$card->locationX] = 0;
            }
            $countPerColumn[$card->locationX] += 1;
        }
        return new FaunaProgress(count(array_filter($countPerColumn, fn ($c) => $c >= \EA\MAX_TABLEAU_SIZE)), $scoreNbColumn);
    }
}

class FaunaActionRows extends FaunaActionBase
{
    protected function onGetPlayerFaunaProgress(int $playerId, array $scores)
    {
        $scoreNbRow = array_shift($scores);
        $countPerRow = [];
        foreach ($this->getPlayerTableauCards() as $card) {
            if (!array_key_exists($card->locationY, $countPerRow)) {
                $countPerRow[$card->locationY] = 0;
            }
            $countPerRow[$card->locationY] += 1;
        }
        return new FaunaProgress(count(array_filter($countPerRow, fn ($c) => $c >= \EA\MAX_TABLEAU_SIZE)), $scoreNbRow);
    }
}

class FaunaActionDiagonals extends FaunaActionBase
{
    private const DIAGONAL_COUNT = 2;

    protected function onGetPlayerFaunaProgress(int $playerId, array $scores)
    {
        $cardTL = null;
        $cardTR = null;
        $cardBL = null;
        $cardBR = null;
        foreach ($this->getPlayerTableauCards() as $card) {
            if ($cardTL === null || ($card->locationX <= $cardTL->locationX && $card->locationY <= $cardTL->locationY)) {
                $cardTL = $card;
            }
            if ($cardTR === null || ($card->locationX >= $cardTR->locationX && $card->locationY <= $cardTR->locationY)) {
                $cardTR = $card;
            }
            if ($cardBL === null || ($card->locationX <= $cardTL->locationX && $card->locationY >= $cardTL->locationY)) {
                $cardBL = $card;
            }
            if ($cardBR === null || ($card->locationX >= $cardTR->locationX && $card->locationY >= $cardTR->locationY)) {
                $cardBR = $card;
            }
        }
        $diagCount = 0;

        // From Top Left to Bottom Right
        if (
            $cardTL !== null
            && $cardBR !== null
            && abs($cardTL->locationX - $cardBR->locationX) + 1 >= \EA\MAX_TABLEAU_SIZE
            && abs($cardTL->locationY - $cardBR->locationY) + 1 >= \EA\MAX_TABLEAU_SIZE
        ) {
            $middleCount = 0;
            foreach ($this->getPlayerTableauCards() as $card) {
                if ($card->locationX == $cardTL->locationX + 1 && $card->locationY == $cardTL->locationY + 1) {
                    $middleCount += 1;
                } else if ($card->locationX == $cardBR->locationX - 1 && $card->locationY == $cardBR->locationY - 1) {
                    $middleCount += 1;
                }
            }
            if ($middleCount == 2) {
                $diagCount += 1;
            }
        }

        // From Top Right to Bottom Left
        if (
            $cardTR !== null
            && $cardBL !== null
            && abs($cardTR->locationX - $cardBL->locationX) + 1 >= \EA\MAX_TABLEAU_SIZE
            && abs($cardTR->locationY - $cardBL->locationY) + 1 >= \EA\MAX_TABLEAU_SIZE
        ) {
            $middleCount = 0;
            foreach ($this->getPlayerTableauCards() as $card) {
                if ($card->locationX == $cardTR->locationX - 1 && $card->locationY == $cardTR->locationY + 1) {
                    $middleCount += 1;
                } else if ($card->locationX == $cardBL->locationX + 1 && $card->locationY == $cardBL->locationY - 1) {
                    $middleCount += 1;
                }
            }
            if ($middleCount == 2) {
                $diagCount += 1;
            }
        }

        return new FaunaProgress($diagCount, self::DIAGONAL_COUNT);
    }
}

class FaunaActionWithDirections extends FaunaActionBase
{
    protected function onGetPlayerFaunaProgress(int $playerId, array $scores)
    {
        $scoreNbDirection = array_shift($scores);
        $countNbDirection = 0;
        foreach ($this->getPlayerIslandClimateTableauCards() as $card) {
            foreach ($card->getCardDef()->getAllAbilities() as $ability) {
                if ($ability->getDirection() !== null) {
                    $countNbDirection += 1;
                    break;
                }
            }
        }
        return new FaunaProgress($countNbDirection, $scoreNbDirection);
    }
}

class FaunaActionCardsWithLessHabitat extends FaunaActionBase
{
    protected function onGetPlayerFaunaProgress(int $playerId, array $scores)
    {
        $scoreNbCard = array_shift($scores);
        $scoreNbHabitatPerCard = array_shift($scores);
        $countNbCard = 0;
        foreach ($this->getPlayerIslandClimateTableauCards() as $card) {
            if ($card->getCardDef()->habitatCount() <= $scoreNbHabitatPerCard) {
                $countNbCard += 1;
            }
        }
        return new FaunaProgress($countNbCard, $scoreNbCard);
    }
}

class FaunaActionCardsWithMoreHabitat extends FaunaActionBase
{
    protected function onGetPlayerFaunaProgress(int $playerId, array $scores)
    {
        $scoreNbCard = array_shift($scores);
        $scoreNbHabitatPerCard = array_shift($scores);
        $countNbCard = 0;
        foreach ($this->getPlayerIslandClimateTableauCards() as $card) {
            if ($card->getCardDef()->habitatCount() >= $scoreNbHabitatPerCard) {
                $countNbCard += 1;
            }
        }
        return new FaunaProgress($countNbCard, $scoreNbCard);
    }
}

class FaunaActionCardsWithLessScore extends FaunaActionBase
{
    protected function onGetPlayerFaunaProgress(int $playerId, array $scores)
    {
        $scoreNbCard = array_shift($scores);
        $scoreScore = array_shift($scores);
        $countNbCard = 0;
        foreach ($this->getPlayerIslandClimateTableauCards() as $card) {
            if ($card->getCardDef()->score <= $scoreScore) {
                $countNbCard += 1;
            }
        }
        return new FaunaProgress($countNbCard, $scoreNbCard);
    }
}

class FaunaActionCardsWithMoreScore extends FaunaActionBase
{
    protected function onGetPlayerFaunaProgress(int $playerId, array $scores)
    {
        $scoreNbCard = array_shift($scores);
        $scoreScore = array_shift($scores);
        $countNbCard = 0;
        foreach ($this->getPlayerIslandClimateTableauCards() as $card) {
            if ($card->getCardDef()->score >= $scoreScore) {
                $countNbCard += 1;
            }
        }
        return new FaunaProgress($countNbCard, $scoreNbCard);
    }
}

class FaunaActionCardsWithLessCost extends FaunaActionBase
{
    protected function onGetPlayerFaunaProgress(int $playerId, array $scores)
    {
        $scoreNbCard = array_shift($scores);
        $scoreCost = array_shift($scores);
        $countNbCard = 0;
        foreach ($this->getPlayerIslandClimateTableauCards() as $card) {
            $cardDef = $card->getCardDef();
            if ($cardDef->soil !== null && $cardDef->soil <= $scoreCost) {
                $countNbCard += 1;
            }
        }
        return new FaunaProgress($countNbCard, $scoreNbCard);
    }
}

class FaunaActionCardsWithMoreCost extends FaunaActionBase
{
    protected function onGetPlayerFaunaProgress(int $playerId, array $scores)
    {
        $scoreNbCard = array_shift($scores);
        $scoreCost = array_shift($scores);
        $countNbCard = 0;
        foreach ($this->getPlayerIslandClimateTableauCards() as $card) {
            $cardDef = $card->getCardDef();
            if ($cardDef->soil !== null && $cardDef->soil >= $scoreCost) {
                $countNbCard += 1;
            }
        }
        return new FaunaProgress($countNbCard, $scoreNbCard);
    }
}

class FaunaActionCardsSets extends FaunaActionBase
{
    protected function onGetPlayerFaunaProgress(int $playerId, array $scores)
    {
        $scoreNbSet = array_shift($scores);
        $cardMgr = self::getMgr('card');

        $countEvent = count($cardMgr->getPlayerBoardEventCards($playerId));
        $countTerrain = 0;
        foreach ($this->getPlayerIslandClimateTableauCards() as $card) {
            if ($card->getCardDef()->isTerrain()) {
                $countTerrain += 1;
            }
        }
        $countColor = [];
        foreach (\EA\MULTICOLOR_COLORS as $color) {
            $countColor[$color] = 0;
        }

        $cards = array_values($this->getPlayerIslandClimateTableauCards());
        $countCards = $this->findSet($cards, $countColor, $scoreNbSet);
        return new FaunaProgress(min($countEvent, $countTerrain, $countCards), $scoreNbSet);
    }

    private function findSet(array $cards, array $countColor, $scoreNbSet)
    {
        if (count($cards) == 0) {
            $min = $scoreNbSet;
            foreach (\EA\MULTICOLOR_COLORS as $color) {
                $min = min($min, $countColor[$color]);
            }
            return $min;
        }
        $cardDef = $cards[0]->getCardDef();
        $max = 0;
        foreach (\EA\MULTICOLOR_COLORS as $color) {
            if ($cardDef->getAbilityMatchingColor($color) !== null) {
                $countColor[$color] += 1;
                $min = $this->findSet(array_slice($cards, 1), $countColor, $scoreNbSet);
                if ($min >= $scoreNbSet) {
                    return $min;
                }
                $max = max($max, $min);
                $countColor[$color] -= 1;
            }
        }
        return max($max, $this->findSet(array_slice($cards, 1), $countColor, $scoreNbSet));
    }
}

class FaunaActionCardsWithMoreAbility extends FaunaActionBase
{
    protected function onGetPlayerFaunaProgress(int $playerId, array $scores)
    {
        $scoreNbCard = array_shift($scores);
        $scoreNbAbility = array_shift($scores);
        $countNbCard = 0;
        foreach ($this->getPlayerIslandClimateTableauCards() as $card) {
            if (count($card->getCardDef()->getAllAbilities()) >= $scoreNbAbility) {
                $countNbCard += 1;
            }
        }
        return new FaunaProgress($countNbCard, $scoreNbCard);
    }
}

class FaunaActionFloraWithBoldGeography extends FaunaActionBase
{
    protected function onGetPlayerFaunaProgress(int $playerId, array $scores)
    {
        $scoreNbCard = array_shift($scores);
        $countNbCard = 0;
        foreach ($this->getPlayerTableauCards() as $card) {
            if (!$card->getCardDef()->isFlora()) {
                continue;
            }
            if ($card->getCardDef()->isBoldGeography) {
                $countNbCard += 1;
            }
        }
        return new FaunaProgress($countNbCard, $scoreNbCard);
    }
}

class FaunaActionFloraWithItalicColor extends FaunaActionBase
{
    protected function onGetPlayerFaunaProgress(int $playerId, array $scores)
    {
        $scoreNbCard = array_shift($scores);
        $countNbCard = 0;
        foreach ($this->getPlayerTableauCards() as $card) {
            if (!$card->getCardDef()->isFlora()) {
                continue;
            }
            if ($card->getCardDef()->isItalicColor) {
                $countNbCard += 1;
            }
        }
        return new FaunaProgress($countNbCard, $scoreNbCard);
    }
}

class FaunaActionFloraWithUnderlineAnimal extends FaunaActionBase
{
    protected function onGetPlayerFaunaProgress(int $playerId, array $scores)
    {
        $scoreNbCard = array_shift($scores);
        $countNbCard = 0;
        foreach ($this->getPlayerTableauCards() as $card) {
            if (!$card->getCardDef()->isFlora()) {
                continue;
            }
            if ($card->getCardDef()->isUnderlineAnimal) {
                $countNbCard += 1;
            }
        }
        return new FaunaProgress($countNbCard, $scoreNbCard);
    }
}

class FaunaActionFloraWithExactPieceSpot extends FaunaActionBase
{
    protected function onGetPlayerFaunaProgress(int $playerId, array $scores)
    {
        $scoreNbCard = array_shift($scores);
        $scoreNbSpot = array_shift($scores);
        $scorePieceType = array_shift($scores);
        $countNbCard = 0;
        foreach ($this->getPlayerTableauCards() as $card) {
            if (!$card->getCardDef()->isFlora()) {
                continue;
            }
            switch ($scorePieceType) {
                case \EA\ABILITY_SPROUT:
                    if ($card->getCardDef()->sproutMax == $scoreNbSpot) {
                        $countNbCard += 1;
                    }
                    break;
                case \EA\ABILITY_GROWTH:
                    if ($card->getCardDef()->growthMax == $scoreNbSpot) {
                        $countNbCard += 1;
                    }
                    break;
                default:
                    throw new \BgaSystemException("BUG! Unsupported scorePieceType $scorePieceType for fauna");
            }
        }
        return new FaunaProgress($countNbCard, $scoreNbCard);
    }
}

class FaunaActionFloraWithLessPieceSpot extends FaunaActionBase
{
    protected function onGetPlayerFaunaProgress(int $playerId, array $scores)
    {
        $scoreNbCard = array_shift($scores);
        $scoreNbSpot = array_shift($scores);
        $scorePieceType = array_shift($scores);
        $countNbCard = 0;
        foreach ($this->getPlayerTableauCards() as $card) {
            if (!$card->getCardDef()->isFlora()) {
                continue;
            }
            switch ($scorePieceType) {
                case \EA\ABILITY_SPROUT:
                    if ($card->getCardDef()->sproutMax <= $scoreNbSpot) {
                        $countNbCard += 1;
                    }
                    break;
                case \EA\ABILITY_GROWTH:
                    if ($card->getCardDef()->growthMax <= $scoreNbSpot) {
                        $countNbCard += 1;
                    }
                    break;
                default:
                    throw new \BgaSystemException("BUG! Unsupported scorePieceType $scorePieceType for fauna");
            }
        }
        return new FaunaProgress($countNbCard, $scoreNbCard);
    }
}

class FaunaActionFloraWithMorePieceSpot extends FaunaActionBase
{
    protected function onGetPlayerFaunaProgress(int $playerId, array $scores)
    {
        $scoreNbCard = array_shift($scores);
        $scoreNbSpot = array_shift($scores);
        $scorePieceType = array_shift($scores);
        $countNbCard = 0;
        foreach ($this->getPlayerTableauCards() as $card) {
            switch ($scorePieceType) {
                case \EA\ABILITY_SPROUT:
                    if ($card->getCardDef()->sproutMax >= $scoreNbSpot) {
                        $countNbCard += 1;
                    }
                    break;
                case \EA\ABILITY_GROWTH:
                    if ($card->getCardDef()->growthMax >= $scoreNbSpot) {
                        $countNbCard += 1;
                    }
                    break;
                default:
                    throw new \BgaSystemException("BUG! Unsupported scorePieceType $scorePieceType for fauna");
            }
        }
        return new FaunaProgress($countNbCard, $scoreNbCard);
    }
}

class FaunaActionFloraFilledFieces extends FaunaActionBase
{
    protected function onGetPlayerFaunaProgress(int $playerId, array $scores)
    {
        $scoreNbCard = array_shift($scores);
        $countNbCard = 0;
        foreach ($this->getPlayerTableauCards() as $card) {
            $cardDef = $card->getCardDef();
            if (!$cardDef->isFlora()) {
                continue;
            }
            if (
                ($cardDef->sproutMax === null || $cardDef->sproutMax == $card->sproutCount)
                && ($cardDef->growthMax === null || $cardDef->growthMax == $card->growthCount)
            ) {
                $countNbCard += 1;
            }
        }
        return new FaunaProgress($countNbCard, $scoreNbCard);
    }
}

class FaunaActionFloraEmptyFieces extends FaunaActionBase
{
    protected function onGetPlayerFaunaProgress(int $playerId, array $scores)
    {
        $scoreNbCard = array_shift($scores);
        $countNbCard = 0;
        foreach ($this->getPlayerTableauCards() as $card) {
            if (!$card->getCardDef()->isFlora()) {
                continue;
            }
            if ($card->sproutCount == 0 && $card->growthCount == 0) {
                $countNbCard += 1;
            }
        }
        return new FaunaProgress($countNbCard, $scoreNbCard);
    }
}

class FaunaActionCardsWithEvenScore extends FaunaActionBase
{
    protected function onGetPlayerFaunaProgress(int $playerId, array $scores)
    {
        $scoreNbCard = array_shift($scores);
        $countNbCard = 0;
        foreach ($this->getPlayerIslandClimateTableauCards() as $card) {
            if (($card->getCardDef()->score % 2) == 0) {
                $countNbCard += 1;
            }
        }
        return new FaunaProgress($countNbCard, $scoreNbCard);
    }
}

class FaunaActionCardsWithOddScore extends FaunaActionBase
{
    protected function onGetPlayerFaunaProgress(int $playerId, array $scores)
    {
        $scoreNbCard = array_shift($scores);
        $countNbCard = 0;
        foreach ($this->getPlayerIslandClimateTableauCards() as $card) {
            if (($card->getCardDef()->score % 2) == 1) {
                $countNbCard += 1;
            }
        }
        return new FaunaProgress($countNbCard, $scoreNbCard);
    }
}
