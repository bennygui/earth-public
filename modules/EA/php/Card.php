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
require_once('CardDefMgr.php');

const CARD_LOCATION_DECK = 0;
const CARD_LOCATION_DISCARD = 1;
const CARD_LOCATION_COMPOST = 2;
const CARD_LOCATION_HAND = 3;
const CARD_LOCATION_TABLEAU = 4;
const CARD_LOCATION_FAUNA_BOARD_FAUNA = 5;
const CARD_LOCATION_FAUNA_BOARD_ECOSYSTEM = 6;
const CARD_LOCATION_BOX = 7;
const CARD_LOCATION_PLAYER_BOARD = 8;
const CARD_LOCATION_GAIA_DECK = 9;
const CARD_LOCATION_GAIA_TABLEAU = 10;
const CARD_LOCATION_GAIA_COMPOST = 11;
const CARD_LOCATION_GAIA_DISCARD = 12;

const MAX_TABLEAU_SIZE = 4;

class Card extends \BX\Action\BaseActionRow
{
    /** @dbcol @dbkey */
    public $cardId;
    /** @dbcol */
    public $playerId;
    /** @dbcol */
    public $locationId;
    /** @dbcol */
    public $locationOrder;
    /** @dbcol */
    public $locationX;
    /** @dbcol */
    public $locationY;
    /** @dbcol */
    public $handChoosing;
    /** @dbcol */
    public $privateVisibility;
    /** @dbcol */
    public $sproutCount;
    /** @dbcol */
    public $growthCount;
    // Only for UI when planting
    public $canPlantOver;

    public function __construct()
    {
        $this->cardId = null;
        $this->playerId = null;
        $this->locationId = CARD_LOCATION_BOX;
        $this->locationOrder = 0;
        $this->locationX = null;
        $this->locationY = null;
        $this->handChoosing = false;
        $this->privateVisibility = false;
        $this->sproutCount = 0;
        $this->growthCount = 0;
        $this->canPlantOver = false;
    }

    public function toCompactUI()
    {
        return $this->cardId
            . "|" . $this->playerId
            . "|" . $this->locationId
            . "|" . $this->locationOrder
            . "|" . $this->locationX
            . "|" . $this->locationY
            . "|" . ($this->handChoosing ? 1 : 0)
            . "|" . ($this->privateVisibility ? 1 : 0)
            . "|" . $this->sproutCount
            . "|" . $this->growthCount
            . "|" . ($this->canPlantOver ? 1 : 0);
    }

    public static function createEmptyForTableau(int $playerId, int $x, int $y)
    {
        $empty = new Card();
        $empty->cardId = null;
        $empty->playerId = $playerId;
        $empty->locationId = CARD_LOCATION_TABLEAU;
        $empty->locationOrder = 0;
        $empty->locationX = $x;
        $empty->locationY = $y;
        $empty->handChoosing = false;
        $empty->privateVisibility = false;
        $empty->sproutCount = 0;
        $empty->growthCount = 0;
        $empty->canPlantOver = true;
        return $empty;
    }

    public function createEmptyFromCard()
    {
        $empty = new Card();
        $empty->cardId = null;
        $empty->playerId = $this->playerId;
        $empty->locationId = $this->locationId;
        $empty->locationOrder = $this->locationOrder;
        $empty->locationX = $this->locationX;
        $empty->locationY = $this->locationY;
        $empty->handChoosing = $this->handChoosing;
        $empty->privateVisibility = $this->privateVisibility;
        $empty->sproutCount = $this->sproutCount;
        $empty->growthCount = $this->growthCount;
        $empty->canPlantOver = $this->canPlantOver;
        return $empty;
    }

    public function getCardDef()
    {
        if ($this->cardId === null) {
            throw new \BgaSystemException("BUG! cardId is null");
        }
        return CardDefMgr::getByCardId($this->cardId);
    }

    public function isInDeck()
    {
        return ($this->locationId == CARD_LOCATION_DECK);
    }

    public function isInDiscard()
    {
        return ($this->locationId == CARD_LOCATION_DISCARD);
    }

    public function isHandChoosing()
    {
        return ($this->handChoosing);
    }

    public function isPrivateVisible()
    {
        return ($this->privateVisibility);
    }

    public function isInPlayerHand(int $playerId)
    {
        return ($this->playerId == $playerId && $this->locationId == CARD_LOCATION_HAND);
    }

    public function isInPlayerCompost(int $playerId)
    {
        return ($this->playerId == $playerId && $this->locationId == CARD_LOCATION_COMPOST);
    }

    public function isInPlayerTableau(int $playerId)
    {
        return ($this->playerId == $playerId && $this->locationId == CARD_LOCATION_TABLEAU);
    }

    public function isInPlayerBoardEvent(int $playerId)
    {
        return ($this->playerId == $playerId && $this->locationId == CARD_LOCATION_PLAYER_BOARD && $this->getCardDef()->isEvent());
    }

    public function isPlayerIsland(int $playerId)
    {
        return ($this->playerId == $playerId
            && $this->locationId == CARD_LOCATION_PLAYER_BOARD
            && $this->getCardDef()->type == CARD_TYPE_ISLAND
        );
    }

    public function isPlayerClimate(int $playerId)
    {
        return ($this->playerId == $playerId
            && $this->locationId == CARD_LOCATION_PLAYER_BOARD
            && $this->getCardDef()->type == CARD_TYPE_CLIMATE
        );
    }

    public function isPlayerEcosystem(int $playerId)
    {
        return ($this->playerId == $playerId
            && $this->locationId == CARD_LOCATION_PLAYER_BOARD
            && $this->getCardDef()->type == CARD_TYPE_ECOSYSTEM
        );
    }

    public function isPublicEcosystem()
    {
        return ($this->playerId == null
            && $this->locationId == CARD_LOCATION_FAUNA_BOARD_ECOSYSTEM
            && $this->getCardDef()->type == CARD_TYPE_ECOSYSTEM
        );
    }

    public function isInGaiaDeck()
    {
        return ($this->locationId == CARD_LOCATION_GAIA_DECK);
    }

    public function isInGaiaTableau()
    {
        return ($this->locationId == CARD_LOCATION_GAIA_TABLEAU);
    }

    public function isInGaiaCompost()
    {
        return ($this->locationId == CARD_LOCATION_GAIA_COMPOST);
    }

    public function isInGaiaDiscard()
    {
        return ($this->locationId == CARD_LOCATION_GAIA_DISCARD);
    }

    public function visibleForPlayer(?int $playerId)
    {
        if ($this->playerId == $playerId) {
            switch ($this->locationId) {
                case CARD_LOCATION_DECK:
                case CARD_LOCATION_DISCARD:
                case CARD_LOCATION_COMPOST:
                case CARD_LOCATION_BOX:
                    return false;
                case CARD_LOCATION_HAND:
                case CARD_LOCATION_TABLEAU:
                case CARD_LOCATION_FAUNA_BOARD_FAUNA:
                case CARD_LOCATION_FAUNA_BOARD_ECOSYSTEM:
                case CARD_LOCATION_PLAYER_BOARD:
                    return true;
                default:
                    throw new \BgaSystemException("BUG! Unknow card location: {$this->locationId}");
            }
        }
        if ($this->privateVisibility) {
            return false;
        }
        switch ($this->locationId) {
            case CARD_LOCATION_DECK:
            case CARD_LOCATION_DISCARD:
            case CARD_LOCATION_COMPOST:
            case CARD_LOCATION_HAND:
            case CARD_LOCATION_BOX:
            case CARD_LOCATION_GAIA_DECK:
            case CARD_LOCATION_GAIA_COMPOST:
                return false;
            case CARD_LOCATION_TABLEAU:
            case CARD_LOCATION_FAUNA_BOARD_FAUNA:
            case CARD_LOCATION_FAUNA_BOARD_ECOSYSTEM:
            case CARD_LOCATION_PLAYER_BOARD:
            case CARD_LOCATION_GAIA_TABLEAU:
            case CARD_LOCATION_GAIA_DISCARD:
                return true;
            default:
                throw new \BgaSystemException("BUG! Unknow card location: {$this->locationId}");
        }
    }

