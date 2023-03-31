<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * earth implementation : © Guillaume Benny bennygui@gmail.com
 * 
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 * 
 * earth.game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 *
 */


require_once(APP_GAMEMODULE_PATH . 'module/table/table.game.php');
require_once("modules/BX/php/DB.php");
require_once("modules/BX/php/Lock.php");
require_once("modules/BX/php/Action.php");
require_once("modules/BX/php/UI.php");
require_once("modules/BX/php/Player.php");
require_once("modules/BX/php/MultiActiveState.php");
require_once("modules/EA/php/Globals.php");
require_once("modules/EA/php/Player.php");
require_once("modules/EA/php/Card.php");
require_once("modules/EA/php/CardDefMgr.php");
require_once("modules/EA/php/LeafToken.php");
require_once("modules/EA/php/PlayerState.php");
require_once("modules/EA/php/PlayerScore.php");
require_once("modules/EA/php/GameState.php");
require_once("modules/EA/php/CardTag.php");
require_once("modules/EA/php/PlayerSeenLeafToken.php");
require_once("modules/EA/php/States/Common.php");
require_once("modules/EA/php/States/PlayerSetup.php");
require_once("modules/EA/php/States/GameNextPhase.php");
require_once("modules/EA/php/States/MainAction.php");
require_once("modules/EA/php/States/ActionPlant.php");
require_once("modules/EA/php/States/ActionCompost.php");
require_once("modules/EA/php/States/ActionWater.php");
require_once("modules/EA/php/States/ActionGrow.php");
require_once("modules/EA/php/States/ActionSoloFauna.php");
require_once("modules/EA/php/States/Ability.php");
require_once("modules/EA/php/States/Activation.php");
require_once("modules/EA/php/States/Event.php");
require_once("modules/EA/php/States/Conversion.php");
require_once("modules/EA/php/States/Confirm.php");
require_once("modules/EA/php/States/GameEnd.php");
require_once("modules/EA/php/States/CardTag.php");
require_once("modules/EA/php/States/SeenLeafToken.php");

require_once("modules/EA/php/Debug.php");

\BX\Action\BaseActionCommandNotifier::sendPrivateNotificationMessage(true);
\BX\Action\ActionRowMgrRegister::registerMgr('player', \EA\PlayerMgr::class);
\BX\Action\ActionRowMgrRegister::registerMgr('card', \EA\CardMgr::class);
\BX\Action\ActionRowMgrRegister::registerMgr('leaf_token', \EA\LeafTokenMgr::class);
\BX\Action\ActionRowMgrRegister::registerMgr('player_state', \EA\PlayerStateMgr::class);
\BX\Action\ActionRowMgrRegister::registerMgr('player_score', \EA\PlayerScoreMgr::class);
\BX\Action\ActionRowMgrRegister::registerMgr('game_state', \EA\GameStateMgr::class);
\BX\Action\ActionRowMgrRegister::registerMgr('card_tag', \EA\CardTagMgr::class);
\BX\Action\ActionRowMgrRegister::registerMgr('player_seen_leaf_token', \EA\PlayerSeenLeafTokenMgr::class);

\BX\Lock\Locker::registerTableColumn('card', 'card_id');
\BX\Lock\Locker::registerTableColumn('leaf_token', 'token_id');
\BX\Lock\Locker::registerTableColumn('player_state', 'player_id');
\BX\Lock\Locker::registerTableColumn('game_state', 'game_state_id');
\BX\Lock\Locker::registerTableColumn('card_tag', 'card_tag_id');
\BX\Lock\Locker::registerTableColumn('player_score', 'score_id');
\BX\Lock\Locker::registerTableColumn('player_seen_leaf_token', 'player_seen_leaf_token_id');

