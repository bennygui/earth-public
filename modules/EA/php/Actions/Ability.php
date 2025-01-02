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

namespace EA\Actions\Ability;

require_once(__DIR__ . '/.././../../BX/php/Action.php');
require_once('Traits.php');

class GainDrawCardFromDeck extends \BX\Action\BaseActionCommandNoUndo
{
    private $nbCardsToDraw;
    private $handChoosing;
    private $isFromEvent;

    public function __construct(int $playerId, int $nbCardsToDraw, bool $handChoosing = false, bool $isFromEvent = false)
    {
        parent::__construct($playerId);
        $this->nbCardsToDraw = $nbCardsToDraw;
        $this->handChoosing = $handChoosing;
        $this->isFromEvent = $isFromEvent;
        if ($nbCardsToDraw < 0) {
            throw new \BgaSystemException("BUG! Invalid nbCardsToDraw: $nbCardsToDraw");
        }
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $cardMgr = self::getMgr('card');
        $handCardIdsBefore = array_keys($cardMgr->getPlayerHandCards($this->playerId));
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
            $card->moveToPlayerHand($this->playerId, $this->handChoosing);
            $drawnCards[] = $card;
        }
        if (isGameSolo() && !$this->isFromEvent) {
            $gameStateMgr = self::getMgr('game_state');
            $gameStateMgr->modifySoloPlayerGainedCard($this->nbCardsToDraw);
        }
        self::getMgr('player_state')->incStatNbCardsDrawn($this->playerId, $this->handChoosing ? 1 : $nbDrawnCards);
        $handCardIdsAfter = array_keys($cardMgr->getPlayerHandCards($this->playerId));
        $notifier->notifyPrivateNoMessage(
            NTF_UPDATE_CARDS,
            [
                'cards' => $drawnCards,
                'handCardIdsBefore' => $handCardIdsBefore,
                'handCardIdsAfter' => $handCardIdsAfter,
            ]
        );
        $notifier->notify(
            NTF_UPDATE_CARD_COUNTS,
            clienttranslate('${player_name} draws ${nbDrawnCards} cards'),
            [
                'nbDrawnCards' => count($drawnCards),
                'cardCounts' => $cardMgr->getCardCountsUIForPlayerId($this->playerId),
            ]
        );
        $notifier->notifyNoMessage(
            NTF_UPDATE_CARD_COUNTS,
            [
                'cardCounts' => $cardMgr->getCardCountsUIDeckDiscard(),
            ]
        );
    }
}

class GainSoil extends \BX\Action\BaseActionCommand
{
    private $nbSoil;
    private $fromCardId;
    private $fromMainActionId;
    private $nbSoilBefore;

    public function __construct(int $playerId, int $nbSoil, ?int $fromCardId, ?int $fromMainActionId = null)
    {
        parent::__construct($playerId);
        $this->nbSoil = $nbSoil;
        $this->fromCardId = $fromCardId;
        $this->fromMainActionId = $fromMainActionId;
        if ($nbSoil < 0) {
            throw new \BgaSystemException("BUG! Invalid nbSoil: $nbSoil");
        }
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        $this->nbSoilBefore = $ps->soilCount;
        $ps->modifyAction();
        $ps->addSoil($this->nbSoil);
        if (isGameSolo()) {
            $cardMgr = self::getMgr('card');
            $gameStateMgr = self::getMgr('game_state');
            $fromCardDef = ($this->fromCardId === null ? null : $cardMgr->getCardById($this->fromCardId)->getCardDef());
            if ($fromCardDef === null || !$fromCardDef->isEvent()) {
                $gameStateMgr->modifySoloPlayerGainedSoil($this->nbSoil);
            }
        }
        self::getMgr('player_state')->incStatNbSoilGained($this->playerId, $this->nbSoil);
        $notifier->notify(
            NTF_PLAYER_GAIN_SOIL,
            clienttranslate('${player_name} gains ${gainSoilCount} ${soilIcon}'),
            [
                'gainSoilCount' => $this->nbSoil,
                'totalSoilCount' => $ps->soilCount,
                'soilIcon' => clienttranslate('soil'),
                'fromCardId' => $this->fromCardId,
                'fromMainActionId' => $this->fromMainActionId,
            ]
        );
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $notifier->notifyNoMessage(
            NTF_PLAYER_PAY_SOIL,
            [
                'paySoilCount' => $this->nbSoil,
                'totalSoilCount' => $this->nbSoilBefore,
                'toCardId' => null,
            ]
        );
    }
}

class PaySoil extends \BX\Action\BaseActionCommand
{
    private $nbSoil;
    private $toCardId;
    private $nbSoilBefore;

    public function __construct(int $playerId, int $nbSoil, int $toCardId)
    {
        parent::__construct($playerId);
        $this->nbSoil = $nbSoil;
        $this->toCardId = $toCardId;
        if ($nbSoil < 0) {
            throw new \BgaSystemException("BUG! Invalid nbSoil: $nbSoil");
        }
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        $this->nbSoilBefore = $ps->soilCount;
        $ps->modifyAction();
        $ps->removeSoil($this->nbSoil);
        $notifier->notify(
            NTF_PLAYER_PAY_SOIL,
            clienttranslate('${player_name} pay ${paySoilCount} ${soilIcon}'),
            [
                'paySoilCount' => $this->nbSoil,
                'totalSoilCount' => $ps->soilCount,
                'soilIcon' => clienttranslate('soil'),
                'toCardId' => $this->toCardId,
            ]
        );
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $notifier->notifyNoMessage(
            NTF_PLAYER_GAIN_SOIL,
            [
                'gainSoilCount' => $this->nbSoil,
                'totalSoilCount' => $this->nbSoilBefore,
                'fromCardId' => $this->toCardId,
            ]
        );
    }
}

