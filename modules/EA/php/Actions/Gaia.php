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

namespace EA\Actions\Gaia;

require_once(__DIR__ . '/.././../../BX/php/Action.php');
require_once('Traits.php');

abstract class CompostFromDeckBase extends \BX\Action\BaseActionCommandNoUndo
{
    use \EA\Actions\Traits\CardQueryTrait;

    public function __construct(int $playerId)
    {
        parent::__construct($playerId);
    }

    public function compostFromDeck(\BX\Action\BaseActionCommandNotifier $notifier, int $compostFromDeckCount)
    {
        if ($compostFromDeckCount < 0) {
            throw new \BgaSystemException("BUG! compostFromDeckCount is invalid: {$compostFromDeckCount}");
        }

        $cardMgr = self::getMgr('card');
        $nbDrawnCards = 0;
        for ($i = 0; $i < $compostFromDeckCount; ++$i) {
            $card = $cardMgr->getTopCardFromDeck();
            if ($card === null) {
                // No more cards, cannot draw more cards
                break;
            }
            $nbDrawnCards += 1;
            $card->modifyAction();
            $card->moveToGaiaCompost();
        }
        $notifier->notify(
            NTF_UPDATE_CARD_COUNTS,
            clienttranslate('Gaia composts ${nbDrawnCards} card(s) from the deck'),
            [
                'nbDrawnCards' => $nbDrawnCards,
                'cardCounts' => $cardMgr->getCardCountsUIForPlayerId($this->playerId),
            ]
        );
        $notifier->notifyNoMessage(
            NTF_UPDATE_CARD_COUNTS,
            [
                'cardCounts' => $cardMgr->getCardCountsUIDeckDiscard(),
            ]
        );
        $notifier->notifyNoMessage(
            NTF_UPDATE_GAIA,
            [
                'gaiaCount' => self::getMgr('game_state')->getGaiaCount(),
                'gaiaTableauCards' => $cardMgr->getGaiaTableauCards(),
                'gaiaDiscardCards' => null,
            ]
        );
    }
}

class CompostFromDeck extends CompostFromDeckBase
{
    private $compostFromDeckCount;

    public function __construct(int $playerId, int $compostFromDeckCount)
    {
        parent::__construct($playerId);
        $this->compostFromDeckCount = $compostFromDeckCount;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $this->compostFromDeck($notifier, $this->compostFromDeckCount);
    }
}

class CompostFromDeckSoil extends CompostFromDeckBase
{
    public function __construct(int $playerId)
    {
        parent::__construct($playerId);
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $gameStateMgr = self::getMgr('game_state');

        $divide = intdiv($gameStateMgr->getGaiaSoil(), 10);

        if ($divide > 0) {
            $nbSoil = 10 * $divide;
            $gameStateMgr->modifyLooseGaiaSoil($nbSoil);
            $notifier->notify(
                NTF_UPDATE_GAIA,
                clienttranslate('Gaia looses ${nbSoil} ${soilIcon} to compost cards from deck'),
                [
                    'nbSoil' => $nbSoil,
                    'soilIcon' => clienttranslate('soil'),
                    'gaiaCount' => self::getMgr('game_state')->getGaiaCount(),
                    'gaiaTableauCards' => null,
                    'gaiaDiscardCards' => null,
                ]
            );
            $this->compostFromDeck($notifier, $divide * 5);
        }
    }
}

class PlaceSprout extends \BX\Action\BaseActionCommand
{
    private $nbSprout;
    private $undoCount;

    public function __construct(int $playerId, int $nbSprout)
    {
        parent::__construct($playerId);
        $this->nbSprout = $nbSprout;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->nbSprout < 0) {
            throw new \BgaSystemException("BUG! nbSprout is invalid: {$this->nbSprout}");
        }

        $gameStateMgr = self::getMgr('game_state');

        $this->undoCount = \BX\Meta\deepClone($gameStateMgr->getGaiaCount());

        $gameStateMgr->modifyGaiaSprout($this->nbSprout);
        $notifier->notify(
            NTF_UPDATE_GAIA,
            clienttranslate('Gaia gains ${nbSprout} ${sproutIcon}'),
            [
                'nbSprout' => $this->nbSprout,
                'sproutIcon' => clienttranslate('sprout(s)'),
                'gaiaCount' => $gameStateMgr->getGaiaCount(),
                'gaiaTableauCards' => null,
                'gaiaDiscardCards' => null,
            ]
        );
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->undoCount !== null) {
            $notifier->notifyNoMessage(
                NTF_UPDATE_GAIA,
                [
                    'gaiaCount' => $this->undoCount,
                    'gaiaTableauCards' => null,
                    'gaiaDiscardCards' => null,
                ]
            );
        }
    }
}

class PlaceGrowth extends \BX\Action\BaseActionCommandNoUndo
{
    private $nbGrowth;

    public function __construct(int $playerId, int $nbGrowth)
    {
        parent::__construct($playerId);
        $this->nbGrowth = $nbGrowth;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->nbGrowth < 0) {
            throw new \BgaSystemException("BUG! nbGrowth is invalid: {$this->nbGrowth}");
        }

