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

const ACTIVATION_DIRECTION_ISLAND_CLIMATE_TABLEAU = 0;
const ACTIVATION_DIRECTION_TABLEAU_ISLAND_CLIMATE = 1;

class PlayerState extends \BX\Action\BaseActionRow
{
    /** @dbcol @dbkey */
    public $playerId;
    /** @dbcol */
    public $soilCount;
    /** @dbcol */
    public $gainedSprout;
    /** @dbcol */
    public $gainedGrowth;
    /** @dbcol */
    public $gainedCompostFromHand;
    /** @dbcol */
    public $gainedCardIdList;
    /** @dbcol */
    public $gainedCardIdDivided;
    /** @dbcol */
    public $stateActivationDirection;
    /** @dbcol */
    public $stateActivatedBeforeCopyCardId;
    /** @dbcol */
    public $stateActivatedAfterCopyCardId;
    /** @dbcol */
    public $stateEventBeforeCopyCardId;
    /** @dbcol */
    public $stateEventAfterCopyCardId;
    /** @dbcol */
    public $stateEventCurrentCardId;
    /** @dbcol */
    public $returnFromEventStateId;
    /** @dbcol */
    public $returnFromConversionStateId;
    /** @dbcol */
    public $firstPlantedCardId;
    /** @dbcol */
    public $secondPlantedCardId;
    // Stats
    /** @dbcol @dboptional */
    public $statNbCardsDrawn;
    /** @dbcol @dboptional */
    public $statNbCardsComposted;
    /** @dbcol @dboptional */
    public $statNbSoilGained;
    /** @dbcol @dboptional */
    public $statNbCardsPaid;
    /** @dbcol @dboptional */
    public $statNbSproutsPlaced;
    /** @dbcol @dboptional */
    public $statNbSproutsPaid;
    /** @dbcol @dboptional */
    public $statNbSproutsConverted;
    /** @dbcol @dboptional */
    public $statNbGrowthPlaced;
    /** @dbcol @dboptional */
    public $statNbGrowthPaid;

    public function __construct()
    {
        $this->playerId = null;
        $this->soilCount = 0;
        $this->gainedSprout = 0;
        $this->gainedGrowth = 0;
        $this->gainedCompostFromHand = 0;
        $this->gainedCardIdList = null;
        $this->gainedCardIdDivided = false;
        $this->stateActivationDirection = null;
        $this->stateActivatedBeforeCopyCardId = null;
        $this->stateActivatedAfterCopyCardId = null;
        $this->stateEventBeforeCopyCardId = null;
        $this->stateEventAfterCopyCardId = null;
        $this->stateEventCurrentCardId = null;
        $this->returnFromEventStateId = null;
        $this->returnFromConversionStateId = null;
        $this->firstPlantedCardId = null;
        $this->secondPlantedCardId = null;
        // Stats
        $this->statNbCardsDrawn = 0;
        $this->statNbCardsComposted = 0;
        $this->statNbSoilGained = 0;
        $this->statNbCardsPaid = 0;
        $this->statNbSproutsPlaced = 0;
        $this->statNbSproutsPaid = 0;
        $this->statNbSproutsConverted = 0;
        $this->statNbGrowthPlaced = 0;
        $this->statNbGrowthPaid = 0;
    }

    public function resetPlayerActivation()
    {
        $this->stateActivationDirection = null;
        $this->stateActivatedBeforeCopyCardId = null;
        $this->stateActivatedAfterCopyCardId = null;
    }

    public function resetPlantedCards()
    {
        $this->firstPlantedCardId = null;
        $this->secondPlantedCardId = null;
    }

    public function addSoil(int $nbSoil)
    {
        $this->soilCount += $nbSoil;
    }

    public function removeSoil(int $nbSoil)
    {
        $this->soilCount -= $nbSoil;
        if ($this->soilCount < 0) {
            throw new \BgaUserException(clienttranslate('You do not have enough soil to do this action'));
        }
    }

    public function setSprout(int $nbSprout)
    {
        if ($this->gainedSprout != 0) {
            throw new \BgaSystemException('BUG! Gained sprouts is not zero!');
        }
        $this->gainedSprout = $nbSprout;
    }