abstract class CompostFromHandBase extends \BX\Action\BaseActionCommand
{
    use \EA\Actions\Traits\CardQueryTrait;

    protected $cardIds;
    protected $undoCards;
    protected $undoCardCounts;

    public function __construct(int $playerId, array $cardIds)
    {
        parent::__construct($playerId);
        $this->cardIds = $cardIds;
    }

    abstract protected function validateCardCount(\BX\Action\BaseActionCommandNotifier $notifier, int $cardCount);

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if (!$this->validateCardCount($notifier, count($this->cardIds))) {
            return;
        }

        $cardMgr = self::getMgr('card');
        $this->undoCards = [];
        $this->undoCardCounts = \BX\Meta\deepClone($cardMgr->getCardCountsUIForPlayerId($this->playerId));

        $compostCards = [];
        foreach ($this->cardIds as $cardId) {
            $card = $this->cardFromHand($cardId);
            $this->undoCards[] = \BX\Meta\deepClone($card);
            $card->modifyAction();
            $card->moveToPlayerCompost($this->playerId);
            $compostCards[] = $card;
        }
        self::getMgr('player_state')->incStatNbCardsComposted($this->playerId, count($this->cardIds));
        $notifier->notifyPrivateNoMessage(NTF_UPDATE_CARDS, ['cards' => $compostCards]);
        $notifier->notify(
            NTF_UPDATE_CARD_COUNTS,
            clienttranslate('${player_name} composts ${compostFromHandCount} cards from their hand'),
            [
                'compostFromHandCount' => count($this->cardIds),
                'cardCounts' => $cardMgr->getCardCountsUIForPlayerId($this->playerId),
            ]
        );
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->undoCards !== null) {
            $notifier->notifyPrivateNoMessage(NTF_UPDATE_CARDS, ['cards' => $this->undoCards]);
        }
        if ($this->undoCardCounts !== null) {
            $notifier->notifyNoMessage(NTF_UPDATE_CARD_COUNTS, ['cardCounts' => $this->undoCardCounts]);
        }
    }
}

class ExactCompostFromHand extends CompostFromHandBase
{
    private $compostFromHandCount;

    public function __construct(int $playerId, int $compostFromHandCount, array $cardIds)
    {
        parent::__construct($playerId, $cardIds);
        $this->compostFromHandCount = $compostFromHandCount;
    }

    protected function validateCardCount(\BX\Action\BaseActionCommandNotifier $notifier, int $cardCount)
    {
        if ($cardCount != $this->compostFromHandCount) {
            throw new \BgaUserException($notifier->_('You must compost the exact number of cards from your hand'));
        }
        return true;
    }
}

class PlaceCompostFromHand extends CompostFromHandBase
{
    public function __construct(int $playerId, array $cardIds)
    {
        parent::__construct($playerId, $cardIds);
    }

    protected function validateCardCount(\BX\Action\BaseActionCommandNotifier $notifier, int $cardCount)
    {
        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        if (count($this->cardIds) > $ps->gainedCompostFromHand) {
            throw new \BgaUserException($notifier->_('You must compost less cards from your hand'));
        }
        if ($ps->gainedCompostFromHand == 0) {
            return false;
        }
        $ps->modifyAction();
        $ps->clearCompostFromHand();
        return true;
    }
}

class AnyCompostFromHand extends CompostFromHandBase
{
    public function __construct(int $playerId, array $cardIds)
    {
        parent::__construct($playerId, $cardIds);
    }

    protected function validateCardCount(\BX\Action\BaseActionCommandNotifier $notifier, int $cardCount)
    {
        return true;
    }

    public function nbPayed()
    {
        return count($this->cardIds);
    }
}

class CompostFromTableau extends \BX\Action\BaseActionCommand
{
    protected $cardId;
    protected $undoCard;
    protected $undoCardCounts;

    public function __construct(int $playerId, int $cardId)
    {
        parent::__construct($playerId);
        $this->cardId = $cardId;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $cardMgr = self::getMgr('card');
        $card = $cardMgr->getCardById($this->cardId);
        $this->undoCard = \BX\Meta\deepClone($card);
        $this->undoCardCounts = \BX\Meta\deepClone($cardMgr->getCardCountsUIForPlayerId($this->playerId));

        $card->modifyAction();
        $card->moveToPlayerCompost($this->playerId);
        self::getMgr('player_state')->incStatNbCardsComposted($this->playerId, 1);
        $notifier->notifyPrivateNoMessage(NTF_UPDATE_CARDS, ['cards' => [$card]]);
        $notifier->notify(
            NTF_UPDATE_CARD_COUNTS,
            clienttranslate('${player_name} composts the card that was planted at this position in their tableau'),
            [
                'cardCounts' => $cardMgr->getCardCountsUIForPlayerId($this->playerId),
            ]
        );
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->undoCard !== null) {
            $notifier->notifyPrivateNoMessage(NTF_UPDATE_CARDS, ['cards' => [$this->undoCard]]);
        }
        if ($this->undoCardCounts !== null) {
            $notifier->notifyNoMessage(NTF_UPDATE_CARD_COUNTS, ['cardCounts' => $this->undoCardCounts]);
        }
    }
}