        $gameStateMgr = self::getMgr('game_state');
        $gameStateMgr->modifyGaiaGrowth($this->nbGrowth);
        $notifier->notify(
            NTF_UPDATE_GAIA,
            clienttranslate('Gaia gains ${nbGrowth} ${growthIcon}'),
            [
                'nbGrowth' => $this->nbGrowth,
                'growthIcon' => clienttranslate('growth'),
                'gaiaCount' => self::getMgr('game_state')->getGaiaCount(),
                'gaiaTableauCards' => null,
                'gaiaDiscardCards' => null,
            ]
        );
    }
}

class GainSoil extends \BX\Action\BaseActionCommandNoUndo
{
    private $nbSoil;

    public function __construct(int $playerId, int $nbSoil)
    {
        parent::__construct($playerId);
        $this->nbSoil = $nbSoil;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->nbSoil < 0) {
            throw new \BgaSystemException("BUG! nbSoil is invalid: {$this->nbSoil}");
        }

        $gameStateMgr = self::getMgr('game_state');
        $gameStateMgr->modifyGaiaSoil($this->nbSoil);
        $notifier->notify(
            NTF_UPDATE_GAIA,
            clienttranslate('Gaia gains ${nbSoil} ${soilIcon}'),
            [
                'nbSoil' => $this->nbSoil,
                'soilIcon' => clienttranslate('soil'),
                'gaiaCount' => self::getMgr('game_state')->getGaiaCount(),
                'gaiaTableauCards' => null,
                'gaiaDiscardCards' => null,
            ]
        );
    }
}

class DrawGaiaCard extends \BX\Action\BaseActionCommandNoUndo
{
    public function __construct(int $playerId)
    {
        parent::__construct($playerId);
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $gameStateMgr = self::getMgr('game_state');
        $cardMgr = self::getMgr('card');
        $card = $cardMgr->getTopCardFromGaiaDeck();
        if ($card === null) {
            if ($gameStateMgr->getGaiaDeckShuffle() == 0) {
                $gameStateMgr->modifyGaiaDeckShuffle();
                $cardMgr->shuffleGaiaDeck();
                $card = $cardMgr->getTopCardFromGaiaDeck();
                $notifier->notify(
                    \BX\Action\NTF_MESSAGE,
                    clienttranslate('Gaia deck is reshuffled'),
                    []
                );
            }
        }
        if ($card === null) {
            $notifier->notify(
                \BX\Action\NTF_MESSAGE,
                clienttranslate('Gaia deck cannot be reshuffled again, the game is ending'),
                []
            );
            return;
        }
        $card->modifyAction();
        $card->moveToGaiaDiscard();
        $notifier->notify(
            NTF_UPDATE_CARDS,
            clienttranslate('Gaia draws a card: ${cardName} ${cardImage}'),
            [
                'cards' => [$card],
                'cardName' => $card->getCardDef()->name,
                'cardImage' => $card->cardId,
                'i18n' => ['cardName'],
            ]
        );
        $notifier->notifyNoMessage(
            NTF_UPDATE_GAIA,
            [
                'gaiaCount' => self::getMgr('game_state')->getGaiaCount(),
                'gaiaTableauCards' => null,
                'gaiaDiscardCards' => $cardMgr->getGaiaDiscardCards(),
            ]
        );
        $notifier->notifyNoMessage(
            NTF_UPDATE_CARD_COUNTS,
            [
                'cardCounts' => $cardMgr->getCardCountsUIForPlayerId($this->playerId),
            ]
        );
        $notifier->notifyNoMessage(
            NTF_UPDATE_CARD_COUNTS,
            [
                'cardCounts' => $cardMgr->getCardCountsUIDeckDiscard(),
            ]
        );
        if ($gameStateMgr->isSoloLastTurn()) {
            $notifier->notify(
                NTF_LAST_ROUND,
                clienttranslate('Last Gaia card was drawn, this is the last turn'),
                [
                    'isLastRound' => true,
                ]
            );
        }
    }
}

class DrawEarthCard extends \BX\Action\BaseActionCommandNoUndo
{
    private $nbCardsToDraw;