    public function clearSprout()
    {
        $this->gainedSprout = 0;
    }

    public function setGrowth(int $nbGrowth)
    {
        if ($this->gainedGrowth != 0) {
            throw new \BgaSystemException('BUG! Gained growth is not zero!');
        }
        $this->gainedGrowth = $nbGrowth;
    }

    public function clearGrowth()
    {
        $this->gainedGrowth = 0;
    }

    public function setCompostFromHand(int $nbCompostFromHand)
    {
        if ($this->gainedCompostFromHand != 0) {
            throw new \BgaSystemException('BUG! Gained compost from hand is not zero!');
        }
        $this->gainedCompostFromHand = $nbCompostFromHand;
    }

    public function clearCompostFromHand()
    {
        $this->gainedCompostFromHand = 0;
    }

    public function getGainedCardIdList()
    {
        if ($this->gainedCardIdList === null) {
            return null;
        }
        if (strlen($this->gainedCardIdList) == 0) {
            return [];
        }
        return explode(',', $this->gainedCardIdList);
    }

    public function setGainedCardIdList(array $cardIds, bool $isDivided)
    {
        $this->gainedCardIdList = implode(',', $cardIds);
        $this->gainedCardIdDivided = $isDivided;
    }

    public function clearGainedCardIdList()
    {
        $this->gainedCardIdList = null;
        $this->gainedCardIdDivided = false;
    }

    public function isGainedCardIdListDivided()
    {
        return $this->gainedCardIdDivided;
    }
}

class PlayerStateMgr extends \BX\Action\BaseActionRowMgr
{
    public function __construct()
    {
        parent::__construct('player_state', \EA\PlayerState::class);
    }

    public function setup(array $playerIdArray)
    {
        foreach ($playerIdArray as $playerId) {
            $ps = $this->db->newRow();
            $ps->playerId = $playerId;
            $this->db->insertRow($ps);
        }
    }

    public function resetPlayersActivationNow()
    {
        foreach ($this->getAll() as $ps) {
            $ps->resetPlayerActivation();
            $this->db->updateRow($ps);
        }
    }

    public function resetPlantedCardsNow()
    {
        foreach ($this->getAll() as $ps) {
            $ps->resetPlantedCards();
            $this->db->updateRow($ps);
        }
    }

    public function getAll()
    {
        return $this->getAllRowsByKey();
    }

    public function getByPlayerId(int $playerId)
    {
        return $this->getRowByKey($playerId);
    }

    public function getAllPlayersSoilCount()
    {
        return array_map(fn ($ps) => $ps->soilCount, $this->getAll());
    }

    public function getPlayerSoilCount(int $playerId)
    {
        return $this->getRowByKey($playerId)->soilCount;
    }

    public function getPlayerActivationDirection(int $playerId)
    {
        return $this->getByPlayerId($playerId)->stateActivationDirection;
    }

    public function stateActivatedBeforeCopyCardId(int $playerId)
    {
        return $this->getByPlayerId($playerId)->stateActivatedBeforeCopyCardId;
    }

    public function stateActivatedAfterCopyCardId(int $playerId)
    {
        return $this->getByPlayerId($playerId)->stateActivatedAfterCopyCardId;
    }

    public function getPlayerReturnFromEventStateId(int $playerId)
    {
        return $this->getByPlayerId($playerId)->returnFromEventStateId;
    }

    public function getPlayerReturnFromConversionStateId(int $playerId)
    {
        return $this->getByPlayerId($playerId)->returnFromConversionStateId;
    }

    public function addPlayerPlantedCard(int $playerId, int $cardId)
    {
        $ps = $this->getByPlayerId($playerId);
        $ps->modifyAction();
        if ($ps->firstPlantedCardId === null) {
            $ps->firstPlantedCardId = $cardId;
            return;
        }
        if ($ps->secondPlantedCardId === null) {
            $ps->secondPlantedCardId = $cardId;
            return;
        }
        throw new \BgaSystemException("BUG! Player $playerId already has 2 planted card when adding cardId $cardId");
    }