class CompostFromDeck extends \BX\Action\BaseActionCommandNoUndo
{
    use \EA\Actions\Traits\CardQueryTrait;

    private $compostFromDeckCount;

    public function __construct(int $playerId, int $compostFromDeckCount)
    {
        parent::__construct($playerId);
        $this->compostFromDeckCount = $compostFromDeckCount;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->compostFromDeckCount < 0) {
            throw new \BgaSystemException("BUG! compostFromDeckCount is invalid: {$this->compostFromDeckCount}");
        }

        $cardMgr = self::getMgr('card');
        $nbDrawnCards = 0;
        for ($i = 0; $i < $this->compostFromDeckCount; ++$i) {
            $card = $cardMgr->getTopCardFromDeck();
            if ($card === null) {
                // No more cards, cannot draw more cards
                break;
            }
            $nbDrawnCards += 1;
            $card->modifyAction();
            $card->moveToPlayerCompost($this->playerId);
        }
        self::getMgr('player_state')->incStatNbCardsComposted($this->playerId, $nbDrawnCards);
        $notifier->notify(
            NTF_UPDATE_CARD_COUNTS,
            clienttranslate('${player_name} composts ${nbDrawnCards} card(s) from the deck'),
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
            NTF_MOVE_COMPOST_FROM_DECK,
            [
                'compostFromDeckCount' => $nbDrawnCards,
            ]
        );
    }
}

class GainSprout extends \BX\Action\BaseActionCommand
{
    private $nbSprout;

    public function __construct(int $playerId, int $nbSprout)
    {
        parent::__construct($playerId);
        $this->nbSprout = $nbSprout;
        if ($nbSprout < 0) {
            throw new \BgaSystemException("BUG! Invalid nbSprout: $nbSprout");
        }
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        $ps->modifyAction();
        $ps->setSprout($this->nbSprout);
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
    }
}

class GainGrowth extends \BX\Action\BaseActionCommand
{
    private $nbGrowth;

    public function __construct(int $playerId, int $nbGrowth)
    {
        parent::__construct($playerId);
        $this->nbGrowth = $nbGrowth;
        if ($nbGrowth < 0) {
            throw new \BgaSystemException("BUG! Invalid nbGrowth: $nbGrowth");
        }
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        $ps->modifyAction();
        $ps->setGrowth($this->nbGrowth);
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
    }
}

class GainCompostFromHand extends \BX\Action\BaseActionCommand
{
    private $nbCard;

    public function __construct(int $playerId, int $nbCard)
    {
        parent::__construct($playerId);
        $this->nbCard = $nbCard;
        if ($nbCard < 0) {
            throw new \BgaSystemException("BUG! Invalid nbCard: $nbCard");
        }
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        $ps->modifyAction();
        $ps->setCompostFromHand($this->nbCard);
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
    }
}

class GainGainedCardIdList extends \BX\Action\BaseActionCommand
{
    private $cardIds;
    private $isDivided;

    public function __construct(int $playerId, array $cardIds, bool $isDivided)
    {
        parent::__construct($playerId);
        $this->cardIds = $cardIds;
        $this->isDivided = $isDivided;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        $ps->modifyAction();
        $ps->setGainedCardIdList($this->cardIds, $this->isDivided);
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
    }
}

class PlaceSprout extends \BX\Action\BaseActionCommand
{
    private $placedSproutList;
    private $undoTableau;
    private $undoPlayerExchange;
    private $fromEndTurnPlaceSprout;

    public function __construct(int $playerId, array $placedSproutList, bool $fromEndTurnPlaceSprout = false)
    {
        parent::__construct($playerId);
        $this->placedSproutList = $placedSproutList;
        if (count($placedSproutList) % 2 != 0) {
            throw new \BgaSystemException('BUG! placedSproutList is not even');
        }
        $this->fromEndTurnPlaceSprout = $fromEndTurnPlaceSprout;
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->fromEndTurnPlaceSprout === null) {
            $this->fromEndTurnPlaceSprout = false;
        }

        $cardMgr = self::getMgr('card');
        $this->undoTableau = \BX\Meta\deepClone($cardMgr->getPlayerTableauCards($this->playerId, $this->playerId));

        $playerExchangeMgr = self::getMgr('player_exchange');
        $pe = $playerExchangeMgr->getBySameFromToPlayerId($this->playerId);
        $this->undoPlayerExchange = \BX\Meta\deepClone($pe);

        $nbPlacedSprout = 0;
        $placedSprout = [];
        for ($i = 0; $i < count($this->placedSproutList); $i += 2) {
            $placedSprout[$this->placedSproutList[$i]] = $this->placedSproutList[$i + 1];
            $nbPlacedSprout += $this->placedSproutList[$i + 1];
        }
        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        if ($nbPlacedSprout > $ps->gainedSprout) {
            throw new \BgaUserException($notifier->_('You must place less sprouts'));
        }