    public function moveToPlayerHand(int $playerId, bool $handChoosing = false, ?int $locationX = null)
    {
        $this->playerId = $playerId;
        $this->locationId = CARD_LOCATION_HAND;
        $this->locationOrder = 0;
        $this->locationX = $locationX != null ? $locationX : self::getNextHandLocationX($playerId);
        $this->locationY = null;
        $this->handChoosing = $handChoosing;
        $this->privateVisibility = false;
        $this->sproutCount = 0;
        $this->growthCount = 0;
    }

    public function moveToPlayerBoard(int $playerId)
    {
        $this->playerId = $playerId;
        $this->locationId = CARD_LOCATION_PLAYER_BOARD;
        $this->locationOrder = null;
        $this->locationX = null;
        $cardType = $this->getCardDef()->type;
        switch ($cardType) {
            case CARD_TYPE_ISLAND:
                $this->locationX = 0;
                break;
            case CARD_TYPE_CLIMATE:
                $this->locationX = 1;
                break;
            case CARD_TYPE_ECOSYSTEM:
                $this->locationX = 2;
                break;
            case CARD_TYPE_EVENT:
                $this->locationOrder = self::getNextEventLocationOrder($playerId);
                break;
        }
        $this->locationY = null;
        $this->handChoosing = false;
        $this->privateVisibility = false;
        $this->sproutCount = 0;
        $this->growthCount = 0;
    }

    public function moveToPlayerCompost(int $playerId)
    {
        $this->playerId = $playerId;
        $this->locationId = CARD_LOCATION_COMPOST;
        $this->locationOrder = self::getNextCompostOrder($playerId);
        $this->locationX = null;
        $this->locationY = null;
        $this->handChoosing = false;
        $this->privateVisibility = false;
        $this->sproutCount = 0;
        $this->growthCount = 0;
    }

    public function moveToPlayerTableau(int $playerId, int $posX, int $posY)
    {
        $this->playerId = $playerId;
        $this->locationId = CARD_LOCATION_TABLEAU;
        $this->locationOrder = 0;
        $this->locationX = $posX;
        $this->locationY = $posY;
        $this->handChoosing = false;
        $this->privateVisibility = true;
        $this->sproutCount = 0;
        $this->growthCount = 0;
    }

    public function markPublic()
    {
        $this->privateVisibility = false;
    }

    public function moveToDeck(int $order)
    {
        $this->playerId = null;
        $this->locationId = CARD_LOCATION_DECK;
        $this->locationOrder = $order;
        $this->locationX = null;
        $this->locationY = null;
        $this->handChoosing = false;
        $this->privateVisibility = false;
        $this->sproutCount = 0;
        $this->growthCount = 0;
    }

    public function moveToDiscard()
    {
        $this->playerId = null;
        $this->locationId = CARD_LOCATION_DISCARD;
        // We don't draw from the discard so the order is not important
        $this->locationOrder = 0;
        $this->locationX = null;
        $this->locationY = null;
        $this->handChoosing = false;
        $this->privateVisibility = false;
        $this->sproutCount = 0;
        $this->growthCount = 0;
        $cardTagMgr = \BX\Action\ActionRowMgrRegister::getMgr('card_tag');
        $cardTagMgr->deleteCardTag($this->cardId);
    }

    public function moveToBox()
    {
        $this->playerId = null;
        $this->locationId = CARD_LOCATION_BOX;
        $this->locationOrder = 0;
        $this->locationX = null;
        $this->locationY = null;
        $this->handChoosing = false;
        $this->privateVisibility = false;
        $this->sproutCount = 0;
        $this->growthCount = 0;
        $cardTagMgr = \BX\Action\ActionRowMgrRegister::getMgr('card_tag');
        $cardTagMgr->deleteCardTag($this->cardId);
    }

    public function moveToFaunaBoardFauna(int $x, int $y)
    {
        $this->playerId = null;
        $this->locationId = CARD_LOCATION_FAUNA_BOARD_FAUNA;
        $this->locationOrder = 0;
        $this->locationX = $x;
        $this->locationY = $y;
        $this->handChoosing = false;
        $this->privateVisibility = false;
        $this->sproutCount = 0;
        $this->growthCount = 0;
    }

    public function moveToFaunaBoardEcosystem(int $y)
    {
        $this->playerId = null;
        $this->locationId = CARD_LOCATION_FAUNA_BOARD_ECOSYSTEM;
        $this->locationOrder = 0;
        $this->locationX = null;
        $this->locationY = $y;
        $this->handChoosing = false;
        $this->privateVisibility = false;
        $this->sproutCount = 0;
        $this->growthCount = 0;
    }

    public function moveToGaiaTableau()
    {
        $this->playerId = null;
        $this->locationId = CARD_LOCATION_GAIA_TABLEAU;
        $this->locationOrder = self::getNextGaiaTableauOrder();
        $this->locationX = null;
        $this->locationY = null;
        $this->handChoosing = false;
        $this->privateVisibility = false;
        $this->sproutCount = 0;
        $this->growthCount = 0;
    }

    public function moveToGaiaDeck(int $order)
    {
        $this->playerId = null;
        $this->locationId = CARD_LOCATION_GAIA_DECK;
        $this->locationOrder = $order;
        $this->locationX = null;
        $this->locationY = null;
        $this->handChoosing = false;
        $this->privateVisibility = false;
        $this->sproutCount = 0;
        $this->growthCount = 0;
    }

    public function moveToGaiaCompost()
    {
        $this->playerId = null;
        $this->locationId = CARD_LOCATION_GAIA_COMPOST;
        $this->locationOrder = 0; // No order needed
        $this->locationX = null;
        $this->locationY = null;
        $this->handChoosing = false;
        $this->privateVisibility = false;
        $this->sproutCount = 0;
        $this->growthCount = 0;
    }

    public function moveToGaiaDiscard()
    {
        $this->playerId = null;
        $this->locationId = CARD_LOCATION_GAIA_DISCARD;
        $this->locationOrder = self::getNextGaiaDiscardOrder();
        $this->locationX = null;
        $this->locationY = null;
        $this->handChoosing = false;
        $this->privateVisibility = false;
        $this->sproutCount = 0;
        $this->growthCount = 0;
    }

    public function addSprout(int $nbSprout)
    {
        if ($nbSprout < 0) {
            throw new \BgaSystemException('BUG! Card::addSprout must be positive');
        }
        $this->sproutCount += $nbSprout;
        if ($this->sproutCount > $this->getCardDef()->sproutMax) {
            throw new \BgaSystemException('BUG! Card::addSprout is above maximum');
        }
    }