class earth extends Table
{
    use BX\Action\GameActionsTrait;
    use BX\MultiActiveState\GameStatesTrait;
    use EA\State\Common\GameStatesTrait;
    use EA\State\PlayerSetup\GameStatesTrait;
    use EA\State\GameNextPhase\GameStatesTrait;
    use EA\State\MainAction\GameStatesTrait;
    use EA\State\ActionPlant\GameStatesTrait;
    use EA\State\ActionCompost\GameStatesTrait;
    use EA\State\ActionWater\GameStatesTrait;
    use EA\State\ActionGrow\GameStatesTrait;
    use EA\State\ActionSoloFauna\GameStatesTrait;
    use EA\State\Ability\GameStatesTrait;
    use EA\State\Activation\GameStatesTrait;
    use EA\State\Event\GameStatesTrait;
    use EA\State\Conversion\GameStatesTrait;
    use EA\State\Confirm\GameStatesTrait;
    use EA\State\GameEnd\GameStatesTrait;
    use EA\State\CardTag\GameStatesTrait;
    use EA\State\SeenLeafToken\GameStatesTrait;

    use EA\Debug\GameStatesTrait;

    function __construct()
    {
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();
        \BX\Action\BaseActionCommandNotifier::setGame($this);
        $this->enableSendFaunaProgress();

        self::initGameStateLabels([
            GAME_OPTION_GAME_MODE => GAME_OPTION_GAME_MODE_ID,
        ]);
    }

    protected function getGameName()
    {
        // Used for translations and stuff. Please do not modify.
        return "earth";
    }

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame($players, $options = [])
    {
        $gameinfos = self::getGameinfos();

        \BX\Action\ActionRowMgrRegister::getMgr('player')->setup($players, $gameinfos['player_colors']);

        self::reattributeColorsBasedOnPreferences($players, $gameinfos['player_colors']);
        self::reloadPlayersBasicInfos();

        /************ Start the game initialization *****/

        // Init global values with their initial values
        //self::setGameStateInitialValue( 'my_first_global_variable', 0 );

        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        $this->initStat('player', STATS_PLAYER_NB_ACTION_PLANT, 0);
        $this->initStat('player', STATS_PLAYER_NB_ACTION_COMPOST, 0);
        $this->initStat('player', STATS_PLAYER_NB_ACTION_WATER, 0);
        $this->initStat('player', STATS_PLAYER_NB_ACTION_GROW, 0);

        $playerIdArray = $this->getPlayerIdArray();
        \BX\Action\ActionRowMgrRegister::getMgr('card')->setup($playerIdArray);
        \BX\Action\ActionRowMgrRegister::getMgr('leaf_token')->setup($playerIdArray);
        \BX\Action\ActionRowMgrRegister::getMgr('player_state')->setup($playerIdArray);
        \BX\Action\ActionRowMgrRegister::getMgr('game_state')->setup($playerIdArray);

        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();

        /************ End of the game initialization *****/
    }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = [];

        $playerId = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
        \BX\Action\ActionCommandMgr::apply($playerId);

        $playerIdArray = $this->getPlayerIdArray();

        $playerMgr = \BX\Action\ActionRowMgrRegister::getMgr('player');
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $leafTokenMgr = \BX\Action\ActionRowMgrRegister::getMgr('leaf_token');
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $playerScoreMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_score');
        $cardTagMgr = \BX\Action\ActionRowMgrRegister::getMgr('card_tag');
        $playerSeenLeafTokenMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_seen_leaf_token');