        $gainedCardIdList = $ps->getGainedCardIdList();
        $isGainedCardIdListDivided = $ps->isGainedCardIdListDivided();

        $keepSprout = ($ps->gainedSprout - $nbPlacedSprout);
        if ($this->fromEndTurnPlaceSprout) {
            $keepSprout = 0;
            $pe->modifyAction();
            $pe->sproutTake += $nbPlacedSprout;
        } else {
            if (!gameHasExpansionAbundance() || $gainedCardIdList !== null || $isGainedCardIdListDivided) {
                $keepSprout = 0;
            }
            if ($keepSprout != 0) {
                $pe->modifyAction();
                $pe->sproutGive += $keepSprout;
            }
        }

        if ($ps->gainedSprout == 0) {
            $ps->modifyAction();
            $ps->clearSprout();
            $ps->clearGainedCardIdList();
            return;
        }

        $maxGainedPerCard = 0;
        if ($gainedCardIdList === null) {
            $maxGainedPerCard = null;
        } else if (count($gainedCardIdList) > 0) {
            $maxGainedPerCard = floor($ps->gainedSprout / count($gainedCardIdList));
        }

        $ps->modifyAction();
        $ps->clearSprout();
        $ps->clearGainedCardIdList();

        foreach ($cardMgr->getPlayerTableauCountSprout($this->playerId) as $count) {
            if (!\array_key_exists($count->cardId, $placedSprout)) {
                continue;
            }
            if ($gainedCardIdList !== null) {
                if ($isGainedCardIdListDivided && $placedSprout[$count->cardId] > $maxGainedPerCard) {
                    throw new \BgaSystemException("BUG! Placed too many sprouts per card: max is $maxGainedPerCard for {$count->cardId}");
                }
                if (array_search($count->cardId, $gainedCardIdList) === false) {
                    throw new \BgaSystemException("BUG! Placed sprouts per card on invalid cardId {$count->cardId}");
                }
            }
            if ($placedSprout[$count->cardId] < 0) {
                throw new \BgaSystemException('BUG! placedSprout is negative');
            }
            if ($placedSprout[$count->cardId] + $count->count > $count->max) {
                throw new \BgaSystemException("BUG! Placed too many sprouts on cardId {$count->cardId}");
            }
            $card = $cardMgr->getCardById($count->cardId);
            $card->modifyAction();
            $card->addSprout($placedSprout[$count->cardId]);
            unset($placedSprout[$count->cardId]);
        }
        if (count($placedSprout) > 0) {
            throw new \BgaSystemException('BUG! placedSprout on invalid cards');
        }
        self::getMgr('player_state')->incStatNbSproutsPlaced($this->playerId, $nbPlacedSprout);
        $notifier->notify(
            NTF_UPDATE_PLAYER_TABLEAU,
            clienttranslate('${player_name} places ${nbPlacedSprout} ${sproutIcon} in their tableau'),
            [
                'nbPlacedSprout' => $nbPlacedSprout,
                'sproutIcon' => clienttranslate('sprout(s)'),
                'tableauCards' => \EA\cardsToCompactUI($cardMgr->getPlayerTableauCards($this->playerId, null)),
            ]
        );
        $notifier->notifyPrivateNoMessage(
            NTF_UPDATE_PLAYER_TABLEAU,
            [
                'tableauCards' => \EA\cardsToCompactUI($cardMgr->getPlayerTableauCards($this->playerId, $this->playerId)),
            ]
        );
        if ($keepSprout > 0) {
            $notifier->notify(
                NTF_UPDATE_PLAYER_EXCHANGE,
                clienttranslate('${player_name} places keeps ${sproutCount} on their player board'),
                [
                    'fromPlayerId' => $this->playerId,
                    'toPlayerId' => $this->playerId,
                    'sproutCount' => $keepSprout,
                    'playerExchange' => $pe,
                ]
            );
        } else if ($this->fromEndTurnPlaceSprout) {
            $notifier->notifyNoMessage(
                NTF_UPDATE_PLAYER_EXCHANGE,
                [
                    'playerExchange' => $pe,
                ]
            );
        }
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->undoTableau !== null) {
            $notifier->notifyNoMessage(NTF_UPDATE_PLAYER_TABLEAU, ['tableauCards' => \EA\cardsToCompactUI($this->undoTableau)]);
        }
        if ($this->undoPlayerExchange !== null) {
            $notifier->notifyNoMessage(NTF_UPDATE_PLAYER_EXCHANGE, ['playerExchange' => $this->undoPlayerExchange]);
        }
    }
}


class PlaceGrowth extends \BX\Action\BaseActionCommand
{
    private $placedGrowthList;
    private $undoTableau;