    public function removeSprout(int $nbSprout)
    {
        if ($nbSprout < 0) {
            throw new \BgaSystemException('BUG! Card::removeSprout must be positive');
        }
        $this->sproutCount -= $nbSprout;
        if ($this->sproutCount < 0) {
            throw new \BgaSystemException('BUG! Card::removeSprout is below zero');
        }
    }

    public function addGrowth(int $nbGrowth)
    {
        if ($nbGrowth < 0) {
            throw new \BgaSystemException('BUG! Card::addGrowth must be positive');
        }
        $this->growthCount += $nbGrowth;
        if ($this->growthCount > $this->getCardDef()->growthMax) {
            throw new \BgaSystemException('BUG! Card::addGrowth is above maximum');
        }
    }

    public function removeGrowth(int $nbGrowth)
    {
        if ($nbGrowth < 0) {
            throw new \BgaSystemException('BUG! Card::removeGrowth must be positive');
        }
        $this->growthCount -= $nbGrowth;
        if ($this->growthCount < 0) {
            throw new \BgaSystemException('BUG! Card::removeGrowth is below zero');
        }
    }

    private static function getNextHandLocationX(int $playerId)
    {
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        return $cardMgr->getPlayerNextHandLocationX($playerId);
    }

    private static function getNextEventLocationOrder(int $playerId)
    {
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        return $cardMgr->getPlayerNextEventLocationOrder($playerId);
    }

    private static function getNextCompostOrder(int $playerId)
    {
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        return $cardMgr->getPlayerNextCompostOrder($playerId);
    }

    private static function getNextGaiaTableauOrder()
    {
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        return $cardMgr->getNextGaiaTableauOrder();
    }

    private static function getNextGaiaDiscardOrder()
    {
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        return $cardMgr->getNextGaiaDiscardOrder();
    }
}

function cardsToCompactUI($cards)
{
    if ($cards === null) {
        return null;
    } else if (is_array($cards)) {
        return implode(';', array_map(fn ($c) => $c->toCompactUI(), $cards));
    } else {
        return $cards->toCompactUI();
    }
}

class CardCountsUI extends \BX\UI\UISerializable
{
    public $deckCount;
    public $discardCount;
    public $handCountByPlayerId;
    public $compostCountByPlayerId;
    public $gaiaDeckCount;

    public function __construct(array $playerIdArray)
    {
        $this->deckCount = 0;
        $this->discardCount = 0;
        $this->handCountByPlayerId = [];
        $this->compostCountByPlayerId = [];
        foreach ($playerIdArray as $playerId) {
            $this->handCountByPlayerId[$playerId] = 0;
            $this->compostCountByPlayerId[$playerId] = 0;
        }
        $this->gaiaDeckCount = 0;
        $this->gaiaDiscardCount = 0;
    }

    public function toUIEverything()
    {
        return \BX\UI\deepCopyToArray($this);
    }

    public function toUIDeckDiscard()
    {
        $ret = $this->toUIEverything();
        unset($ret['handCountByPlayerId']);
        unset($ret['compostCountByPlayerId']);
        return $ret;
    }

    public function toUIPlayer()
    {
        $ret = $this->toUIEverything();
        unset($ret['deckCount']);
        unset($ret['discardCount']);
        return $ret;
    }

    public function addCard(Card $card)
    {
        switch ($card->locationId) {
            case CARD_LOCATION_DECK:
                $this->deckCount += 1;
                break;
            case CARD_LOCATION_DISCARD:
                $this->discardCount += 1;
                break;
            case CARD_LOCATION_COMPOST:
                if (array_key_exists($card->playerId, $this->compostCountByPlayerId)) {
                    $this->compostCountByPlayerId[$card->playerId] += 1;
                }
                break;
            case CARD_LOCATION_HAND:
                if (array_key_exists($card->playerId, $this->handCountByPlayerId)) {
                    $this->handCountByPlayerId[$card->playerId] += 1;
                }
                break;
            case CARD_LOCATION_GAIA_DECK:
                $this->gaiaDeckCount += 1;
                break;
            case CARD_LOCATION_BOX:
            case CARD_LOCATION_TABLEAU:
            case CARD_LOCATION_FAUNA_BOARD_FAUNA:
            case CARD_LOCATION_FAUNA_BOARD_ECOSYSTEM:
            case CARD_LOCATION_PLAYER_BOARD:
            case CARD_LOCATION_GAIA_TABLEAU:
            case CARD_LOCATION_GAIA_COMPOST:
            case CARD_LOCATION_GAIA_DISCARD:
                break;
            default:
                throw new \BgaSystemException("BUG! Unknow card location: {$card->locationId}");
        }
    }
}

class CardResourceCountUI extends \BX\UI\UISerializable
{
    public $cardId;
    public $count;
    public $max;

    public function __construct(int $cardId, int $count, int $max)
    {
        $this->cardId = $cardId;
        $this->count = $count;
        $this->max = $max;
    }
}

class CardMgr extends \BX\Action\BaseActionRowMgr
{
    public function __construct()
    {
        parent::__construct('card', \EA\Card::class);
    }

    public function setup(array $playerIdArray)
    {
        $useEcosytemCards = !(isGameModeBeginner());
        $nbStartingCards = isGameModeAdvanced() ? 2 : 1;
        $this->setupEarthDeck();
        $this->setupFauna();
        $ecosystemFrontCards = [];
        if ($useEcosytemCards) {
            $ecosystemFrontCards = $this->getShuffledEcosystemFrontCards();
            $this->setupFaunaEcosystem($ecosystemFrontCards);
        }
        $this->setupPlayers($playerIdArray, CardDefMgr::getAllIsland(), $nbStartingCards);
        $this->setupPlayers($playerIdArray, CardDefMgr::getAllClimate(), $nbStartingCards);
        if ($useEcosytemCards) {
            $this->setupPlayers($playerIdArray, $ecosystemFrontCards, $nbStartingCards);
        }
        $this->setupGaiaDeck();
    }

    public function setupEarthDeck()
    {
        $earthCardIds = array_keys(CardDefMgr::getAllEarth());
        shuffle($earthCardIds);
        foreach ($earthCardIds as $i => $cardId) {
            $c = $this->db->newRow();
            $c->cardId = $cardId;
            $c->moveToDeck($i);
            $this->db->insertRow($c);
        }
    }

    public function setupGaiaDeck()
    {
        if (!isGameSolo()) {
            return;
        }
        $gaiaCardIds = array_keys(CardDefMgr::getAllGaia());
        shuffle($gaiaCardIds);
        foreach ($gaiaCardIds as $i => $cardId) {
            $c = $this->db->newRow();
            $c->cardId = $cardId;
            $c->moveToGaiaDeck($i);
            $this->db->insertRow($c);
        }
    }

    public function setupFauna()
    {
        $faunaCards = array_values(array_filter(CardDefMgr::getAllFauna(), fn ($cd) => $cd->isFront()));
        shuffle($faunaCards);
        for ($x = 0; $x < 2; ++$x) {
            for ($y = 0; $y < 2; ++$y) {
                $cd = array_shift($faunaCards);
                $cardId = $cd->id;
                if (random_int(0, 1) == 1) {
                    $cardId = $cd->otherSideCardId();
                }
                $c = $this->db->newRow();
                $c->cardId = $cardId;
                $c->moveToFaunaBoardFauna($x, $y);
                $this->db->insertRow($c);
            }
        }
    }