    public function playerHasPlantedCard(int $playerId, int $cardId)
    {
        $ps = $this->getByPlayerId($playerId);
        if ($ps->firstPlantedCardId === $cardId) {
            return true;
        }
        if ($ps->secondPlantedCardId === $cardId) {
            return true;
        }
        return false;
    }

    public function playerPlantedCardIds(int $playerId)
    {
        $ps = $this->getByPlayerId($playerId);
        $ret = [];
        if ($ps->firstPlantedCardId !== null) {
            $ret[] = $ps->firstPlantedCardId;
        }
        if ($ps->secondPlantedCardId !== null) {
            $ret[] = $ps->secondPlantedCardId;
        }
        return $ret;
    }

    public function incStatNbCardsDrawn(int $playerId, int $count)
    {
        $ps = $this->getByPlayerId($playerId);
        $ps->modifyAction();
        $ps->statNbCardsDrawn += $count;
    }

    public function incStatNbCardsComposted(int $playerId, int $count)
    {
        $ps = $this->getByPlayerId($playerId);
        $ps->modifyAction();
        $ps->statNbCardsComposted += $count;
    }

    public function incStatNbSoilGained(int $playerId, int $count)
    {
        $ps = $this->getByPlayerId($playerId);
        $ps->modifyAction();
        $ps->statNbSoilGained += $count;
    }

    public function incStatNbCardsPaid(int $playerId, int $count)
    {
        $ps = $this->getByPlayerId($playerId);
        $ps->modifyAction();
        $ps->statNbCardsPaid += $count;
    }

    public function incStatNbSproutsPlaced(int $playerId, int $count)
    {
        $ps = $this->getByPlayerId($playerId);
        $ps->modifyAction();
        $ps->statNbSproutsPlaced += $count;
    }

    public function incStatNbSproutsPaid(int $playerId, int $count)
    {
        $ps = $this->getByPlayerId($playerId);
        $ps->modifyAction();
        $ps->statNbSproutsPaid += $count;
    }

    public function incStatNbSproutsConverted(int $playerId, int $count)
    {
        $ps = $this->getByPlayerId($playerId);
        $ps->modifyAction();
        $ps->statNbSproutsConverted += $count;
    }

    public function incStatNbGrowthPlaced(int $playerId, int $count)
    {
        $ps = $this->getByPlayerId($playerId);
        $ps->modifyAction();
        $ps->statNbGrowthPlaced += $count;
    }

    public function incStatNbGrowthPaid(int $playerId, int $count)
    {
        $ps = $this->getByPlayerId($playerId);
        $ps->modifyAction();
        $ps->statNbGrowthPaid += $count;
    }

    public function zombieReset(int $playerId)
    {
        $ps = $this->getByPlayerId($playerId);
        $ps->gainedSprout = 0;
        $ps->gainedGrowth = 0;
        $ps->gainedCompostFromHand = 0;
        $ps->clearGainedCardIdList();
        $ps->resetPlayerActivation();
        $ps->stateEventAfterCopyCardId = null;
        $ps->stateEventCurrentCardId = null;
        $ps->returnFromEventStateId = null;
        $ps->returnFromConversionStateId = null;
        $ps->firstPlantedCardId = null;
        $ps->secondPlantedCardId = null;
        $this->db->updateRow($ps);
    }

    public function debugRandomEndGame()
    {
        foreach ($this->getAll() as $playerState) {
            $playerState->soilCount = random_int(0, 40);
            $playerState->gainedSprout = 0;
            $playerState->gainedGrowth = 0;
            $playerState->gainedCompostFromHand = 0;
            $playerState->gainedCardIdList = null;
            $playerState->gainedCardIdDivided = false;
            $playerState->stateActivationDirection = null;
            $playerState->stateActivatedBeforeCopyCardId = null;
            $playerState->stateActivatedAfterCopyCardId = null;
            $playerState->stateEventBeforeCopyCardId = null;
            $playerState->stateEventAfterCopyCardId = null;
            $playerState->stateEventCurrentCardId = null;
            $playerState->returnFromEventStateId = null;
            $playerState->returnFromConversionStateId = null;
            $playerState->firstPlantedCardId = null;
            $playerState->secondPlantedCardId = null;
            $this->db->updateRow($playerState);
        }
    }
}