    public function __construct(int $playerId, array $placedGrowthList)
    {
        parent::__construct($playerId);
        $this->placedGrowthList = $placedGrowthList;
        if (count($placedGrowthList) % 2 != 0) {
            throw new \BgaSystemException('BUG! placedGrowthList is not even');
        }
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $cardMgr = self::getMgr('card');
        $this->undoTableau = \BX\META\deepClone($cardMgr->getPlayerTableauCards($this->playerId, $this->playerId));

        $nbPlacedGrowth = 0;
        $placedGrowth = [];
        for ($i = 0; $i < count($this->placedGrowthList); $i += 2) {
            $placedGrowth[$this->placedGrowthList[$i]] = $this->placedGrowthList[$i + 1];
            $nbPlacedGrowth += $this->placedGrowthList[$i + 1];
        }
        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        if ($nbPlacedGrowth > $ps->gainedGrowth) {
            throw new \BgaUserException($notifier->_('You must place less growth'));
        }
        if ($ps->gainedGrowth == 0) {
            $ps->modifyAction();
            $ps->clearGrowth();
            $ps->clearGainedCardIdList();
            return;
        }

        $gainedCardIdList = $ps->getGainedCardIdList();
        $maxGainedPerCard = $gainedCardIdList === null ? null : floor($ps->gainedGrowth / count($gainedCardIdList));
        $isGainedCardIdListDivided = $ps->isGainedCardIdListDivided();

        $ps->modifyAction();
        $ps->clearGrowth();
        $ps->clearGainedCardIdList();

        foreach ($cardMgr->getPlayerTableauCountGrowth($this->playerId) as $count) {
            if (!\array_key_exists($count->cardId, $placedGrowth)) {
                continue;
            }
            if ($gainedCardIdList !== null) {
                if ($isGainedCardIdListDivided && $placedGrowth[$count->cardId] > $maxGainedPerCard) {
                    throw new \BgaSystemException("BUG! Placed too many growth per card: max is $maxGainedPerCard for {$count->cardId}");
                }
                if (array_search($count->cardId, $gainedCardIdList) === false) {
                    throw new \BgaSystemException("BUG! Placed growth per card on invalid cardId {$count->cardId}");
                }
            }
            if ($placedGrowth[$count->cardId] < 0) {
                throw new \BgaSystemException('BUG! placedGrowth is negative');
            }
            if ($placedGrowth[$count->cardId] + $count->count > $count->max) {
                throw new \BgaSystemException("BUG! Placed too many growths on cardId {$count->cardId}");
            }
            $card = $cardMgr->getCardById($count->cardId);
            $card->modifyAction();
            $card->addGrowth($placedGrowth[$count->cardId]);
            unset($placedGrowth[$count->cardId]);
        }
        if (count($placedGrowth) > 0) {
            throw new \BgaSystemException('BUG! placedGrowth on invalid cards');
        }
        self::getMgr('player_state')->incStatNbGrowthPlaced($this->playerId, $nbPlacedGrowth);
        $notifier->notify(
            NTF_UPDATE_PLAYER_TABLEAU,
            clienttranslate('${player_name} places ${nbPlacedGrowth} ${growthIcon} in their tableau'),
            [
                'nbPlacedGrowth' => $nbPlacedGrowth,
                'growthIcon' => clienttranslate('growth(s)'),
                'tableauCards' => \EA\cardsToCompactUI($cardMgr->getPlayerTableauCards($this->playerId, null)),
            ]
        );
        $notifier->notifyPrivateNoMessage(
            NTF_UPDATE_PLAYER_TABLEAU,
            [
                'tableauCards' => \EA\cardsToCompactUI($cardMgr->getPlayerTableauCards($this->playerId, $this->playerId)),
            ]
        );
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->undoTableau !== null) {
            $notifier->notifyNoMessage(NTF_UPDATE_PLAYER_TABLEAU, ['tableauCards' => \EA\cardsToCompactUI($this->undoTableau)]);
        }
    }
}

class CompostDestroy extends \BX\Action\BaseActionCommandNoUndo
{
    private $nbCard;