    public function __construct(int $playerId, int $nbCardsToDraw)
    {
        parent::__construct($playerId);
        $this->nbCardsToDraw = $nbCardsToDraw;
        if ($nbCardsToDraw < 0) {
            throw new \BgaSystemException("BUG! Invalid nbCardsToDraw: $nbCardsToDraw");
        }
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $cardMgr = self::getMgr('card');
        $nbDrawnCards = 0;
        $drawnCards = [];
        for ($i = 0; $i < $this->nbCardsToDraw; ++$i) {
            $card = $cardMgr->getTopCardFromDeck();
            if ($card === null) {
                // No more cards, cannot draw more cards
                break;
            }
            $nbDrawnCards += 1;
            $card->modifyAction();
            $card->moveToGaiaTableau();
            $drawnCards[] = $card;
        }
        $notifier->notify(
            NTF_UPDATE_CARDS,
            clienttranslate('Gaia draws ${nbDrawnCards} earth cards'),
            [
                'nbDrawnCards' => $nbDrawnCards,
                'cards' => $drawnCards,
            ]
        );
        $notifier->notifyNoMessage(
            NTF_UPDATE_CARD_COUNTS,
            [
                'cardCounts' => $cardMgr->getCardCountsUIDeckDiscard(),
            ]
        );
        $notifier->notifyNoMessage(
            NTF_UPDATE_CARD_COUNTS,
            [
                'cardCounts' => $cardMgr->getCardCountsUIForPlayerId($this->playerId),
            ]
        );
        $notifier->notifyNoMessage(
            NTF_UPDATE_GAIA,
            [
                'gaiaCount' => self::getMgr('game_state')->getGaiaCount(),
                'gaiaTableauCards' => $cardMgr->getGaiaTableauCards(),
                'gaiaDiscardCards' => $cardMgr->getGaiaDiscardCards(),
            ]
        );
    }
}

class GaiaFaunaChoose extends \BX\Action\BaseActionCommand
{
    protected $x;
    protected $y;
    protected $undoToken;
    protected $undoGaiaCount;

    public function __construct(int $playerId, int $x, int $y)
    {
        parent::__construct($playerId);
        $this->x = $x;
        $this->y = $y;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->x != 0 && $this->x != 1) {
            throw new \BgaSystemException("BUG! Invalid x position: {$this->x}");
        }
        if ($this->y != 0 && $this->y != 1) {
            throw new \BgaSystemException("BUG! Invalid y position: {$this->y}");
        }

        $gameStateMgr = self::getMgr('game_state');
        if ($this->y == 0) {
            $gameStateMgr->modifyGaiaLastFaunaLeft();
        } else {
            $gameStateMgr->modifyGaiaLastFaunaRight();
        }
        $leafTokenMgr = self::getMgr('leaf_token');
        $this->leafId = $leafTokenMgr->getLeafIdFromBoardLocation($this->x, $this->y);
        $token = $leafTokenMgr->getLeafTokenByLeafIdAndPlayerId($this->leafId, \EA\GAIA_PLAYER_ID);
        if ($token->isOnFaunaBoard()) {
            throw new \BgaSystemException("BUG! Token is already on fauna board {$this->x} {$this->y}");
        }
        $max = -1;
        foreach ($leafTokenMgr->getFaunaLeafTokenAtFaunaForLocation($this->x, $this->y) as $otherToken) {
            if ($otherToken->locationOrder !== null && $otherToken->locationOrder > $max) {
                $max = $otherToken->locationOrder;
            }
        }

        $this->undoToken = \BX\Meta\deepClone($token);
        $this->undoGaiaCount = \BX\Meta\deepClone(self::getMgr('game_state')->getGaiaCount());
        $token->modifyAction();
        $token->moveToFaunaBoardFauna($this->x, $this->y);
        $token->moveToFaunaBoardFaunaFinalOrder($max + 1);
        $cardMgr = self::getMgr('card');
        $card = $cardMgr->getFaunaCardAtLocation($this->x, $this->y);
        $notifier->notify(
            NTF_UPDATE_LEAF_TOKEN,
            clienttranslate('Gaia claims a Fauna objective: ${cardName} ${cardImage}'),
            [
                'leafToken' => $token->toPlayerUI($this->playerId),
                'playerActiveOrder' => $gameStateMgr->playerIdsWithActiveOrder(),
                'cardName' => $card->getCardDef()->name,
                'cardImage' => $card->cardId,
                'i18n' => ['cardName'],
            ]
        );
        $notifier->notifyNoMessage(
            NTF_UPDATE_GAIA,
            [
                'gaiaCount' => self::getMgr('game_state')->getGaiaCount(),
                'gaiaTableauCards' => null,
                'gaiaDiscardCards' => null,
            ]
        );
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
        if ($this->undoGaiaCount !== null) {
            $notifier->notifyNoMessage(
                NTF_UPDATE_GAIA,
                [
                    'gaiaCount' => $this->undoGaiaCount,
                    'gaiaTableauCards' => null,
                    'gaiaDiscardCards' => null,
                ]
            );
        }
    }
}

class LeafTokenBonus extends \BX\Action\BaseActionCommandNoUndo
{
    public function __construct(int $playerId)
    {
        parent::__construct($playerId);
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $leafTokenMgr = self::getMgr('leaf_token');
        if ($leafTokenMgr->hasFaunaBoardTableauBonus()) {
            return;
        }
        $leafToken = $leafTokenMgr->getTableauBonusLeafTokenForPlayerId(\EA\GAIA_PLAYER_ID);
        $leafToken->modifyAction();
        $leafToken->moveToFaunaBoardTableauBonus();
        $notifier->notify(
            NTF_UPDATE_LEAF_TOKEN,
            clienttranslate('Gaia claims the 4x4 tableau bonus on the fauna board'),
            [
                'leafToken' => $leafToken->toPlayerUI($this->playerId),
            ]
        );
    }
}