    public function getShuffledEcosystemFrontCards()
    {
        $ecosystemFrontCards = array_values(array_filter(CardDefMgr::getAllEcosystem(), fn ($cd) => $cd->isFront()));
        shuffle($ecosystemFrontCards);
        return $ecosystemFrontCards;
    }

    public function setupFaunaEcosystem(array &$ecosystemFrontCards)
    {
        for ($y = 0; $y < 2; ++$y) {
            $cd = array_shift($ecosystemFrontCards);
            $cardId = $cd->id;
            if (random_int(0, 1) == 1) {
                $cardId = $cd->otherSideCardId();
            }
            $c = $this->db->newRow();
            $c->cardId = $cardId;
            $c->moveToFaunaBoardEcosystem($y);
            $this->db->insertRow($c);
        }
    }

    public function setupPlayers(array $playerIdArray, array $cardDefArray, int $nbCard)
    {
        $frontCards = array_values(array_filter($cardDefArray, fn ($cd) => $cd->isFront()));
        shuffle($frontCards);
        foreach ($playerIdArray as $playerId) {
            $createCard = function (CardDef $cd) use ($playerId) {
                $c = $this->db->newRow();
                $c->cardId = $cd->id;
                $c->moveToPlayerHand($playerId, true);
                $this->db->insertRow($c);
            };
            for ($i = 0; $i < $nbCard; ++$i) {
                $cd = array_shift($frontCards);
                $createCard($cd);
                $cd = CardDefMgr::getByCardId($cd->otherSideCardId());
                $createCard($cd);
            }
        }
    }

    public function getCardById(int $cardId)
    {
        return $this->getRowByKey($cardId);
    }

    public function getAll()
    {
        return $this->getAllRowsByKey();
    }

    public function getFaunaCards()
    {
        return array_filter($this->getAll(), fn ($c) => $c->getCardDef()->isFauna());
    }

    public function getPublicEcosystemCards()
    {
        $cards = array_values(array_filter($this->getAll(), fn ($c) => $c->isPublicEcosystem()));
        usort($cards, fn ($c1, $c2) => $c1->locationY - $c2->locationY);
        return $cards;
    }

    public function getFaunaCardAtLocation(int $x, int $y)
    {
        foreach ($this->getFaunaCards() as $card) {
            if ($card->locationX == $x && $card->locationY == $y) {
                return $card;
            }
        }
        return null;
    }

    public function getAllVisibleForPlayer(int $playerId)
    {
        return array_filter($this->getAll(), fn ($c) => $c->visibleForPlayer($playerId));
    }

    public function getCardCountsUIEverything()
    {
        $playerMgr = \BX\Action\ActionRowMgrRegister::getMgr('player');
        $count = new CardCountsUI($playerMgr->getAllPlayerIds());
        foreach ($this->getAll() as $card) {
            $count->addCard($card);
        }
        return $count->toUIEverything();
    }

    public function getCardCountsUIDeckDiscard()
    {
        $count = new CardCountsUI([]);
        foreach ($this->getAll() as $card) {
            $count->addCard($card);
        }
        return $count->toUIDeckDiscard();
    }

    public function getCardCountsUIForPlayerId(int $playerId)
    {
        $count = new CardCountsUI([$playerId]);
        foreach ($this->getAll() as $card) {
            $count->addCard($card);
        }
        return $count->toUIPlayer();
    }

    public function isTableauFilledForOneOfAllPlayers()
    {
        $playerMgr = \BX\Action\ActionRowMgrRegister::getMgr('player');
        foreach ($playerMgr->getAllPlayerIds() as $playerId) {
            if ($this->isTableauFilledForPlayer($playerId)) {
                return true;
            }
        }
        return false;
    }

    public function isTableauFilledForPlayer(int $playerId)
    {
        return (count($this->getPlayerTableauCards($playerId, $playerId)) == MAX_TABLEAU_SIZE * MAX_TABLEAU_SIZE);
    }

    public function getPlayerHandCards(int $playerId)
    {
        return array_filter($this->getAll(), fn ($c) => $c->isInPlayerHand($playerId));
    }

    public function getPlayerHandChoosingCards(int $playerId)
    {
        return array_filter($this->getPlayerHandCards($playerId), fn ($c) => $c->isHandChoosing());
    }

    public function playerHasHandChoosingCards(int $playerId)
    {
        return count($this->getPlayerHandChoosingCards($playerId)) > 0;
    }

    public function getPlayerHandPlantableCards(int $playerId, int $playerSoilCount)
    {
        $ret = [];
        $plantOverColors = [];
        foreach ($this->getPlayerTableauCards($playerId, $playerId) as $card) {
            $abilityBrown = $card->getCardDef()->abilityBrown();
            if ($abilityBrown !== null && $abilityBrown->hasCanPlantOver()) {
                $plantOverColors[] = $abilityBrown->canPlantOverColor();
            }
        }
        foreach ($this->getAll() as $card) {
            if (!$card->isInPlayerHand($playerId)) {
                continue;
            }
            $cardDef = $card->getCardDef();
            if (!$cardDef->isPlantable()) {
                continue;
            }
            $cost = $this->getPlayerCardCost($playerId, $card->cardId);
            $cost -= $playerSoilCount;
            $abilityBlack = $cardDef->abilityBlack();
            if ($abilityBlack !== null && $abilityBlack->hasSpecialPlantingPayment()) {
                switch ($abilityBlack->getSpecialPlantingPayment()) {
                    case \EA\ABILITY_PLANT_PAY_WITH_SPROUT:
                        $cost -= $this->getPlayerSproutCount($playerId);
                        break;
                    case \EA\ABILITY_PLANT_PAY_WITH_GROWTH:
                        $cost -= $this->getPlayerGrowthCount($playerId);
                        break;
                    case \EA\ABILITY_PLANT_PAY_WITH_COMPOST:
                        // The -1 is because we cannot pay with the card that will be planted
                        $cost -= (count($this->getPlayerHandCards($playerId)) - 1);
                        break;
                    default:
                        throw new \BgaSystemException('BUG! Unkown special payment');
                }
            }
            if ($cost <= 0) {
                $ret[] = $card;
                continue;
            }
            // Some cards can be planted for free if the player has the ability in its tableau
            $canPlantOver = false;
            foreach ($plantOverColors as $color) {
                if ($cardDef->hasAbilityMatchingColor($color)) {
                    $canPlantOver = true;
                }
            }
            if ($canPlantOver) {
                $ret[] = $card;
                continue;
            }
        }
        return $ret;
    }

    public function getPlayerNextHandLocationX(int $playerId)
    {
        $max = -1;
        foreach ($this->getPlayerHandCards($playerId) as $card) {
            if ($card->locationX > $max) {
                $max = $card->locationX;
            }
        }
        return ($max + 1);
    }

    public function getPlayerEventHandCards($playerId)
    {
        $cards = array_filter(
            $this->getPlayerHandCards($playerId),
            fn ($c) => $c->getCardDef()->isEvent()
        );
        return $cards;
    }