    public function __construct(int $playerId, int $nbCard)
    {
        parent::__construct($playerId);
        $this->nbCard = $nbCard;
        if ($nbCard <= 0) {
            throw new \BgaSystemException('BUG! nbCard in CompostDestroy must be more than 0');
        }
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $cardMgr = self::getMgr('card');
        $cards = array_values($cardMgr->getPlayerCompostCards($this->playerId));
        usort($cards, fn ($c1, $c2) => $c2->locationOrder - $c1->locationOrder);
        if ($this->nbCard > count($cards)) {
            throw new \BgaUserException($notifier->_('You do not have enough cards to discard in your compost'));
        }
        for ($i = 0; $i < $this->nbCard; ++$i) {
            $cards[$i]->modifyAction();
            $cards[$i]->moveToDiscard();
        }
        self::getMgr('player_state')->incStatNbCardsPaid($this->playerId, $this->nbCard);
        $notifier->notify(
            NTF_DESTROY_COMPOST,
            clienttranslate('${player_name} discards ${nbCard} cards from their compost'),
            [
                'nbCard' => $this->nbCard,
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
    }
}

class PaySprout extends \BX\Action\BaseActionCommand
{
    private $ability;
    private $payedSproutList;
    private $undoTableau;
    private $nbPayed;

    public function __construct(int $playerId, ?\EA\Ability $ability, array $payedSproutList)
    {
        parent::__construct($playerId);
        $this->ability = $ability;
        $this->payedSproutList = $payedSproutList;
        $this->nbPayed = 0;
        if (count($payedSproutList) % 2 != 0) {
            throw new \BgaSystemException('BUG! payedSproutList is not even');
        }
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $cardMgr = self::getMgr('card');
        $this->undoTableau = \BX\META\deepClone($cardMgr->getPlayerTableauCards($this->playerId, $this->playerId));

        $nbPayedSprout = 0;
        $payedSprout = [];
        for ($i = 0; $i < count($this->payedSproutList); $i += 2) {
            $payedSprout[$this->payedSproutList[$i]] = $this->payedSproutList[$i + 1];
            $nbPayedSprout += $this->payedSproutList[$i + 1];
        }
        if ($this->ability !== null) {
            $hasPayment = false;
            $this->ability->foreachPayment(function ($abilityId, $count) use ($notifier, $nbPayedSprout, &$hasPayment) {
                if ($abilityId == \EA\ABILITY_SPROUT) {
                    $hasPayment = true;
                    if ($nbPayedSprout != $count) {
                        throw new \BgaUserException($notifier->_('You did not paid enough sprouts'));
                    }
                }
            });
            if (!$hasPayment) {
                return;
            }
        }
        foreach ($cardMgr->getPlayerTableauCountSprout($this->playerId) as $count) {
            if (!\array_key_exists($count->cardId, $payedSprout)) {
                continue;
            }
            if ($payedSprout[$count->cardId] < 0) {
                throw new \BgaSystemException('BUG! payedSprout is negative');
            }
            if ($count->count - $payedSprout[$count->cardId] < 0) {
                throw new \BgaSystemException("BUG! Payed too many sprouts on cardId {$count->cardId}");
            }
            $card = $cardMgr->getCardById($count->cardId);
            $card->modifyAction();
            $card->removeSprout($payedSprout[$count->cardId]);
            unset($payedSprout[$count->cardId]);
        }
        if (count($payedSprout) > 0) {
            throw new \BgaSystemException('BUG! payedSprout on invalid cards');
        }
        $this->nbPayed = $nbPayedSprout;
        self::getMgr('player_state')->incStatNbSproutsPaid($this->playerId, $nbPayedSprout);
        $notifier->notify(
            NTF_UPDATE_PLAYER_TABLEAU,
            clienttranslate('${player_name} pays ${nbPayedSprout} ${sproutIcon} from their tableau'),
            [
                'nbPayedSprout' => $nbPayedSprout,
                'sproutIcon' => clienttranslate('sprout(s)'),
                'tableauCards' => \EA\cardsToCompactUI($cardMgr->getPlayerTableauCards($this->playerId, null)),
            ]
        );
        $notifier->notifyPrivateNoMessage(
            NTF_UPDATE_PLAYER_TABLEAU,
            [
                'tableauCards' => \EA\cardsToCompactUI($cardMgr->getPlayerTableauCards($this->playerId, $this->playerId)),
            ]
        );
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->undoTableau !== null) {
            $notifier->notifyNoMessage(NTF_UPDATE_PLAYER_TABLEAU, ['tableauCards' => \EA\cardsToCompactUI($this->undoTableau)]);
        }
    }

    public function nbPayed()
    {
        return $this->nbPayed;
    }
}

class PayGrowth extends \BX\Action\BaseActionCommand
{
    private $ability;
    private $payedGrowthList;
    private $undoTableau;
    private $nbPayed;

    public function __construct(int $playerId, ?\EA\Ability $ability, array $payedGrowthList)
    {
        parent::__construct($playerId);
        $this->ability = $ability;
        $this->payedGrowthList = $payedGrowthList;
        $this->nbPayed = 0;
        if (count($payedGrowthList) % 2 != 0) {
            throw new \BgaSystemException('BUG! payedGrowthList is not even');
        }
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $cardMgr = self::getMgr('card');
        $this->undoTableau = \BX\META\deepClone($cardMgr->getPlayerTableauCards($this->playerId, $this->playerId));

        $nbPayedGrowth = 0;
        $payedGrowth = [];
        for ($i = 0; $i < count($this->payedGrowthList); $i += 2) {
            $payedGrowth[$this->payedGrowthList[$i]] = $this->payedGrowthList[$i + 1];
            $nbPayedGrowth += $this->payedGrowthList[$i + 1];
        }
        if ($this->ability !== null) {
            $hasPayment = false;
            $this->ability->foreachPayment(function ($abilityId, $count) use ($notifier, $nbPayedGrowth, &$hasPayment) {
                if ($abilityId == \EA\ABILITY_GROWTH) {
                    $hasPayment = true;
                    if ($nbPayedGrowth != $count) {
                        throw new \BgaUserException($notifier->_('You did not paid enough growth'));
                    }
                }
            });
            if (!$hasPayment) {
                return;
            }
        }
        foreach ($cardMgr->getPlayerTableauCountGrowth($this->playerId) as $count) {
            if (!\array_key_exists($count->cardId, $payedGrowth)) {
                continue;
            }
            if ($payedGrowth[$count->cardId] < 0) {
                throw new \BgaSystemException('BUG! payedGrowth is negative');
            }
            if ($count->count - $payedGrowth[$count->cardId] < 0) {
                throw new \BgaSystemException("BUG! Payed too many growth on cardId {$count->cardId}");
            }
            $card = $cardMgr->getCardById($count->cardId);
            $card->modifyAction();
            $card->removeGrowth($payedGrowth[$count->cardId]);
            unset($payedGrowth[$count->cardId]);
        }
        if (count($payedGrowth) > 0) {
            throw new \BgaSystemException('BUG! payedGrowth on invalid cards');
        }
        $this->nbPayed = $nbPayedGrowth;
        self::getMgr('player_state')->incStatNbGrowthPaid($this->playerId, $nbPayedGrowth);
        $notifier->notify(
            NTF_UPDATE_PLAYER_TABLEAU,
            clienttranslate('${player_name} pays ${nbPayedGrowth} ${growthIcon} from their tableau'),
            [
                'nbPayedGrowth' => $nbPayedGrowth,
                'growthIcon' => clienttranslate('growth(s)'),
                'tableauCards' => \EA\cardsToCompactUI($cardMgr->getPlayerTableauCards($this->playerId, null)),
            ]
        );
        $notifier->notifyPrivateNoMessage(
            NTF_UPDATE_PLAYER_TABLEAU,
            [
                'tableauCards' => \EA\cardsToCompactUI($cardMgr->getPlayerTableauCards($this->playerId, $this->playerId)),
            ]
        );
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->undoTableau !== null) {
            $notifier->notifyNoMessage(NTF_UPDATE_PLAYER_TABLEAU, ['tableauCards' => \EA\cardsToCompactUI($this->undoTableau)]);
        }
    }

