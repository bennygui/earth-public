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

namespace EA\State\GameEnd;

require_once(__DIR__ . '/.././../../BX/php/Action.php');
require_once(__DIR__ . '/.././../../BX/php/MultiActiveState.php');
require_once(__DIR__ . '/../Score.php');

trait GameStatesTrait
{
    public function stPreGameEndingLastChance()
    {
        if (gameHasExpansionAbundance()) {
            $this->gamestate->nextState('lastEndTurnEndGame');
        } else {
            $this->gamestate->nextState('basicEndGame');
        }
    }

    public function stGameEndingLastChance()
    {
        $this->gamestate->setAllPlayersMultiactive();
        $this->gamestate->initializePrivateStateForAllActivePlayers();
    }

    public function confirmEndGame()
    {
        \BX\Lock\Locker::lock();
        $playerId = $this->getCurrentPlayerId();
        $this->checkAction('confirmEndGame');
        \BX\Action\ActionCommandMgr::apply($playerId);
        $this->updateSeenFaunaObjective($playerId);

        // Commit if there are no undo so that there are no undo button
        $creator = \BX\Action\buildActionCommandCreator($playerId, \BX\Action\ActionCommandMgr::count($playerId) == 0);
        $creator->add(new \BX\MultiActiveState\ExitPrivateStateActionCommand($playerId));
        $creator->saveOrCommit();
    }

    public function stGameEndingScore()
    {
        // Commit all players
        $playerIdArray = $this->getPlayerIdArray();
        foreach ($playerIdArray as $playerId) {
            \BX\Action\ActionCommandMgr::commit($playerId);
        }

        $this->notifyAllPlayers(
            NTF_LAST_ROUND,
            '',
            [
                'isLastRound' => false,
            ]
        );
        $this->endGameScoring();
        $this->endGameStats();

        // End the game!
        $this->gamestate->nextState();
    }

    private function endGameScoring()
    {
        // Commit all players
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $playerIdArray = $gameStateMgr->playerIdsInActiveOrder();
        foreach ($playerIdArray as $playerId) {
            \BX\Action\ActionCommandMgr::commit($playerId);
        }

        // Needed if a player plays an event card and gets a Fauna objetive at the end of the game
        foreach ($playerIdArray as $playerId) {
            \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\Fauna\RevealPrivateFauna($playerId));
        }
        foreach ($playerIdArray as $playerId) {
            \BX\Action\ActionCommandMgr::saveOneAndCommit(new \EA\Actions\Fauna\MoveLeafTokenToFinalPosition($playerId));
        }
    
        $this->updateAllPlayersCardCounts();

        // Save scores
        \EA\Score\commitFinalScores();

        // Notification for scorepad
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $publicEcosystemCards = $cardMgr->getPublicEcosystemCards();
        $playerScoreMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_score');
        $scorepad = $playerScoreMgr->getScorepadUI();
        $this->notifyAllPlayers(
            NTF_SCOREPAD,
            '',
            [
                'scorepad' => $scorepad,
                'gameHasEnded' => true,
            ]
        );

        // Update player score and score_aux
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $tieBreakerPerPlayerId = [];
        foreach ($scorepad as $pad) {
            if ($pad->playerId == \EA\GAIA_PLAYER_ID) {
                continue;
            }
            // Tie breaker:
            // 0) Score
            // 1) Most soil remaining in their reserve
            // 2) Most cards in their hand
            // 3) Most growth
            // 4) Most sprouts
            // 5) Most composted cards
            $tieBreakerPerPlayerId[$pad->playerId] = [
                $pad->scoreTotal,
                $playerStateMgr->getPlayerSoilCount($pad->playerId),
                count($cardMgr->getPlayerHandCards($pad->playerId)),
                $pad->scoreGrowth,
                $pad->scoreSprout,
                $pad->scoreCompost,
            ];
        }
        $tieBreakerPlayerIdArray = $playerIdArray;
        usort($tieBreakerPlayerIdArray, function ($pId1, $pId2) use ($tieBreakerPerPlayerId) {
            if ($tieBreakerPerPlayerId[$pId1] > $tieBreakerPerPlayerId[$pId2]) {
                return 1;
            } else if ($tieBreakerPerPlayerId[$pId1] > $tieBreakerPerPlayerId[$pId2]) {
                return -1;
            } else {
                return 0;
            }
        });
        $tieBreakerPlayerOrder = array_flip($tieBreakerPlayerIdArray);
        if (isGameSolo()) {
            $playerId = null;
            $playerScore = null;
            $gaiaScore = null;
            foreach ($scorepad as $pad) {
                if ($pad->playerId == \EA\GAIA_PLAYER_ID) {
                    $gaiaScore = $pad->scoreTotal;
                } else {
                    $playerScore = $pad->scoreTotal;
                    $playerId = $pad->playerId;
                }
            }
            $creator = new \BX\Action\ActionCommandCreatorCommit($playerId);
            $creator->add(new \BX\Player\PlayerSetEndGameScoreActionCommand($playerId, $playerScore - $gaiaScore));
            $creator->commit();
        } else {
            foreach ($scorepad as $pad) {
                $creator = new \BX\Action\ActionCommandCreatorCommit($pad->playerId);
                $creator->add(new \BX\Player\PlayerSetEndGameScoreActionCommand($pad->playerId, $pad->scoreTotal, 1 + $tieBreakerPlayerOrder[$pad->playerId]));
                $creator->commit();
            }
        }