    public function getPlayerBoardEventCards($playerId)
    {
        $cards = array_values(array_filter(
            $this->getAll(),
            fn ($c) => $c->isInPlayerBoardEvent($playerId)
        ));
        usort($cards, fn ($c1, $c2) => $c1->locationOrder - $c2->locationOrder);
        return $cards;
    }

    public function getAllPlayerBoardEventCards(array $playerIdArray)
    {
        $ret = [];
        foreach ($playerIdArray as $playerId) {
            $ret[$playerId] = $this->getPlayerBoardEventCards($playerId);
        }
        return $ret;
    }

    public function playerHasEventInHand(int $playerId)
    {
        return count($this->getPlayerEventHandCards($playerId)) > 0;
    }

    public function getPlayerCompostCards(int $playerId)
    {
        return array_filter($this->getAll(), fn ($c) => $c->isInPlayerCompost($playerId));
    }

    public function getPlayerNextCompostOrder(int $playerId)
    {
        $max = -1;
        foreach ($this->getPlayerCompostCards($playerId) as $card) {
            if ($card->locationOrder > $max) {
                $max = $card->locationOrder;
            }
        }
        return ($max + 1);
    }

    public function getGaiaTableauCards()
    {
        $cards = array_filter($this->getAll(), fn ($c) => $c->isInGaiaTableau());
        usort($cards, fn ($c1, $c2) => $c1->locationOrder - $c2->locationOrder);
        return $cards;
    }

    public function getNextGaiaTableauOrder()
    {
        $max = -1;
        foreach ($this->getGaiaTableauCards() as $card) {
            if ($card->locationOrder > $max) {
                $max = $card->locationOrder;
            }
        }
        return ($max + 1);
    }

    public function getGaiaDiscardCards()
    {
        $cards = array_filter($this->getAll(), fn ($c) => $c->isInGaiaDiscard());
        usort($cards, fn ($c1, $c2) => $c1->locationOrder - $c2->locationOrder);
        return $cards;
    }

    public function getGaiaDiscardTopCard()
    {
        $cards = $this->getGaiaDiscardCards();
        if (count($cards) == 0) {
            return null;
        }
        return $cards[count($cards) - 1];
    }

    public function getNextGaiaDiscardOrder()
    {
        $max = -1;
        foreach ($this->getGaiaDiscardCards() as $card) {
            if ($card->locationOrder > $max) {
                $max = $card->locationOrder;
            }
        }
        return ($max + 1);
    }

    public function getGaiaCompostCardCount()
    {
        return count(array_filter($this->getAll(), fn ($c) => $c->isInGaiaCompost()));
    }

    public function getPlayerNextEventLocationOrder(int $playerId)
    {
        $max = -1;
        foreach ($this->getPlayerBoardEventCards($playerId) as $card) {
            if ($card->locationOrder > $max) {
                $max = $card->locationOrder;
            }
        }
        return ($max + 1);
    }

    public function getPlayerIslandCard($playerId)
    {
        foreach ($this->getAll() as $card) {
            if ($card->isPlayerIsland($playerId)) {
                return $card;
            }
        }
        return null;
    }

    public function getPlayerClimateCard($playerId)
    {
        foreach ($this->getAll() as $card) {
            if ($card->isPlayerClimate($playerId)) {
                return $card;
            }
        }
        return null;
    }

    public function getPlayerEcosystemCard($playerId)
    {
        foreach ($this->getAll() as $card) {
            if ($card->isPlayerEcosystem($playerId)) {
                return $card;
            }
        }
        return null;
    }

    public function getTopCardFromDeck()
    {
        $getTopCard = function () {
            $topCard = null;
            foreach ($this->getAll() as $card) {
                if (!$card->isInDeck()) {
                    continue;
                }
                if ($topCard === null || $card->locationOrder < $topCard->locationOrder) {
                    $topCard = $card;
                }
            }
            return $topCard;
        };
        $topCard = $getTopCard();
        if ($topCard !== null) {
            return $topCard;
        }
        // No more cards in deck, shuffle discard
        $newDeckCards = [];
        foreach ($this->getAll() as $card) {
            if (!$card->isInDiscard()) {
                continue;
            }
            $newDeckCards[] = $card;
        }
        if (count($newDeckCards) == 0) {
            // No more cards in deck and in discard
            return null;
        }
        shuffle($newDeckCards);
        foreach ($newDeckCards as $i => $card) {
            $card->modifyAction();
            $card->moveToDeck($i);
        }
        return $getTopCard();
    }

    public function getTopCardFromPlayerCompost(int $playerId)
    {
        $topCard = null;
        foreach ($this->getAll() as $card) {
            if (!$card->isInPlayerCompost($playerId)) {
                continue;
            }
            if ($topCard === null || $card->locationOrder > $topCard->locationOrder) {
                $topCard = $card;
            }
        }
        return $topCard;
    }

    public function getTopCardFromGaiaDeck()
    {
        $topCard = null;
        foreach ($this->getAll() as $card) {
            if (!$card->isInGaiaDeck()) {
                continue;
            }
            if ($topCard === null || $card->locationOrder < $topCard->locationOrder) {
                $topCard = $card;
            }
        }
        return $topCard;
    }

    public function shuffleGaiaDeck()
    {
        $newDeckCards = [];
        foreach ($this->getAll() as $card) {
            if (!$card->getCardDef()->isGaia()) {
                continue;
            }
            $newDeckCards[] = $card;
        }
        shuffle($newDeckCards);
        foreach ($newDeckCards as $i => $card) {
            $card->modifyAction();
            $card->moveToGaiaDeck($i);
        }
    }

    public function getAllPlayerTableauCards(array $playerIdArray, int $viewerPlayerId)
    {
        $ret = [];
        foreach ($playerIdArray as $playerId) {
            $ret[$playerId] = $this->getPlayerTableauCards($playerId, $viewerPlayerId);
        }
        return $ret;
    }

    private static function cmpTableauCards(\EA\Card $c1, \EA\Card $c2)
    {
        $cmp = $c1->locationY - $c2->locationY;
        if ($cmp != 0) {
            return $cmp;
        }
        return $c1->locationX - $c2->locationX;
    }

    public function getPlayerPrivateTableauCards(int $playerId)
    {
        $tableauCards = array_filter($this->getAll(), fn ($c) => $c->isInPlayerTableau($playerId) && $c->isPrivateVisible());
        usort($tableauCards, fn ($c1, $c2) => self::cmpTableauCards($c1, $c2));
        return $tableauCards;
    }

    public function getPlayerTableauCards(int $playerId, ?int $viewerPlayerId)
    {
        $ret = [];
        foreach ($this->getAll() as $card) {
            if (!$card->isInPlayerTableau($playerId)) {
                continue;
            }
            if ($card->visibleForPlayer($viewerPlayerId)) {
                $ret[] = $card;
            } else {
                $ret[] = $card->createEmptyFromCard();
            }
        }
        usort($ret, fn ($c1, $c2) => self::cmpTableauCards($c1, $c2));
        return $ret;
    }

    public function getPlayerTableauCardAtPos(int $playerId, int $posX, int $posY)
    {
        $tableauCards = $this->getPlayerTableauCards($playerId, $playerId);
        foreach ($tableauCards as $card) {
            if ($card->locationX == $posX && $card->locationY == $posY) {
                return $card;
            }
        }
        return null;
    }