    public function nbPayed()
    {
        return $this->nbPayed;
    }
}

class GainDrawCardFromCompost extends \BX\Action\BaseActionCommandNoUndo
{
    private $nbCardsToDraw;
    private $handChoosing;

    public function __construct(int $playerId, int $nbCardsToDraw = 3, bool $handChoosing = true)
    {
        parent::__construct($playerId);
        $this->nbCardsToDraw = $nbCardsToDraw;
        $this->handChoosing = $handChoosing;
        if ($nbCardsToDraw <= 0) {
            throw new \BgaSystemException("BUG! Invalid nbCardsToDraw: $nbCardsToDraw");
        }
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $cardMgr = self::getMgr('card');
        $nbDrawnCards = 0;
        $drawnCards = [];
        for ($i = 0; $i < $this->nbCardsToDraw; ++$i) {
            $card = $cardMgr->getTopCardFromPlayerCompost($this->playerId);
            if ($card === null) {
                // No more cards, cannot draw more cards
                break;
            }
            $nbDrawnCards += 1;
            $card->modifyAction();
            $card->moveToPlayerHand($this->playerId, $this->handChoosing);
            $drawnCards[] = $card;
        }
        if ($nbDrawnCards <= 0) {
            throw new \BgaUserException($notifier->_('No cards to draw in compost'));
        }
        self::getMgr('player_state')->incStatNbCardsDrawn($this->playerId, $nbDrawnCards);
        $notifier->notifyPrivateNoMessage(
            NTF_UPDATE_CARDS,
            [
                'cards' => $drawnCards,
            ]
        );
        $notifier->notify(
            NTF_UPDATE_CARD_COUNTS,
            clienttranslate('${player_name} draws ${nbDrawnCards} cards from their compost'),
            [
                'nbDrawnCards' => count($drawnCards),
                'cardCounts' => $cardMgr->getCardCountsUIForPlayerId($this->playerId),
            ]
        );
    }
}

class GainSeed extends \BX\Action\BaseActionCommand
{
    private $nbSeed;
    private $fromCardId;
    private $nbSeedBefore;

    public function __construct(int $playerId, int $nbSeed, ?int $fromCardId = null)
    {
        parent::__construct($playerId);
        $this->nbSeed = $nbSeed;
        $this->fromCardId = $fromCardId;
        if ($nbSeed < 0) {
            throw new \BgaSystemException("BUG! Invalid nbSeed: $nbSeed");
        }
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        $this->nbSeedBefore = $ps->seedCount;
        $ps->modifyAction();
        $ps->addSeed($this->nbSeed);
        self::getMgr('player_state')->incStatNbSeedGained($this->playerId, $this->nbSeed);

        $notifier->notify(
            NTF_PLAYER_GAIN_SEED,
            clienttranslate('${player_name} gains ${gainSeedCount} ${seedIcon}'),
            [
                'gainSeedCount' => $this->nbSeed,
                'totalSeedCount' => $ps->seedCount,
                'fromCardId' => $this->fromCardId,
                'seedIcon' => clienttranslate('seed'),
            ]
        );
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $notifier->notifyNoMessage(
            NTF_PLAYER_PAY_SEED,
            [
                'totalSeedCount' => $this->nbSeedBefore,
            ]
        );
    }
}

class SproutAllOthers extends \BX\Action\BaseActionCommand
{
    private $nbSprout;
    private $fromCardId;
    private $undoPlayerExchanges;