        $stateId = $this->gamestate->state_id();
        $gameHasEnded = ($stateId == STATE_GAME_ENDING_SCORE_ID
            || $stateId == STATE_GAME_END_ID
            || $playerScoreMgr->hasScore()
        );
        $result['players'] = $playerMgr->getAllForUI($gameHasEnded);
        $result['cards'] = $cardMgr->getAllVisibleForPlayer($playerId);
        $result['cardCounts'] = $cardMgr->getCardCountsUIEverything();
        $result['carddefs'] = \EA\CardDefMgr::getAll();
        $result['tableauPerPlayerId'] = $cardMgr->getAllPlayerTableauCards($playerIdArray, $playerId);
        $result['eventPerPlayerId'] = $cardMgr->getAllPlayerBoardEventCards($playerIdArray);
        $result['leafs'] = \EA\leafTokenToPlayerUI($leafTokenMgr->getAll(), $playerId);
        $result['soilCountByPlayerId'] = $playerStateMgr->getAllPlayersSoilCount();
        $result['playerActiveOrder'] = $gameStateMgr->playerIdsWithActiveOrder();
        $result['isLastRound'] = (!$gameHasEnded &&
            ($cardMgr->isTableauFilledForOneOfAllPlayers() || $gameStateMgr->isSoloLastTurn())
        );
        $result['scorepad'] = $playerScoreMgr->getScorepadUI();
        $result['mainActionId'] = $gameStateMgr->getActiveMainActionId();
        $result['activePlayerId'] = $gameStateMgr->activePlayerId();
        $result['cardTags'] = $cardTagMgr->getPlayerCardTags($playerId);
        $result['gaia'] = $gameStateMgr->getGaiaCount();
        $result['gaiaColorName'] = $gameStateMgr->getGaiaColorName();
        $result['gaiaEasySide'] = (isGameSoloBeginner() || isGameSoloMedium());
        $result['isGaiaTurn'] = $gameStateMgr->isGaiaTurn();
        $result['isGameModeBeginner'] = isGameModeBeginner();
        $result['newFaunaObjectivePlayerIds'] = $playerSeenLeafTokenMgr->newFaunaObjectivePlayerIdsForPlayerId($playerId);
        $result['faunaProgress'] = $this->getFaunaProgressForPlayers($playerIdArray, false);
        if (array_search($playerId, $playerIdArray) !== false) {
            $result['faunaProgress'][$playerId] = $this->getFaunaProgressForPlayers([$playerId], true)[$playerId];
        }

