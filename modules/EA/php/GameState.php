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
require_once(__DIR__ . '/../../BX/php/Collection.php');

const GAME_STATE_ID = 0;

const GAME_PHASE_PLAYER_SETUP = 0;
const GAME_PHASE_CHOOSE_MAIN_ACTION = 1;
const GAME_PHASE_EXECUTE_MAIN_ACTION = 2;
const GAME_PHASE_CARD_ACTIVATION = 3;
const GAME_PHASE_END_GAME = 4;
const GAME_PHASE_END_TURN = 5;

class GameState extends \BX\Action\BaseActionRow
{
    /** @dbcol @dbkey */
    public $gameStateId;
    /** @dbcol */
    public $activeGamePhase;
    /** @dbcol */
    public $activeMainActionId;
    /** @dbcol */
    public $activePlayerId;
    /** @dbcol */
    public $isLastRound;
    /** @dbcol */
    public $gaiaSoil;
    /** @dbcol */
    public $gaiaSprout;
    /** @dbcol */
    public $gaiaGrowth;
    /** @dbcol */
    public $gaiaColorName;
    /** @dbcol */
    public $soloTurn;
    /** @dbcol */
    public $soloPlayerGainedSoil;
    /** @dbcol */
    public $soloPlayerGainedCard;
    /** @dbcol */
    public $gaiaDeckShuffle;
    /** @dbcol */
    public $lastGaiaFaunaLeft;
    /** @dbcol @dboptional */
    public $gameVersion;

    public function __construct()
    {
        $this->gameStateId = GAME_STATE_ID;
        $this->activeGamePhase = GAME_PHASE_PLAYER_SETUP;
        $this->activeMainActionId = null;
        $this->activePlayerId = null;
        $this->isLastRound = false;
        $this->gaiaSoil = 0;
        $this->gaiaSprout = 0;
        $this->gaiaGrowth = 0;
        $this->gaiaColorName = null;
        $this->soloTurn = 1;
        $this->soloPlayerGainedSoil = 0;
        $this->soloPlayerGainedCard = 0;
        $this->gaiaDeckShuffle = 0;
        $this->lastGaiaFaunaLeft = true;
        $this->gameVersion = null;
    }
}

class GaiaCountUI extends \BX\UI\UISerializable
{
    public $soilCount;
    public $sproutCount;
    public $growthCount;
    public $compostCount;
    public $roundCount;
    public $faunaCount;

    public function __construct(GameState $gs)
    {
        $this->soilCount = $gs->gaiaSoil;
        $this->sproutCount = $gs->gaiaSprout;
        $this->growthCount = $gs->gaiaGrowth;
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $this->compostCount = $cardMgr->getGaiaCompostCardCount();
        $this->roundCount = $gs->gaiaDeckShuffle * 6 + count($cardMgr->getGaiaDiscardCards());
        $leafTokenMgr = \BX\Action\ActionRowMgrRegister::getMgr('leaf_token');
        $this->faunaCount = $leafTokenMgr->countFaunaLeafOnFaunaFaunaByPlayerId(\EA\GAIA_PLAYER_ID);
    }
}

class GameStateMgr extends \BX\Action\BaseActionRowMgr
{
    public function __construct()
    {
        parent::__construct('game_state', \EA\GameState::class);
    }

    public function setup(array $playerIdArray)
    {
        $gs = $this->db->newRow();
        $gs->gameVersion = 1;
        if (isGameSolo()) {
            $playerMgr = \BX\Action\ActionRowMgrRegister::getMgr('player');
            $gs->gaiaColorName = $playerMgr->getFirstUnusedColorName();
            if (gameHasExpansionAbundance()) {
                $gs->gaiaSprout = 10;
            }
        }
        $this->db->insertRow($gs);
    }

    public function activeGamePhase()
    {
        return $this->getRowByKey(GAME_STATE_ID)->activeGamePhase;
    }

    public function activePlayerId()
    {
        if (isGameSolo() && $this->isGaiaTurn()) {
            return GAIA_PLAYER_ID;
        }
        return $this->getRowByKey(GAME_STATE_ID)->activePlayerId;
    }

    public function checkActivePlayerId($playerId)
    {
        if ($this->activePlayerId() != $playerId) {
            throw new \BgaUserException(clienttranslate("This action is only allowed for the active player"));
        }
    }

    public function checkInactivePlayerId($playerId)
    {
        if ($this->activePlayerId() == $playerId) {
            throw new \BgaUserException(clienttranslate("This action is only allowed if you are not the active player"));
        }
    }