    public function __construct(int $playerId, int $nbSprout, int $fromCardId)
    {
        parent::__construct($playerId);
        $this->nbSprout = $nbSprout;
        $this->fromCardId = $fromCardId;
        if ($nbSprout <= 0) {
            throw new \BgaSystemException("BUG! Invalid nbSprout: $nbSprout");
        }
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $playerExchangeMgr = self::getMgr('player_exchange');
        $playerExchanges = $playerExchangeMgr->getByFromPlayerIdExceptSame($this->playerId);
        $this->undoPlayerExchanges = \BX\Meta\deepClone($playerExchanges);

        foreach ($playerExchanges as $pe) {
            $pe->modifyAction();
            $pe->sproutGive += $this->nbSprout;
        }

        $notifier->notify(
            \BX\Action\NTF_MESSAGE,
            clienttranslate('${player_name} gives ${nbSprout} ${sproutIcon} to all other player(s)'),
            [
                'nbSprout' => $this->nbSprout,
                'sproutIcon' => 'sprout(s)',
                'i18n' => ['sproutIcon'],
            ]
        );

        foreach ($playerExchanges as $pe) {
            $notifier->notifyNoMessage(
                NTF_UPDATE_PLAYER_EXCHANGE,
                [
                    'fromPlayerId' => $this->playerId,
                    'toPlayerId' => $pe->toPlayerId,
                    'playerExchange' => $pe,
                ]
            );
        }
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->undoPlayerExchanges !== null) {
            foreach ($this->undoPlayerExchanges as $pe) {
                $notifier->notifyNoMessage(NTF_UPDATE_PLAYER_EXCHANGE, ['playerExchange' => $pe]);
            }
        }
    }
}

class SproutChooseOne extends \BX\Action\BaseActionCommand
{
    private $toPlayerId;
    private $nbSprout;
    private $fromCardId;
    private $undoPlayerExchange;

    public function __construct(int $playerId, int $toPlayerId, int $nbSprout, int $fromCardId)
    {
        parent::__construct($playerId);
        $this->toPlayerId = $toPlayerId;
        $this->nbSprout = $nbSprout;
        $this->fromCardId = $fromCardId;
        if ($nbSprout <= 0) {
            throw new \BgaSystemException("BUG! Invalid nbSprout: $nbSprout");
        }
        if ($playerId == $toPlayerId) {
            throw new \BgaSystemException("BUG! toPlayerId cannot by the same as playerId $playerId");
        }
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $playerMgr = self::getMgr('player');
        $toPlayer = $playerMgr->getByPlayerId($this->toPlayerId);
        if ($toPlayer === null) {
            throw new \BgaSystemException("BUG! Player {$this->toPlayerId} does not exist");
        }

        $playerExchangeMgr = self::getMgr('player_exchange');
        $playerExchange = $playerExchangeMgr->getByFromPlayerIdToPlayerId($this->playerId, $this->toPlayerId);
        $this->undoPlayerExchange = \BX\Meta\deepClone($playerExchange);

        $playerExchange->modifyAction();
        $playerExchange->sproutGive += $this->nbSprout;

        $notifier->notify(
            NTF_UPDATE_PLAYER_EXCHANGE,
            clienttranslate('${player_name} gives ${nbSprout} ${sproutIcon} to ${player_name2}'),
            [
                'fromPlayerId' => $this->playerId,
                'toPlayerId' => $this->toPlayerId,
                'playerExchange' => $playerExchange,
                'nbSprout' => $this->nbSprout,
                'sproutIcon' => 'sprout(s)',
                'player_name2' => $toPlayer->playerName,
                'i18n' => ['sproutIcon'],
            ]
        );
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->undoPlayerExchange !== null) {
            $notifier->notifyNoMessage(NTF_UPDATE_PLAYER_EXCHANGE, ['playerExchange' => $this->undoPlayerExchange]);
        }
    }
}

class GainSproutChooseOne extends \BX\Action\BaseActionCommand
{
    private $nbSproutChooseOne;

    public function __construct(int $playerId, int $nbSproutChooseOne)
    {
        parent::__construct($playerId);
        $this->nbSproutChooseOne = $nbSproutChooseOne;
        if ($nbSproutChooseOne < 0) {
            throw new \BgaSystemException("BUG! Invalid nbSproutChooseOne: $nbSproutChooseOne");
        }
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        $ps->modifyAction();
        $ps->setSproutChooseOne($this->nbSproutChooseOne);
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
    }
}

class PlaceSproutChooseOne extends \BX\Action\BaseActionCommand
{
    private $toPlayerId;
    private $fromCardId;
    private $sproutChooseOne;

    public function __construct(int $playerId, ?int $toPlayerId, int $fromCardId)
    {
        parent::__construct($playerId);
        $this->toPlayerId = $toPlayerId;
        $this->fromCardId = $fromCardId;
        if ($playerId == $toPlayerId) {
            throw new \BgaSystemException("BUG! toPlayerId cannot by the same as playerId $playerId");
        }
    }

    public function do(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        $playerStateMgr = self::getMgr('player_state');
        $ps = $playerStateMgr->getByPlayerId($this->playerId);
        if ($ps->gainedSproutChooseOne <= 0) {
            if ($this->toPlayerId !== null) {
                throw new \BgaSystemException("BUG! toPlayerId must be null");
            }
            return;
        }
        if ($this->toPlayerId === null) {
            throw new \BgaSystemException("BUG! toPlayerId must not be null");
        }
        $this->sproutChooseOne = new SproutChooseOne(
            $this->playerId,
            $this->toPlayerId,
            $ps->gainedSproutChooseOne,
            $this->fromCardId
        );
        $this->sproutChooseOne->do($notifier);
        $ps->modifyAction();
        $ps->clearSproutChooseOne();
    }

    public function undo(\BX\Action\BaseActionCommandNotifier $notifier)
    {
        if ($this->sproutChooseOne !== null) {
            $this->sproutChooseOne->undo($notifier);
        }
    }
}