        return $result;
    }

    public function currentPlayerId()
    {
        return $this->getCurrentPlayerId();
    }

    public static function translate(string $text)
    {
        return self::_($text);
    }

    public function getPlayerIdArray()
    {
        $playersInfos = $this->loadPlayersBasicInfos();
        $playerIdArray = array_keys($playersInfos);
        usort($playerIdArray, function ($p1, $p2) use (&$playersInfos) {
            return ($playersInfos[$p1]['player_no'] <=> $playersInfos[$p2]['player_no']);
        });
        return $playerIdArray;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        if ($cardMgr->isTableauFilledForOneOfAllPlayers()) {
            return 100;
        }
        $max = 0;
        foreach ($this->getPlayerIdArray() as $playerId) {
            $max = max($max, count($cardMgr->getPlayerTableauCards($playerId, $playerId)));
        }
        return (100 * $max / (\EA\MAX_TABLEAU_SIZE * \EA\MAX_TABLEAU_SIZE));
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Zombie
    ////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
        
        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message. 
    */
    function zombieTurn($state, $playerId)
    {
        $this->undoAll($playerId);

        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');

        $statename = $state['name'];
        switch ($statename) {
            case STATE_PLAYER_SETUP:
                if ($cardMgr->getPlayerIslandCard($playerId) === null) {
                    $this->notifyAllPlayers(
                        \BX\Action\NTF_MESSAGE,
                        clienttranslate('The next actions are done automatically since the player left'),
                        []
                    );
                    $cardIds = [];
                    $handCards = $cardMgr->getPlayerHandCards($playerId);
                    foreach ($handCards as $card) {
                        if ($card->getCardDef()->isIsland()) {
                            $cardIds[] = $card->cardId;
                            break;
                        }
                    }
                    foreach ($handCards as $card) {
                        if ($card->getCardDef()->isClimate()) {
                            $cardIds[] = $card->cardId;
                            break;
                        }
                    }
                    foreach ($handCards as $card) {
                        if ($card->getCardDef()->isEcosystem()) {
                            $cardIds[] = $card->cardId;
                            break;
                        }
                    }
                    $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
                    $creator->add(new \EA\Actions\PlayerSetup\Choose($playerId, $cardIds));
                    $creator->commit();
                }
                $this->gamestate->setPlayerNonMultiactive($playerId, null);
                break;
            case STATE_MAIN_ACTION:
                $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
                $gameStateMgr->zombieEndMainAction();
                $this->gamestate->nextState();
                break;
            case STATE_ACTION_PLANT:
            case STATE_ACTION_COMPOST:
            case STATE_ACTION_WATER:
            case STATE_ACTION_GROW:
            case STATE_ACTIVATION:
            case STATE_GAME_ENDING_LAST_CHANCE:
                $this->gamestate->setPlayerNonMultiactive($playerId, null);
                break;
            case STATE_EVENT_SELECT_GAIN:
                $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
                $creator->add(new \BX\MultiActiveState\JumpPrivateStateActionCommand($playerId, $playerStateMgr->getPlayerReturnFromEventStateId($playerId)));
                $creator->commit();
                break;
            case STATE_EVENT_CHOOSE_CARD:
            case STATE_EVENT_SELECT_PAYMENT:
            default:
                throw new \BgaSystemException("BUG! Zombie mode not supported for this game state: " . $statename);
        }

        // Zombie player reset everything
        $playerStateMgr->zombieReset($playerId);
        // Previouly, Zombie player would discards all cards... A few reported
        // that they lost their cards, probably by being kicked out without
        // knowing so let's try not discarding cards...
        //$cardMgr->zombieDiscard($playerId);
        //$this->notifyAllPlayers(
        //    NTF_UPDATE_CARD_COUNTS,
        //    '',
        //    [
        //        'cardCounts' => $cardMgr->getCardCountsUIForPlayerId($playerId),
        //    ]
        //);
        //$this->notifyAllPlayers(
        //    NTF_UPDATE_CARD_COUNTS,
        //    '',
        //    [
        //        'cardCounts' => $cardMgr->getCardCountsUIDeckDiscard(),
        //    ]
        //);
    }

    ///////////////////////////////////////////////////////////////////////////////////:
    ////////// DB upgrade
    //////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */
    function upgradeTableDb($from_version)
    {
        if ($from_version <= 2301111925) {
            $sql = ""
                . "CREATE TABLE IF NOT EXISTS `DBPREFIX_player_seen_leaf_token` ("
                . "  `player_seen_leaf_token_id` int(10) unsigned NOT NULL AUTO_INCREMENT,"
                . "  `token_id` smallint(5) unsigned NOT NULL,"
                . "  `player_id` int(10) unsigned NULL,"
                . "  PRIMARY KEY (`player_seen_leaf_token_id`)"
                . ") ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            self::applyDbUpgradeToAllDB($sql);
            $tokenId = $this->getUniqueValueFromDB("SELECT MAX(token_id) FROM leaf_token");
            foreach (array_keys(self::getCollectionFromDB("SELECT player_id FROM player")) as $playerId) {
                $tokenId += 1;
                $sql = "INSERT INTO `DBPREFIX_leaf_token` "
                    . " (token_id, leaf_id, player_id, location_id, location_x, location_y, location_order) "
                    . " VALUES ($tokenId, 5, $playerId, 0, 5, NULL, NULL);";
                self::applyDbUpgradeToAllDB($sql);
            }
        }

        // $from_version <= 2302012234
        if (empty(self::getUniqueValueFromDB("SHOW COLUMNS FROM leaf_token LIKE 'private_location_id'"))) {
            try {
                self::applyDbUpgradeToAllDB("ALTER TABLE DBPREFIX_leaf_token ADD `private_location_id` smallint(5) unsigned NULL;");
            } catch (Exception $e) {
                self::applyDbUpgradeToAllDB("ALTER TABLE leaf_token ADD `private_location_id` smallint(5) unsigned NULL;");
            }
        }
        if (empty(self::getUniqueValueFromDB("SHOW COLUMNS FROM leaf_token LIKE 'private_location_x'"))) {
            try {
                self::applyDbUpgradeToAllDB("ALTER TABLE DBPREFIX_leaf_token ADD `private_location_x` smallint(5) NULL;");
            } catch (Exception $e) {
                self::applyDbUpgradeToAllDB("ALTER TABLE leaf_token ADD `private_location_x` smallint(5) NULL;");
            }
        }
        if (empty(self::getUniqueValueFromDB("SHOW COLUMNS FROM leaf_token LIKE 'private_location_y'"))) {
            try {
                self::applyDbUpgradeToAllDB("ALTER TABLE DBPREFIX_leaf_token ADD `private_location_y` smallint(5) NULL;");
            } catch (Exception $e) {
                self::applyDbUpgradeToAllDB("ALTER TABLE leaf_token ADD `private_location_y` smallint(5) NULL;");
            }
        }
        if (empty(self::getUniqueValueFromDB("SHOW COLUMNS FROM leaf_token LIKE 'private_location_order'"))) {
            try {
                self::applyDbUpgradeToAllDB("ALTER TABLE DBPREFIX_leaf_token ADD `private_location_order` smallint(5) unsigned NULL;");
            } catch (Exception $e) {
                self::applyDbUpgradeToAllDB("ALTER TABLE leaf_token ADD `private_location_order` smallint(5) unsigned NULL;");
            }
        }

        // $from_version <= 2302061822
        if (empty(self::getUniqueValueFromDB("SHOW COLUMNS FROM `player_state` LIKE 'stat_nb_cards_drawn'"))) {
            try {
                self::applyDbUpgradeToAllDB(
                    "ALTER TABLE `DBPREFIX_player_state` "
                        . " ADD `stat_nb_cards_drawn` int(10) unsigned NULL,"
                        . " ADD `stat_nb_cards_composted` int(10) unsigned NULL,"
                        . " ADD `stat_nb_soil_gained` int(10) unsigned NULL,"
                        . " ADD `stat_nb_cards_paid` int(10) unsigned NULL,"
                        . " ADD `stat_nb_sprouts_placed` int(10) unsigned NULL,"
                        . " ADD `stat_nb_sprouts_paid` int(10) unsigned NULL,"
                        . " ADD `stat_nb_sprouts_converted` int(10) unsigned NULL,"
                        . " ADD `stat_nb_growth_placed` int(10) unsigned NULL,"
                        . " ADD `stat_nb_growth_paid` int(10) unsigned NULL;"
                );
            } catch (Exception $e) {
                self::applyDbUpgradeToAllDB(
                    "ALTER TABLE `player_state`` "
                        . " ADD `stat_nb_cards_drawn` int(10) unsigned NULL,"
                        . " ADD `stat_nb_cards_composted` int(10) unsigned NULL,"
                        . " ADD `stat_nb_soil_gained` int(10) unsigned NULL,"
                        . " ADD `stat_nb_cards_paid` int(10) unsigned NULL,"
                        . " ADD `stat_nb_sprouts_placed` int(10) unsigned NULL,"
                        . " ADD `stat_nb_sprouts_paid` int(10) unsigned NULL,"
                        . " ADD `stat_nb_sprouts_converted` int(10) unsigned NULL,"
                        . " ADD `stat_nb_growth_placed` int(10) unsigned NULL,"
                        . " ADD `stat_nb_growth_paid` int(10) unsigned NULL;"
                );
            }
        }

        // $from_version <= 2302101618
        if (empty(self::getUniqueValueFromDB("SHOW COLUMNS FROM player_score LIKE 'extra_score'"))) {
            try {
                self::applyDbUpgradeToAllDB("ALTER TABLE DBPREFIX_player_score ADD `extra_score` varchar(256) NULL;");
            } catch (Exception $e) {
                self::applyDbUpgradeToAllDB("ALTER TABLE player_score ADD `extra_score` varchar(256) NULL;");
            }
        }
    }
}