    public function playerIdsInActiveOrder()
    {
        $playerMgr = \BX\Action\ActionRowMgrRegister::getMgr('player');
        $playerIdArray = $playerMgr->getAllPlayerIds();
        $activePlayerId = $this->activePlayerId();
        if ($activePlayerId !== null) {
            $playerIdArray = \BX\Collection\rotateValueToFront($playerIdArray, $activePlayerId);
        }
        return $playerIdArray;
    }

    public function playerIdsWithActiveOrder()
    {
        return array_flip($this->playerIdsInActiveOrder());
    }

    public function actionActivateMainAction(int $mainActionId)
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        $gs->modifyAction();
        $gs->activeMainActionId = $mainActionId;
    }

    public function activateMainActionAndExcutePhaseNow(int $mainActionId)
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        $gs->activeMainActionId = $mainActionId;
        $gs->activeGamePhase = GAME_PHASE_EXECUTE_MAIN_ACTION;
        $this->db->updateRow($gs);
    }

    public function getActiveMainActionId()
    {
        return $this->getRowByKey(GAME_STATE_ID)->activeMainActionId;
    }

    public function isLastRound()
    {
        return $this->getRowByKey(GAME_STATE_ID)->isLastRound;
    }

    public function actionActivateLastRound()
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        $gs->modifyAction();
        $gs->isLastRound = true;
    }

    public function activateNextGamePhase()
    {
        $gamePhase = $this->activeGamePhase();
        $clearMainAction = false;
        if ($gamePhase == GAME_PHASE_PLAYER_SETUP) {
            $this->resetSoloGainNow();
        }
        switch ($gamePhase) {
            case GAME_PHASE_CHOOSE_MAIN_ACTION:
                $gamePhase = GAME_PHASE_EXECUTE_MAIN_ACTION;
                break;
            case GAME_PHASE_EXECUTE_MAIN_ACTION:
                if (gameVersionHasFalltroughActivation()) {
                    // Falltrough if not planting
                    if ($this->getActiveMainActionId() == MAIN_ACTION_ID_PLANT) {
                        $gamePhase = GAME_PHASE_CARD_ACTIVATION;
                        break;
                    }
                } else {
                    if (!isGameSolo() || $this->getActiveMainActionId() != MAIN_ACTION_ID_SOLO_FAUNA) {
                        $gamePhase = GAME_PHASE_CARD_ACTIVATION;
                        break;
                    }
                    // Falltrough if when the action is Solo Fauna
                }
            case GAME_PHASE_CARD_ACTIVATION:
                if (gameHasExpansionAbundance()) {
                    $gamePhase = GAME_PHASE_END_TURN;
                    break;
                }
            case GAME_PHASE_PLAYER_SETUP:
            case GAME_PHASE_END_TURN:
                $gamePhase = GAME_PHASE_CHOOSE_MAIN_ACTION;
                $clearMainAction = true;
                $this->activateNextPlayerId();
                break;
            default:
                throw new \BgaSystemException("BUG! Unkown activeGamePhase: {$gamePhase}");
        }
        // Detect end of game
        if ($gamePhase == GAME_PHASE_CHOOSE_MAIN_ACTION && $this->isLastRound()) {
            $playerMgr = \BX\Action\ActionRowMgrRegister::getMgr('player');
            $playerIdArray = $playerMgr->getAllPlayerIds();
            if (isGameSolo() || $playerIdArray[0] == $this->activePlayerId()) {
                $gamePhase = GAME_PHASE_END_GAME;
            }
        }
        if ($gamePhase == GAME_PHASE_CHOOSE_MAIN_ACTION && isGameSolo()) {
            $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
            if (
                $this->isGaiaTurn()
                && $this->getGaiaDeckShuffle() >= 1
                && $cardMgr->getTopCardFromGaiaDeck() === null
            ) {
                $gamePhase = GAME_PHASE_END_GAME;
            }
        }
        $gs = $this->getRowByKey(GAME_STATE_ID);
        $gs->activeGamePhase = $gamePhase;
        if ($clearMainAction) {
            if (
                $gamePhase == GAME_PHASE_EXECUTE_MAIN_ACTION
                || $gamePhase == GAME_PHASE_CARD_ACTIVATION
            ) {
                throw new \BgaSystemException("BUG! Cannot clear main action with new game phase: $gamePhase");
            }
            $gs->activeMainActionId = null;
        }
        $this->db->updateRow($gs);
    }

    private function activateNextPlayerId()
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        $playerMgr = \BX\Action\ActionRowMgrRegister::getMgr('player');
        $playerIdArray = $playerMgr->getAllPlayerIds();
        $index = false;
        if ($gs->activePlayerId !== null) {
            $index = array_search($gs->activePlayerId, $playerIdArray);
        }
        if ($index === false) {
            $index = 0;
        } else {
            $index += 1;
            if ($index >= count($playerIdArray)) {
                $index = 0;
            }
        }
        $gs->activePlayerId = $playerIdArray[$index];
        $this->db->updateRow($gs);
    }

    public function getGaiaCount()
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        return new GaiaCountUI($gs);
    }

    public function getGaiaColorName()
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        return $gs->gaiaColorName;
    }

    public function activateNextSoloTurn()
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        $gs->soloTurn += 1;
        $maxSoloTurn = 1;
        if (isGameSoloBeginner()) {
            $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
            $gaiaTopCard = $cardMgr->getGaiaDiscardTopCard();
            if ($gaiaTopCard !== null) {
                $ability = $gaiaTopCard->getCardDef()->getFirstAbility();
                if ($ability->color == \EA\AB_COLOR_BROWN) {
                    $maxSoloTurn = 2;
                }
            }
        }
        if ($gs->soloTurn > $maxSoloTurn) {
            $gs->soloTurn = 0;
        }
        $gs->soloPlayerGainedSoil = 0;
        $gs->soloPlayerGainedCard = 0;
        $this->db->updateRow($gs);
    }

    public function resetSoloGainNow()
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        $gs->soloPlayerGainedSoil = 0;
        $gs->soloPlayerGainedCard = 0;
        $this->db->updateRow($gs);
    }

    public function isGaiaTurn()
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        return ($gs->soloTurn == 0);
    }

    public function isSoloPlayerSecondTurn()
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        return ($gs->soloTurn == 2);
    }

    public function isGaiaLastFaunaLeft()
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        return $gs->lastGaiaFaunaLeft;
    }

    public function modifyGaiaLastFaunaLeft()
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        $gs->modifyAction();
        $gs->lastGaiaFaunaLeft = true;
    }

    public function modifyGaiaLastFaunaRight()
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        $gs->modifyAction();
        $gs->lastGaiaFaunaLeft = false;
    }

    public function isSoloLastTurn()
    {
        if ($this->getGaiaDeckShuffle() < 1) {
            return false;
        }
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        return ($cardMgr->getTopCardFromGaiaDeck() == null);
    }

    public function getSoloPlayerGainedSoil()
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        return $gs->soloPlayerGainedSoil;
    }

    public function modifySoloPlayerGainedSoil(int $nbSoil)
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        $gs->modifyAction();
        $gs->soloPlayerGainedSoil += $nbSoil;
    }

    public function getSoloPlayerGainedCard()
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        return $gs->soloPlayerGainedCard;
    }

    public function modifySoloPlayerGainedCard(int $nbCard)
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        $gs->modifyAction();
        $gs->soloPlayerGainedCard += $nbCard;
    }

    public function modifyGaiaSprout(int $nbSprout)
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        $gs->modifyAction();
        $gs->gaiaSprout += $nbSprout;
    }

    public function getGaiaSprout()
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        return $gs->gaiaSprout;
    }

    public function modifyGaiaSoil(int $nbSoil)
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        $gs->modifyAction();
        $gs->gaiaSoil += $nbSoil;
    }

    public function modifyLooseGaiaSoil(int $nbSoil)
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        $gs->modifyAction();
        $gs->gaiaSoil -= $nbSoil;
    }

    public function getGaiaSoil()
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        return $gs->gaiaSoil;
    }

    public function modifyGaiaGrowth(int $nbGrowth)
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        $gs->modifyAction();
        $gs->gaiaGrowth += $nbGrowth;
    }

    public function getGaiaGrowth()
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        return $gs->gaiaGrowth;
    }

    public function getGaiaDeckShuffle()
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        return $gs->gaiaDeckShuffle;
    }

    public function modifyGaiaDeckShuffle()
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        $gs->modifyAction();
        $gs->gaiaDeckShuffle += 1;
    }

    public function zombieEndMainAction()
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        $gs->activeGamePhase = GAME_PHASE_CARD_ACTIVATION;
        $gs->activeMainActionId = null;
        $this->db->updateRow($gs);
    }

    public function debugRandomEndGame(array $playerIdArray)
    {
        shuffle($playerIdArray);
        $gs = $this->getRowByKey(GAME_STATE_ID);
        $gs->activeGamePhase = GAME_PHASE_END_GAME;
        $gs->activeMainActionId = null;
        $gs->activePlayerId = $playerIdArray[0];
        $gs->isLastRound = false;
        $this->db->updateRow($gs);
    }

    public function getGameVersion()
    {
        $gs = $this->getRowByKey(GAME_STATE_ID);
        $version = $gs->gameVersion;
        if ($version === null || $version < 0) {
            $version = 0;
        }
        return $version;
    }
}