    public function getPlayerTableauWithPlacementCardsUI(int $playerId, int $plantCardId)
    {
        $plantCardDef = \EA\CardDefMgr::getByCardId($plantCardId);
        $tableau = [];
        $minX = 0;
        $maxX = 0;
        $minY = 0;
        $maxY = 0;
        foreach ($this->getAll() as $card) {
            if (!$card->isInPlayerTableau($playerId)) {
                continue;
            }
            // Planting over some cards is possible
            $cardClone = \BX\Meta\deepClone($card);
            $cardClone->canPlantOver = false;
            $abilityBrown = $card->getCardDef()->abilityBrown();
            if (
                $abilityBrown !== null
                && $abilityBrown->hasCanPlantOver()
                && $plantCardDef->hasAbilityMatchingColor($abilityBrown->canPlantOverColor())
            ) {
                $cardClone->canPlantOver = true;
            }
            $tableau[$card->locationX][$card->locationY] = $cardClone;
            $minX = min($minX, $card->locationX);
            $maxX = max($maxX, $card->locationX);
            $minY = min($minY, $card->locationY);
            $maxY = max($maxY, $card->locationY);
        }
        $createPlacementCard = function (int $x, int $y) use ($playerId, $minX, $maxX, $minY, $maxY, &$tableau) {
            if (\array_key_exists($x, $tableau) && \array_key_exists($y, $tableau[$x])) {
                return;
            }
            if (abs($maxX - $minX) + 1 >= MAX_TABLEAU_SIZE) {
                if ($x < $minX || $x > $maxX) {
                    return;
                }
            }
            if (abs($maxY - $minY) + 1 >= MAX_TABLEAU_SIZE) {
                if ($y < $minY || $y > $maxY) {
                    return;
                }
            }
            $tableau[$x][$y] = Card::createEmptyForTableau($playerId, $x, $y);
        };
        for ($x = $minX; $x <= $maxX; ++$x) {
            for ($y = $minY; $y <= $maxY; ++$y) {
                if (\array_key_exists($x, $tableau) && \array_key_exists($y, $tableau[$x])) {
                    if ($tableau[$x][$y]->cardId !== null) {
                        for ($dx = -1; $dx <= 1; ++$dx) {
                            for ($dy = -1; $dy <= 1; ++$dy) {
                                $createPlacementCard($x + $dx, $y + $dy);
                            }
                        }
                    }
                }
            }
        }
        if (count($tableau) == 0) {
            $createPlacementCard(0, 0);
        }
        $ret = [];
        foreach ($tableau as $innerTableau) {
            foreach ($innerTableau as $card) {
                $ret[] = $card;
            }
        }
        usort($ret, fn ($c1, $c2) => self::cmpTableauCards($c1, $c2));
        return $ret;
    }