        // Text notification for each part
        foreach ($scorepad as $pad) {
            $playerName = null;
            if ($pad->playerId == \EA\GAIA_PLAYER_ID) {
                $playerName = clienttranslate('Gaia');
            } else {
                $playerName = $this->loadPlayersBasicInfos()[$pad->playerId]['player_name'];
            }
            $this->notifyAllPlayers(
                \BX\Action\NTF_MESSAGE,
                $pad->playerId == \EA\GAIA_PLAYER_ID
                    ? clienttranslate('${player_name} scores ${score} victory point(s) for their Earth cards')
                    : clienttranslate('${player_name} scores ${score} victory point(s) for their Tableau cards'),
                [
                    'player_name' => $playerName,
                    'score' => $pad->scoreCard,
                ]
            );
            if ($pad->playerId != \EA\GAIA_PLAYER_ID) {
                $this->notifyAllPlayers(
                    \BX\Action\NTF_MESSAGE,
                    clienttranslate('${player_name} scores ${score} victory point(s) for their Event card(s)'),
                    [
                        'player_name' => $playerName,
                        'score' => $pad->scoreEvent,
                    ]
                );
            }
            $this->notifyAllPlayers(
                \BX\Action\NTF_MESSAGE,
                clienttranslate('${player_name} scores ${score} victory point(s) for their Compost card(s)'),
                [
                    'player_name' => $playerName,
                    'score' => $pad->scoreCompost,
                ]
            );
            $this->notifyAllPlayers(
                \BX\Action\NTF_MESSAGE,
                clienttranslate('${player_name} scores ${score} victory point(s) for their Sprout(s)'),
                [
                    'player_name' => $playerName,
                    'score' => $pad->scoreSprout,
                ]
            );
            $this->notifyAllPlayers(
                \BX\Action\NTF_MESSAGE,
                clienttranslate('${player_name} scores ${score} victory point(s) for their Growth and Canopies'),
                [
                    'player_name' => $playerName,
                    'score' => $pad->scoreGrowth,
                ]
            );
            if ($pad->playerId != \EA\GAIA_PLAYER_ID) {
                $this->notifyAllPlayers(
                    \BX\Action\NTF_MESSAGE,
                    clienttranslate('${player_name} scores ${score} victory point(s) for their Terrain card(s) (brown abilities)'),
                    [
                        'player_name' => $playerName,
                        'score' => $pad->scoreTerrain,
                    ]
                );
            }
            if (!isGameModeBeginner() && $pad->playerId != \EA\GAIA_PLAYER_ID) {
                $playerEcosystemCard = $cardMgr->getPlayerEcosystemCard($pad->playerId);
                $this->notifyAllPlayers(
                    \BX\Action\NTF_MESSAGE,
                    clienttranslate('${player_name} scores ${score} victory point(s) for their personal Ecosystem card: ${cardName}'),
                    [
                        'player_name' => $playerName,
                        'score' => $pad->scorePlayerEcosystem,
                        'cardName' => $playerEcosystemCard->getCardDef()->name,
                        'i18n' => ['cardName'],
                    ]
                );
                $this->notifyAllPlayers(
                    \BX\Action\NTF_MESSAGE,
                    clienttranslate('${player_name} scores ${score} victory point(s) for the shared Ecosystem card: ${cardName}'),
                    [
                        'player_name' => $playerName,
                        'score' => $pad->scorePublicEcosystem1,
                        'cardName' => $publicEcosystemCards[0]->getCardDef()->name,
                        'i18n' => ['cardName'],
                    ]
                );
                $this->notifyAllPlayers(
                    \BX\Action\NTF_MESSAGE,
                    clienttranslate('${player_name} scores ${score} victory point(s) for the shared Ecosystem card: ${cardName}'),
                    [
                        'player_name' => $playerName,
                        'score' => $pad->scorePublicEcosystem2,
                        'cardName' => $publicEcosystemCards[1]->getCardDef()->name,
                        'i18n' => ['cardName'],
                    ]
                );
            }
            $this->notifyAllPlayers(
                \BX\Action\NTF_MESSAGE,
                clienttranslate('${player_name} scores ${score} victory point(s) on the Fauna board'),
                [
                    'player_name' => $playerName,
                    'score' => $pad->scoreFauna,
                ]
            );
            $this->notifyAllPlayers(
                \BX\Action\NTF_MESSAGE,
                clienttranslate('${player_name} scores a total of ${score} victory point(s)'),
                [
                    'player_name' => $playerName,
                    'score' => $pad->scoreTotal,
                ]
            );
        }
    }

    public function endGameStats()
    {
        $cardMgr = \BX\Action\ActionRowMgrRegister::getMgr('card');
        $playerStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_state');
        $leafTokenMgr = \BX\Action\ActionRowMgrRegister::getMgr('leaf_token');
        $gameStateMgr = \BX\Action\ActionRowMgrRegister::getMgr('game_state');
        $playerScoreMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_score');
        $playerExchangeMgr = \BX\Action\ActionRowMgrRegister::getMgr('player_exchange');

        foreach ($playerScoreMgr->getScorepadUI() as $pad) {
            if ($pad->playerId == \EA\GAIA_PLAYER_ID) {
                $playerIdArray = $gameStateMgr->playerIdsInActiveOrder();
                $playerId = $playerIdArray[0];
                $this->setStat(
                    $pad->scoreTotal,
                    STATS_PLAYER_GAIA_VP_TOTAL,
                    $playerId
                );
                $this->setStat(
                    $pad->scoreCard,
                    STATS_PLAYER_GAIA_VP_EARTH,
                    $playerId
                );
                $this->setStat(
                    count($cardMgr->getGaiaTableauCards()),
                    STATS_PLAYER_GAIA_NB_EARTH,
                    $playerId
                );
                $this->setStat(
                    $pad->scoreCompost,
                    STATS_PLAYER_GAIA_VP_COMPOST,
                    $playerId
                );
                $this->setStat(
                    $pad->scoreCompost,
                    STATS_PLAYER_GAIA_NB_COMPOST,
                    $playerId
                );
                $this->setStat(
                    $pad->scoreSprout,
                    STATS_PLAYER_GAIA_VP_SPROUT,
                    $playerId
                );
                $this->setStat(
                    $pad->scoreSprout,
                    STATS_PLAYER_GAIA_NB_SPROUT,
                    $playerId
                );
                $this->setStat(
                    $pad->scoreGrowth,
                    STATS_PLAYER_GAIA_VP_GROWTH,
                    $playerId
                );
                $this->setStat(
                    $gameStateMgr->getGaiaGrowth(),
                    STATS_PLAYER_GAIA_NB_GROWTH_AND_CANOPIES,
                    $playerId
                );
                $this->setStat(
                    \EA\Score\ScoreGrowthGaia::getNbCanopies($gameStateMgr->getGaiaGrowth()),
                    STATS_PLAYER_GAIA_NB_CANOPIES,
                    $playerId
                );
                $hasFaunaBonus = $leafTokenMgr->playerHasFaunaBoardTableauBonus(\EA\GAIA_PLAYER_ID);
                $faunaBonusScore = \EA\Score\ScoreFaunaBonus::getFaunaBonusScore();
                $this->setStat(
                    $pad->scoreFauna - ($hasFaunaBonus ? $faunaBonusScore : 0),
                    STATS_PLAYER_GAIA_VP_FAUNA_CARD,
                    $playerId
                );
                $this->setStat(
                    ($hasFaunaBonus ? $faunaBonusScore : 0),
                    STATS_PLAYER_GAIA_VP_FAUNA_TABLEAU,
                    $playerId
                );
                $this->setStat(
                    $pad->scoreFauna,
                    STATS_PLAYER_GAIA_VP_FAUNA_TOTAL,
                    $playerId
                );
                $this->setStat(
                    $gameStateMgr->getGaiaSoil(),
                    STATS_PLAYER_GAIA_NB_SOIL,
                    $playerId
                );
            } else {
                $this->setStat(
                    $pad->scoreTotal,
                    STATS_PLAYER_VP_TOTAL,
                    $pad->playerId
                );
                $this->setStat(
                    $pad->scoreCard,
                    STATS_PLAYER_VP_TABLEAU,
                    $pad->playerId
                );
                $this->setStat(
                    count($cardMgr->getPlayerTableauCards($pad->playerId, $pad->playerId)),
                    STATS_PLAYER_NB_TABLEAU,
                    $pad->playerId
                );
                $this->setStat(
                    $pad->scoreEvent,
                    STATS_PLAYER_VP_EVENT,
                    $pad->playerId
                );
                $this->setStat(
                    count($cardMgr->getPlayerBoardEventCards($pad->playerId)),
                    STATS_PLAYER_NB_EVENT,
                    $pad->playerId
                );
                $this->setStat(
                    $pad->scoreCompost,
                    STATS_PLAYER_VP_COMPOST,
                    $pad->playerId
                );
                $this->setStat(
                    $pad->scoreCompost,
                    STATS_PLAYER_NB_COMPOST,
                    $pad->playerId
                );
                $this->setStat(
                    $pad->scoreSprout,
                    STATS_PLAYER_VP_SPROUT,
                    $pad->playerId
                );
                $this->setStat(
                    $pad->scoreSprout,
                    STATS_PLAYER_NB_SPROUT,
                    $pad->playerId
                );
                $this->setStat(
                    $pad->scoreGrowth,
                    STATS_PLAYER_VP_GROWTH,
                    $pad->playerId
                );
                $this->setStat(
                    $cardMgr->getPlayerGrowthCount($pad->playerId),
                    STATS_PLAYER_NB_GROWTH_AND_CANOPIES,
                    $pad->playerId
                );
                $this->setStat(
                    $cardMgr->getPlayerCanopiesCount($pad->playerId),
                    STATS_PLAYER_NB_CANOPIES,
                    $pad->playerId
                );
                $this->setStat(
                    $pad->scoreTerrain,
                    STATS_PLAYER_VP_TERRAIN,
                    $pad->playerId
                );
                $this->setStat(
                    count(array_filter($cardMgr->getPlayerTableauCards($pad->playerId, $pad->playerId), fn ($c) => $c->getCardDef()->isTerrain())),
                    STATS_PLAYER_NB_TERRAIN,
                    $pad->playerId
                );
                if ($pad->scorePlayerEcosystem !== null) {
                    $this->setStat(
                        $pad->scorePlayerEcosystem,
                        STATS_PLAYER_VP_PLAYER_ECOSYSTEM,
                        $pad->playerId
                    );
                    $this->setStat(
                        $pad->scorePublicEcosystem1,
                        STATS_PLAYER_VP_FIRST_ECOSYSTEM,
                        $pad->playerId
                    );
                    $this->setStat(
                        $pad->scorePublicEcosystem2,
                        STATS_PLAYER_VP_SECOND_ECOSYSTEM,
                        $pad->playerId
                    );
                    $this->setStat(
                        $pad->scorePlayerEcosystem
                            + $pad->scorePublicEcosystem1
                            + $pad->scorePublicEcosystem2,
                        STATS_PLAYER_VP_TOTAL_ECOSYSTEM,
                        $pad->playerId
                    );
                }
                $hasFaunaBonus = $leafTokenMgr->playerHasFaunaBoardTableauBonus($pad->playerId);
                $faunaBonusScore = \EA\Score\ScoreFaunaBonus::getFaunaBonusScore();
                $this->setStat(
                    $pad->scoreFauna - ($hasFaunaBonus ? $faunaBonusScore : 0),
                    STATS_PLAYER_VP_FAUNA_CARD,
                    $pad->playerId
                );
                $this->setStat(
                    ($hasFaunaBonus ? $faunaBonusScore : 0),
                    STATS_PLAYER_VP_FAUNA_TABLEAU,
                    $pad->playerId
                );
                $this->setStat(
                    $pad->scoreFauna,
                    STATS_PLAYER_VP_FAUNA_TOTAL,
                    $pad->playerId
                );
                $this->setStat(
                    count($cardMgr->getPlayerHandCards($pad->playerId)),
                    STATS_PLAYER_NB_CARD_HAND,
                    $pad->playerId
                );
                $this->setStat(
                    $playerStateMgr->getPlayerSoilCount($pad->playerId),
                    STATS_PLAYER_NB_SOIL,
                    $pad->playerId
                );

                // Stats from player state
                $ps = $playerStateMgr->getByPlayerId($pad->playerId);
                $this->setStat(
                    $ps->statNbCardsDrawn ?? 0,
                    STATS_PLAYER_NB_CARDS_DRAWN_TOTAL,
                    $pad->playerId
                );
                $this->setStat(
                    $ps->statNbCardsComposted ?? 0,
                    STATS_PLAYER_NB_CARDS_COMPOSTED_TOTAL,
                    $pad->playerId
                );
                $this->setStat(
                    $ps->statNbSoilGained ?? 0,
                    STATS_PLAYER_NB_SOIL_GAINED_TOTAL,
                    $pad->playerId
                );
                $this->setStat(
                    $ps->statNbCardsPaid ?? 0,
                    STATS_PLAYER_NB_CARDS_PAID_FROM_COMPOST,
                    $pad->playerId
                );
                $this->setStat(
                    $ps->statNbSproutsPlaced ?? 0,
                    STATS_PLAYER_NB_SPROUTS_PLACED_TOTAL,
                    $pad->playerId
                );
                $this->setStat(
                    $ps->statNbSproutsPaid ?? 0,
                    STATS_PLAYER_NB_SPROUTS_PAID_TOTAL,
                    $pad->playerId
                );
                $this->setStat(
                    $ps->statNbSproutsConverted ?? 0,
                    STATS_PLAYER_NB_SPROUTS_CONVERTED_TOTAL,
                    $pad->playerId
                );
                $this->setStat(
                    $ps->statNbGrowthPlaced ?? 0,
                    STATS_PLAYER_NB_GROWTH_PLACED_TOTAL,
                    $pad->playerId
                );
                $this->setStat(
                    $ps->statNbGrowthPaid ?? 0,
                    STATS_PLAYER_NB_GROWTH_PAID_TOTAL,
                    $pad->playerId
                );
                // Abundance
                if (gameHasExpansionAbundance()) {
                    $this->setStat(
                        $ps->statNbSeedGained ?? 0,
                        STATS_PLAYER_NB_GAINED_SEEDS_TOTAL,
                        $pad->playerId
                    );
                    $this->setStat(
                        $ps->statNbLeafsConverted ?? 0,
                        STATS_PLAYER_NB_LEAFS_CONVERTED_TOTAL,
                        $pad->playerId
                    );
                    $this->setStat(
                        $ps->statNbGerminate ?? 0,
                        STATS_PLAYER_NB_GERMINATE_TOTAL,
                        $pad->playerId
                    );
                    // Stats for sprout exchange
                    $pe = $playerExchangeMgr->getBySameFromToPlayerId($pad->playerId);
                    $this->setStat(
                        $pe->sproutGive,
                        STATS_PLAYER_NB_SPROUTS_PLACED_ON_BOARD_BY_PLAYER,
                        $pad->playerId
                    );
                    $this->setStat(
                        $pe->sproutTake,
                        STATS_PLAYER_NB_SPROUTS_PLACED_FROM_BOARD,
                        $pad->playerId
                    );
                    $this->setStat(
                        array_sum(array_map(fn ($pe) => $pe->sproutGive, $playerExchangeMgr->getByToPlayerIdExceptSame($pad->playerId))),
                        STATS_PLAYER_NB_SPROUTS_PLACED_ON_BOARD_BY_OTHERS,
                        $pad->playerId
                    );
                }
            }
        }
    }
}