    public function canPlantCardAtPosition(int $playerId, int $cardId, int $posX, int $posY)
    {
        $tableauCards = $this->getPlayerTableauWithPlacementCardsUI($playerId, $cardId);
        foreach ($tableauCards as $card) {
            if ($card->locationX == $posX && $card->locationY == $posY) {
                if ($card->canPlantOver) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getPlayerTableauCountSprout(int $playerId)
    {
        $tableauCards = $this->getPlayerTableauCards($playerId, $playerId);
        $ret = [];
        foreach ($tableauCards as $card) {
            $max = $card->getCardDef()->sproutMax;
            if ($max === null || $max == 0) {
                continue;
            }
            $ret[] = new CardResourceCountUI($card->cardId, $card->sproutCount, $max);
        }
        return $ret;
    }

    public function getPlayerTableauCountGrowth(int $playerId)
    {
        $tableauCards = $this->getPlayerTableauCards($playerId, $playerId);
        $ret = [];
        foreach ($tableauCards as $card) {
            $max = $card->getCardDef()->growthMax;
            if ($max === null || $max == 0) {
                continue;
            }
            $ret[] = new CardResourceCountUI($card->cardId, $card->growthCount, $max);
        }
        return $ret;
    }

    public function getPlayerSproutCount(int $playerId)
    {
        $tableauCards = $this->getPlayerTableauCards($playerId, $playerId);
        $count = 0;
        foreach ($tableauCards as $card) {
            $max = $card->getCardDef()->sproutMax;
            if ($max === null || $max == 0) {
                continue;
            }
            $count += $card->sproutCount;
        }
        return $count;
    }

    public function getPlayerGrowthCount(int $playerId)
    {
        $tableauCards = $this->getPlayerTableauCards($playerId, $playerId);
        $count = 0;
        foreach ($tableauCards as $card) {
            $max = $card->getCardDef()->growthMax;
            if ($max === null || $max == 0) {
                continue;
            }
            $count += $card->growthCount;
        }
        return $count;
    }

    public function getPlayerCanopiesCount(int $playerId)
    {
        $tableauCards = $this->getPlayerTableauCards($playerId, $playerId);
        $count = 0;
        foreach ($tableauCards as $card) {
            $max = $card->getCardDef()->growthMax;
            if ($max === null || $max == 0) {
                continue;
            }
            if ($card->growthCount == $max) {
                $count += 1;
            }
        }
        return $count;
    }

    public function getPlayerIslandClimateTableauCards(int $playerId, bool $considerPrivateVisibility = true)
    {
        $cards = $this->getPlayerTableauCards($playerId, $considerPrivateVisibility ? $playerId : null);
        $cards[] = $this->getPlayerIslandCard($playerId);
        $cards[] = $this->getPlayerClimateCard($playerId);
        return $cards;
    }

    public function getPlayerCardCost(int $playerId, int $cardId)
    {
        $cardDef = \EA\CardDefMgr::getByCardId($cardId);
        $cost = $cardDef->soil;
        $cards = $this->getPlayerIslandClimateTableauCards($playerId);
        foreach ($cards as $card) {
            $abilityBrown = $card->getCardDef()->abilityBrown();
            if ($abilityBrown === null) {
                continue;
            }
            $abilityBrown->foreachGain(function ($abilityId, $count) use (&$cost, $cardDef, $abilityBrown) {
                switch ($abilityId) {
                    case ABILITY_REDUCE_COST_FOR_TYPE:
                        if ($cardDef->hasCardType($abilityBrown->getPerTypeCondition())) {
                            $cost -= $count;
                        }
                        break;
                    case ABILITY_REDUCE_COST_FOR_HABITAT:
                        if ($cardDef->hasHabitat($abilityBrown->getPerHabitatCondition())) {
                            $cost -= $count;
                        }
                        break;
                }
            });
        }
        return max(0, $cost);
    }

    public function getPlayerTableauCardsInCardColumn(int $playerId, int $cardId)
    {
        $ret = [];
        $searchCard = $this->getCardById($cardId);
        foreach ($this->getPlayerTableauCards($playerId, $playerId) as $card) {
            if ($card->locationX == $searchCard->locationX) {
                $ret[] = $card;
            }
        }
        return $ret;
    }

    public function getPlayerTableauCardsPerColumn(int $playerId)
    {
        $ret = [];
        foreach ($this->getPlayerTableauCards($playerId, $playerId) as $card) {
            $ret[$card->locationX][] = $card;
        }
        return $ret;
    }

    public function getPlayerTableauCardsInCardRow(int $playerId, int $cardId)
    {
        $ret = [];
        $searchCard = $this->getCardById($cardId);
        foreach ($this->getPlayerTableauCards($playerId, $playerId) as $card) {
            if ($card->locationY == $searchCard->locationY) {
                $ret[] = $card;
            }
        }
        return $ret;
    }

    public function getPlayerTableauCardsPerRow(int $playerId)
    {
        $ret = [];
        foreach ($this->getPlayerTableauCards($playerId, $playerId) as $card) {
            $ret[$card->locationY][] = $card;
        }
        return $ret;
    }

    public function getPlayerTableauCardsInCardAdjacent(int $playerId, int $cardId)
    {
        $ret = [];
        $searchCard = $this->getCardById($cardId);
        foreach ($this->getPlayerTableauCards($playerId, $playerId) as $card) {
            if (
                ($card->locationX == $searchCard->locationX - 1 && $card->locationY == $searchCard->locationY - 1)
                || ($card->locationX == $searchCard->locationX + 0 && $card->locationY == $searchCard->locationY - 1)
                || ($card->locationX == $searchCard->locationX + 1 && $card->locationY == $searchCard->locationY - 1)
                || ($card->locationX == $searchCard->locationX - 1 && $card->locationY == $searchCard->locationY + 0)
                || ($card->locationX == $searchCard->locationX + 1 && $card->locationY == $searchCard->locationY + 0)
                || ($card->locationX == $searchCard->locationX - 1 && $card->locationY == $searchCard->locationY + 1)
                || ($card->locationX == $searchCard->locationX + 0 && $card->locationY == $searchCard->locationY + 1)
                || ($card->locationX == $searchCard->locationX + 1 && $card->locationY == $searchCard->locationY + 1)
            ) {
                $ret[] = $card;
            }
        }
        return $ret;
    }

    public function getPlayerTableauCardsInCardOrthoAdjacent(int $playerId, int $cardId)
    {
        $ret = [];
        $searchCard = $this->getCardById($cardId);
        foreach ($this->getPlayerTableauCards($playerId, $playerId) as $card) {
            if (
                ($card->locationX == $searchCard->locationX + 0 && $card->locationY == $searchCard->locationY - 1)
                || ($card->locationX == $searchCard->locationX - 1 && $card->locationY == $searchCard->locationY + 0)
                || ($card->locationX == $searchCard->locationX + 1 && $card->locationY == $searchCard->locationY + 0)
                || ($card->locationX == $searchCard->locationX + 0 && $card->locationY == $searchCard->locationY + 1)
            ) {
                $ret[] = $card;
            }
        }
        return $ret;
    }

    public function getPlayerTableauCardsInCardDiagAdjacent(int $playerId, int $cardId)
    {
        $ret = [];
        $searchCard = $this->getCardById($cardId);
        foreach ($this->getPlayerTableauCards($playerId, $playerId) as $card) {
            if (
                ($card->locationX == $searchCard->locationX - 1 && $card->locationY == $searchCard->locationY - 1)
                || ($card->locationX == $searchCard->locationX + 1 && $card->locationY == $searchCard->locationY - 1)
                || ($card->locationX == $searchCard->locationX - 1 && $card->locationY == $searchCard->locationY + 1)
                || ($card->locationX == $searchCard->locationX + 1 && $card->locationY == $searchCard->locationY + 1)
            ) {
                $ret[] = $card;
            }
        }
        return $ret;
    }

    public function getPlayerTableauCardsInCardOrthoLine(int $playerId, int $cardId, callable $isMatch)
    {
        $searchCard = $this->getCardById($cardId);
        $bestLineCards = [$searchCard];
        $orthoAdjacentForCard = [];
        foreach ($this->getPlayerTableauCards($playerId, $playerId) as $card) {
            if (!$isMatch($card)) {
                continue;
            }
            $orthoAdjacentForCard[$card->cardId] = array_filter(
                $this->getPlayerTableauCardsInCardOrthoAdjacent($playerId, $card->cardId),
                fn($c) => $isMatch($c)
            );
        }
        foreach ($this->getPlayerTableauCards($playerId, $playerId) as $card) {
            if (!$isMatch($card)) {
                continue;
            }
            $lineCards = $this->buildCardOrthoLine($playerId, $cardId, $isMatch, $card, [$card], [$card->cardId => true], $orthoAdjacentForCard);
            if (count($lineCards) > count($bestLineCards) && count(array_filter($lineCards, fn ($c) => $c->cardId == $cardId)) > 0) {
                $bestLineCards = $lineCards;
            }
        }
        return $bestLineCards;
    }

    private function buildCardOrthoLine(int $playerId, int $searchCardId, callable $isMatch, Card $lastCard, array $matchedCards, array $viewedCardIds, array &$orthoAdjacentForCard)
    {
        $longestMatchCards = $matchedCards;
        foreach ($orthoAdjacentForCard[$lastCard->cardId] as $orthoCard) {
            if (array_key_exists($orthoCard->cardId, $viewedCardIds)) {
                continue;
            }
            $newMatchCards = $this->buildCardOrthoLine(
                $playerId,
                $searchCardId,
                $isMatch,
                $orthoCard,
                array_merge($matchedCards, [$orthoCard]),
                array_replace($viewedCardIds, [$orthoCard->cardId => true]),
                $orthoAdjacentForCard
            );
            if (count($newMatchCards) > count($longestMatchCards)) {
                $longestMatchCards = $newMatchCards;
            } else if (
                count($newMatchCards) == count($longestMatchCards)
                && count(array_filter($newMatchCards, fn ($c) => $c->cardId == $searchCardId)) > 0
            ) {
                $longestMatchCards = $newMatchCards;
            }
        }
        return $longestMatchCards;
    }

    public function getPlayerTableauCardsInCardDiagLine(int $playerId, int $cardId, callable $isMatch, array &$diagAdjacentForCard)
    {
        $searchCard = $this->getCardById($cardId);
        $bestLineCards = [$searchCard];
        foreach ($this->getPlayerTableauCards($playerId, $playerId) as $card) {
            if (!$isMatch($card)) {
                continue;
            }
            $lineCards = $this->buildCardDiagLine($playerId, $isMatch, $card, [$card], [$card->cardId => true], $diagAdjacentForCard);
            if (count($lineCards) > count($bestLineCards) && count(array_filter($lineCards, fn ($c) => $c->cardId == $cardId)) > 0) {
                $bestLineCards = $lineCards;
            }
        }
        return $bestLineCards;
    }

    private function buildCardDiagLine(int $playerId, callable $isMatch, Card $lastCard, array $matchedCards, array $viewedCardIds, array &$diagAdjacentForCard)
    {
        $longestMatchCards = $matchedCards;
        foreach ($diagAdjacentForCard[$lastCard->cardId] as $orthoCard) {
            if (array_key_exists($orthoCard->cardId, $viewedCardIds)) {
                continue;
            }
            $newMatchCards = $this->buildCardDiagLine(
                $playerId,
                $isMatch,
                $orthoCard,
                array_merge($matchedCards, [$orthoCard]),
                array_replace($viewedCardIds, [$orthoCard->cardId => true]),
                $diagAdjacentForCard
            );
            if (count($newMatchCards) > count($longestMatchCards)) {
                $longestMatchCards = $newMatchCards;
            }
        }
        return $longestMatchCards;
    }

    public function playerCanPlayConversion(int $playerId)
    {
        return ($this->getPlayerSproutCount($playerId) >= 3);
    }

    public function getIslandClimateTableauPlayerCardsWithAbilityMatchingMainAction(int $playerId, int $activePlayerId, int $mainActionId)
    {
        $cards = $this->getPlayerTableauCards($playerId, $playerId);
        $cards[] = $this->getPlayerIslandCard($playerId);
        $cards[] = $this->getPlayerClimateCard($playerId);
        return self::filterActivatableCards($cards, $playerId, $activePlayerId, $mainActionId);
    }

    public function getTableauPlayerCardsWithAbilityMatchingMainAction(int $playerId, int $activePlayerId, int $mainActionId)
    {
        $cards = $this->getPlayerTableauCards($playerId, $playerId);
        return self::filterActivatableCards($cards, $playerId, $activePlayerId, $mainActionId);
    }

    public static function filterActivatableCards(array $cards, int $playerId, int $activePlayerId, int $mainActionId)
    {
        return array_filter($cards, function ($card)  use ($mainActionId, $playerId, $activePlayerId) {
            // Filter to get only cards with ability matching the main action color
            if (!$card->getCardDef()->activateForMainAction($mainActionId)) {
                return false;
            }
            // Filter to remove cards with ability that says: "if you choose the [color] action"
            $abColorId = $card->getCardDef()->getAbilityMatchingMainAction($mainActionId)->getIfChooseColorCondition();
            if ($abColorId === null) {
                return true;
            }
            return ($playerId == $activePlayerId && $abColorId == \EA\CardDef::mainActionToAbilityColor($mainActionId));
        });
    }

    public function zombieDiscard(int $playerId)
    {
        foreach ($this->getPlayerHandCards($playerId) as $card) {
            $card->moveToDiscard();
            $this->db->updateRow($card);
        }
    }

    public function debugMoveCardToPlayerHandNow(int $cardId, int $playerId)
    {
        $card = $this->getCardById($cardId);
        if ($card === null) {
            foreach (array_keys(CardDefMgr::getAllEarth()) as $key) {
                if (strpos("$key", "$cardId") !== false) {
                    $card = $this->getCardById($key);
                    break;
                }
            }
        }
        if ($card === null) {
            throw new \BgaSystemException("BUG! Could not find card with cardId $cardId");
        }
        $card->moveToPlayerHand($playerId);
        $this->db->updateRow($card);
    }

    public function debugMoveAllEventsToPlayerHandNow(int $playerId)
    {
        foreach (CardDefMgr::getAllEarth() as $cardDef) {
            if (!$cardDef->isEvent()) {
                continue;
            }
            $card = $this->getCardById($cardDef->id);
            $card->moveToPlayerHand($playerId);
            $this->db->updateRow($card);
        }
    }

    public function debugMoveAllEarthToPlayerHandNow(int $playerId)
    {
        foreach (CardDefMgr::getAllEarth() as $cardDef) {
            if (!$cardDef->isEarth()) {
                continue;
            }
            $card = $this->getCardById($cardDef->id);
            $card->moveToPlayerHand($playerId);
            $this->db->updateRow($card);
        }
    }

    public function debugReplaceFauna(array $cardIds)
    {
        foreach ($this->getFaunaCards() as $card) {
            $this->db->deleteRow($card);
            $card->cardId = array_shift($cardIds);
            $this->db->insertRow($card);
        }
    }

    public function debugRandomEndGame(array $playerIdArray)
    {
        shuffle($playerIdArray);
        $mustFillTableau = true;
        foreach ($playerIdArray as $playerId) {
            // Initial player setup
            $hand = $this->getPlayerHandCards($playerId);
            shuffle($hand);
            $choice = [];
            $choice[] = array_values(array_filter($hand, fn ($c) => $c->getCardDef()->isIsland()))[0];
            $choice[] = array_values(array_filter($hand, fn ($c) => $c->getCardDef()->isClimate()))[0];
            if (!isGameModeBeginner()) {
                $choice[] = array_values(array_filter($hand, fn ($c) => $c->getCardDef()->isEcosystem()))[0];
            }
            foreach ($choice as $card) {
                $card->moveToPlayerBoard($playerId);
                $this->db->updateRow($card);
            }
            $hand = $this->getPlayerHandCards($playerId);
            foreach ($hand as $card) {
                $card->moveToBox();
                $this->db->updateRow($card);
            }

            // Draw hand of card
            $deckCards = array_values(array_filter($this->getAll(), fn ($c) => $c->isInDeck()));
            shuffle($deckCards);

            $nbCards = random_int(0, 30);
            for ($i = 0; $i < $nbCards; ++$i) {
                $card = array_shift($deckCards);
                if ($card !== null) {
                    $card->moveToPlayerHand($playerId);
                    $this->db->updateRow($card);
                }
            }

            // Compost cards
            $nbCards = random_int(0, 40);
            for ($i = 0; $i < $nbCards; ++$i) {
                $card = array_shift($deckCards);
                if ($card !== null) {
                    $card->moveToPlayerCompost($playerId);
                    $this->db->updateRow($card);
                }
            }

            // Play event cards
            $nbCards = random_int(0, 10);
            foreach ($deckCards as $i => $card) {
                if (!$card->getCardDef()->isEvent()) {
                    continue;
                }
                $nbCards -= 1;
                if ($nbCards < 0) {
                    break;
                }
                unset($deckCards[$i]);
                $card->moveToPlayerBoard($playerId);
                $this->db->updateRow($card);
            }
            $deckCards = array_values($deckCards);

            // Build Tableau
            $nbCards = random_int(10, MAX_TABLEAU_SIZE * MAX_TABLEAU_SIZE);
            if ($mustFillTableau) {
                $mustFillTableau = false;
                $nbCards = MAX_TABLEAU_SIZE * MAX_TABLEAU_SIZE;
            }
            foreach ($deckCards as $i => $card) {
                if (!$card->getCardDef()->isPlantable()) {
                    continue;
                }
                $nbCards -= 1;
                if ($nbCards < 0) {
                    break;
                }
                unset($deckCards[$i]);
                $pos = array_filter(
                    $this->getPlayerTableauWithPlacementCardsUI($playerId, $card->cardId),
                    fn ($c) => $c->cardId === null
                );
                if (count($pos) == 0) {
                    break;
                }
                shuffle($pos);
                $firstPos = array_values($pos)[0];
                $card->moveToPlayerTableau($playerId, $firstPos->locationX, $firstPos->locationY);
                $card->markPublic();
                $this->db->updateRow($card);
            }

            // Place sprouts and growth
            foreach ($this->getPlayerTableauCards($playerId, $playerId) as $card) {
                $max = $card->getCardDef()->sproutMax;
                if ($max !== null && $max > 0) {
                    $card->sproutCount = random_int(0, $max);
                }
                $max = $card->getCardDef()->growthMax;
                if ($max !== null && $max > 0) {
                    $card->growthCount = random_int(0, $max);
                }
                $this->db->updateRow($card);
            }
        }

        // Build discard
        $deckCards = array_values(array_filter($this->getAll(), fn ($c) => $c->isInDeck()));
        shuffle($deckCards);
        $nbCards = random_int(0, 20);
        for ($i = 0; $i < $nbCards; ++$i) {
            $card = array_shift($deckCards);
            if ($card !== null) {
                $card->moveToDiscard();
                $this->db->updateRow($card);
            }
        }
    }
}
