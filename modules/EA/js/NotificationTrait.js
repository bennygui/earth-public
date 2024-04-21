/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * earth implementation : © Guillaume Benny bennygui@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */

var isDebug = window.location.host == 'studio.boardgamearena.com' || window.location.hash.indexOf('debug') > -1;
var debug = isDebug ? console.info.bind(window.console) : function () { };

define([
    "dojo",
    "dojo/_base/declare",
],
    function (dojo, declare) {
        return declare("ea.NotificationTrait", null, {
            FAUNA_DIALOG_DURATION: 30 * 1000,

            constructor() {
                // Format: ['notif', delay]
                if (this.notificationsToRegister === undefined) {
                    this.notificationsToRegister = [];
                }
                this.notificationsToRegister.push(['NTF_UPDATE_CARDS', -1]);
                this.notificationsToRegister.push(['NTF_PLAYER_GAIN_SOIL', -1]);
                this.notificationsToRegister.push(['NTF_PLAYER_PAY_SOIL', -1]);
                this.notificationsToRegister.push(['NTF_UPDATE_CARD_COUNTS', 1]);
                this.notificationsToRegister.push(['NTF_UPDATE_LEAF_TOKEN', -1]);
                this.notificationsToRegister.push(['NTF_MOVE_COMPOST_FROM_DECK', -1]);
                this.notificationsToRegister.push(['NTF_UPDATE_PLAYER_TABLEAU', -1]);
                this.notificationsToRegister.push(['NTF_DESTROY_COMPOST', -1]);
                this.notificationsToRegister.push(['NTF_UPDATE_PLAYER_EVENT', 1]);
                this.notificationsToRegister.push(['NTF_LAST_ROUND', null]);
                this.notificationsToRegister.push(['NTF_SCOREPAD', -1]);
                this.notificationsToRegister.push(['NTF_UPDATE_ACTIVE', null]);
                this.notificationsToRegister.push(['NTF_UPDATE_CARD_TAG', -1]);
                this.notificationsToRegister.push(['NTF_UPDATE_GAIA', -1]);
                this.notificationsToRegister.push(['NTF_IS_GAIA_TURN', null]);
                this.notificationsToRegister.push(['NTF_SEEN_FAUNA_OBJECTIVE', null]);
                this.notificationsToRegister.push(['NTF_UPDATE_FAUNA_PROGRESS', null]);
            },

            notif_UpdateCards(args) {
                debug('notif_UpdateCards');
                debug(args);
                const movements = [];
                let i = 0;
                for (const card of args.args.cards) {
                    movements.push(this.wait(i * 50).then(() => {
                        return this.updateOneCard(card);
                    }));
                    ++i;
                }
                Promise.all(movements).then(() => {
                    this.notifqueue.setSynchronousDuration(0);
                });
            },
            updateOneCard(card) {
                const cardId = card.cardId;
                let cardElem = this.cardMgr.getCardElementById(cardId);
                if (cardElem == null) {
                    // Card does not exist, create from deck unless noted otherwise
                    cardElem = this.cardMgr.createCardElement(card, true);
                    const elemCreationElem = gameui.getElementCreationElement();
                    elemCreationElem.appendChild(cardElem);
                    if (parseInt(card.locationId) == this.CARD_LOCATION_GAIA_DISCARD) {
                        this.gaiaBoardMgr.moveCardIdToGaiaDeck(cardId, true);
                    } else {
                        this.deckMgr.moveCardIdToDeck(cardId, true);
                    }
                }
                // Move the card. The called function should do nothing if already at right place
                let movement = Promise.resolve();
                switch (parseInt(card.locationId)) {
                    case this.CARD_LOCATION_DECK:
                        movement = this.deckMgr.moveCardIdToDeck(cardId).then(() => cardElem.remove());
                        break;
                    case this.CARD_LOCATION_DISCARD:
                    case this.CARD_LOCATION_BOX:
                        movement = this.deckMgr.moveCardIdToDiscard(cardId);
                        break;
                    case this.CARD_LOCATION_COMPOST:
                        movement = this.playerBoardMgr.moveCardIdToPlayerIdCompost(card.playerId, cardId);
                        break;
                    case this.CARD_LOCATION_HAND:
                        movement = this.handMgr.moveCardIdToHand(cardId, card.locationX);
                        break;
                    case this.CARD_LOCATION_TABLEAU:
                        // Sould be handled by tableau notification
                        break;
                    case this.CARD_LOCATION_PLAYER_BOARD:
                        if (card.locationId == gameui.CARD_LOCATION_PLAYER_BOARD && card.locationOrder === null) {
                            if (card.locationX == 0) {
                                this.tableauMgr.setIslandCardId(card.playerId, card.cardId);
                            } else if (card.locationX == 1) {
                                this.tableauMgr.setClimateCardId(card.playerId, card.cardId);
                            }
                        }
                        movement = this.playerBoardMgr.moveCardIdToPlayerIdBoard(card.playerId, cardId, card.locationX, card.locationOrder);
                        break;
                    case this.CARD_LOCATION_FAUNA_BOARD_FAUNA:
                    case this.CARD_LOCATION_FAUNA_BOARD_ECOSYSTEM:
                    case this.CARD_LOCATION_GAIA_COMPOST:
                    case this.CARD_LOCATION_GAIA_DECK:
                        // Should not happen
                        break;
                    case this.CARD_LOCATION_GAIA_TABLEAU:
                        movement = this.gaiaBoardMgr.moveCardIdToGaiaTableau(cardId);
                        break;
                    case this.CARD_LOCATION_GAIA_DISCARD:
                        movement = this.gaiaBoardMgr.moveCardIdToGaiaDiscard(cardId);
                        break;
                }
                return movement;
            },

            notif_UpdatePlayerTableau(args) {
                debug('notif_UpdatePlayerTableau');
                debug(args);
                const movement = this.tableauMgr.buildPlayerTableauFromCards(
                    args.args.playerId,
                    gameui.parseCompactCardList(args.args.tableauCards)
                );
                movement.then(() => {
                    this.notifqueue.setSynchronousDuration(0);
                });
            },

            notif_MoveCompostFromDeck(args) {
                debug('notif_MoveCompostFromDeck');
                debug(args);
                const movements = [];
                for (let i = 0; i < args.args.compostFromDeckCount; ++i) {
                    const cardElem = this.cardMgr.createEarthBackCardElement();
                    this.deckMgr.moveElementToDeck(cardElem, true);
                    movements.push(this.playerBoardMgr.moveElementToPlayerIdCompost(args.args.playerId, cardElem, false, 50));
                }
                Promise.all(movements).then(() => {
                    this.notifqueue.setSynchronousDuration(0);
                });
            },

            notif_DestroyCompost(args) {
                debug('notif_DestroyCompost');
                debug(args);
                const elemCreationElem = gameui.getElementCreationElement();
                const movements = [];
                for (let i = 0; i < args.args.nbCard; ++i) {
                    movements.push(this.wait(i * 50).then(() => {
                        const cardElem = this.cardMgr.createEarthBackCardElement();
                        elemCreationElem.appendChild(cardElem);
                        this.playerBoardMgr.moveElementToPlayerIdCompostNoRemove(args.args.playerId, cardElem, true);
                        movements.push(this.deckMgr.moveElementToDiscard(cardElem));
                    }));
                }
                Promise.all(movements).then(() => {
                    this.notifqueue.setSynchronousDuration(0);
                });
            },

            notif_PlayerGainSoil(args) {
                debug('notif_PlayerGainSoil');
                debug(args);
                const movement = [];
                if (this.playerBoardMgr.updateSoilCountForPlayerId(args.args.playerId, args.args.totalSoilCount)) {
                    movement.push(this.playerBoardMgr.animateSoilFromCardIdToPlayerId(args.args.gainSoilCount, args.args.fromCardId, args.args.playerId));
                    movement.push(this.playerBoardMgr.animateSoilFromMainActionIdToPlayerId(args.args.gainSoilCount, args.args.fromMainActionId, args.args.playerId));
                    if (args.args.fromCardId === null && args.args.fromMainActionId === null) {
                        movement.push(this.playerBoardMgr.animateSoilFromConversionToPlayerId(args.args.gainSoilCount, args.args.playerId));
                    }
                } else {
                    movement.push(Promise.resolve());
                }
                Promise.all(movement).then(() => this.notifqueue.setSynchronousDuration(0));
            },

            notif_PlayerPaySoil(args) {
                debug('notif_PlayerPaySoil');
                debug(args);
                const movement = [];
                if (this.playerBoardMgr.updateSoilCountForPlayerId(args.args.playerId, args.args.totalSoilCount)) {
                    movement.push(this.playerBoardMgr.animateSoilFromPlayerIdToCardId(args.args.paySoilCount, args.args.playerId, args.args.toCardId));
                } else {
                    movement.push(Promise.resolve());
                }
                Promise.all(movement).then(() => this.notifqueue.setSynchronousDuration(0));
            },

            notif_UpdateCardCounts(args) {
                debug('notif_UpdateCardCounts');
                debug(args);
                if (args.args.cardCounts.deckCount !== undefined) {
                    this.deckMgr.updateDeckCount(args.args.cardCounts.deckCount);
                }
                if (args.args.cardCounts.discardCount !== undefined) {
                    this.deckMgr.updateDiscardCount(args.args.cardCounts.discardCount);
                }
                this.deckMgr.updateDrawWarning(args.args.cardCounts.deckCount, args.args.cardCounts.discardCount);
                if (args.args.cardCounts.handCountByPlayerId !== undefined) {
                    for (const playerId in args.args.cardCounts.handCountByPlayerId) {
                        gameui.counters[playerId].hand.toValue(args.args.cardCounts.handCountByPlayerId[playerId]);
                    }
                }
                if (args.args.cardCounts.compostCountByPlayerId !== undefined) {
                    this.playerBoardMgr.updateCompostCount(args.args.cardCounts.compostCountByPlayerId);
                }
                if (args.args.cardCounts.gaiaDeckCount !== undefined) {
                    this.gaiaBoardMgr.updateGaiaDeckCount(args.args.cardCounts.gaiaDeckCount);
                }
            },

            notif_UpdateLeafToken(args) {
                debug('notif_UpdateLeafToken');
                debug(args);
                const leafToken = args.args.leafToken;
                // Move the token. The called function should do nothing if already at right place
                const movements = [];
                switch (parseInt(leafToken.locationId)) {
                    case this.LEAF_LOCATION_ID_PLAYER_BOARD:
                        if (leafToken.playerId == this.GAIA_PLAYER_ID) {
                            movements.push(this.gaiaBoardMgr.moveLeafTokenIdToGaiaBoard(
                                leafToken.tokenId,
                                leafToken.locationX
                            ));
                        } else {
                            movements.push(this.playerBoardMgr.moveLeafTokenIdToPlayerBoard(
                                leafToken.tokenId,
                                leafToken.playerId,
                                leafToken.locationX
                            ));
                        }
                        break;
                    case this.LEAF_LOCATION_ID_ACTION:
                        movements.push(this.playerBoardMgr.moveLeafTokenIdToPlayerAction(
                            leafToken.tokenId,
                            leafToken.playerId,
                            leafToken.locationX
                        ));
                        break;
                    case this.LEAF_LOCATION_ID_FAUNA_BOARD_FAUNA:
                        const isLeafTokenOnFaunaBoard = this.faunaBoardMgr.isLeafTokenOnFaunaBoard(leafToken.tokenId);
                        movements.push(this.faunaBoardMgr.moveLeafTokenIdToFaunaCard(
                            leafToken.tokenId,
                            leafToken.locationX,
                            leafToken.locationY,
                            leafToken.locationOrder,
                            args.args.playerActiveOrder
                        ));
                        if (!isLeafTokenOnFaunaBoard) {
                            movements.push(this.showFaunaObjectiveDialog(leafToken, args.log, args.args));
                        }
                        break;
                    case this.LEAF_LOCATION_ID_FAUNA_BOARD_TABLEAU_BONUS:
                        movements.push(this.faunaBoardMgr.moveLeafTokenIdToFaunaTableauBonus(leafToken.tokenId));
                        break;
                }
                Promise.all(movements).then(() => {
                    this.faunaBoardMgr.updatePlayerPanelFaunaCounters();
                    this.objectiveDetailMgr.refresh();
                    this.notifqueue.setSynchronousDuration(0);
                });
            },
            showFaunaObjectiveDialog(leafToken, log, logArgs) {
                if (this.isReadOnly() || this.isFastMode()) {
                    return Promise.resolve();
                }
                if (leafToken.playerId == this.GAIA_PLAYER_ID) {
                    return Promise.resolve();
                }
                // No need for final position except for solo mode where it's the only valid position
                if (leafToken.locationOrder !== null && !this.isGameSolo()) {
                    return Promise.resolve();
                }
                return new Promise((resolve, reject) => {
                    const dialog = new bx.ModalDialog('ea-leaf-dialog', {
                        title: _('Fauna card claimed'),
                        contents: '',
                        closeAction: 'hide',
                        closeWhenClickAnywhere: true,
                        onShow: () => {
                            const containerElem = document.getElementById('popin_ea-leaf-dialog_contents');
                            let zoom = Math.min(
                                document.body.offsetWidth * 0.7 / gameui.CARD_WIDTH,
                                (window.innerHeight * 0.6) / gameui.CARD_HEIGHT
                            );
                            if (zoom > 1) {
                                zoom = 1;
                            }
                            containerElem.style.setProperty('--ea-zoom', zoom);
                            containerElem.innerHTML = this.format_string_recursive(log, logArgs);
                            this.wait(this.FAUNA_DIALOG_DURATION).then(() => {
                                dialog.hide();
                            });
                        },
                        onHide: () => {
                            dialog.kill();
                            resolve()
                        },
                    });
                    dialog.show();
                })
            },

            notif_UpdatePlayerEvent(args) {
                debug('notif_UpdatePlayerEvent');
                debug(args);
                this.playerBoardMgr.buildPlayerEventFromCards(args.args.playerId, args.args.cards);
            },

            notif_LastRound(args) {
                debug('notif_LastRound');
                debug(args);
                this.displayLastRound(args.args.isLastRound);
            },

            notif_Scorepad(args) {
                debug('notif_Scorepad');
                debug(args);
                const movement = this.scoreMgr.activateScorepad(args.args.scorepad)
                movement.then(() => {
                    this.notifqueue.setSynchronousDuration(0);
                });
            },

            notif_UpdateActive(args) {
                debug('notif_UpdateActivePlayer');
                debug(args);
                if (args.args.activePlayerId !== null) {
                    this.playerPanelMgr.moveActivePlayerToken(args.args.activePlayerId);
                }
                this.playerBoardMgr.updateMainAction(args.args.nextMainActionId, args.args.activePlayerId);
            },

            notif_UpdateCardTag(args) {
                debug('notif_UpdateCardTag');
                debug(args);
                this.handMgr.updateCardTag(args.args.cardTags).then(() => {
                    this.notifqueue.setSynchronousDuration(0);
                });
            },

            notif_UpdateGaia(args) {
                debug('notif_UpdateGaia');
                debug(args);
                const movements = [];
                if (args.args.gaiaCount !== null) {
                    movements.push(this.gaiaBoardMgr.updateGaiaCount(args.args.gaiaCount));
                }
                if (args.args.gaiaTableauCards !== null) {
                    this.gaiaBoardMgr.buildGaiaTableauFromCards(args.args.gaiaTableauCards);
                }
                if (args.args.gaiaDiscardCards !== null) {
                    this.gaiaBoardMgr.buildGaiaDiscardFromCards(args.args.gaiaDiscardCards);
                }
                Promise.all(movements).then(() => this.notifqueue.setSynchronousDuration(0));
            },

            notif_IsGaiaTurn(args) {
                debug('notif_isGaiaTurn');
                debug(args);
                this.playerPanelMgr.setIsGaiaTurn(args.args.isGaiaTurn);
            },

            notif_SeenFaunaObjective(args) {
                debug('notif_SeenFaunaObjective');
                debug(args);
                this.playerPanelMgr.clearAllNewFaunaObjectiveIndicator();
            },

            notif_UpdateFaunaProgress(args) {
                debug('notif_UpdateFaunaProgress');
                debug(args);
                for (const playerId in args.args.faunaProgress) {
                    this.faunaProgress[playerId] = args.args.faunaProgress[playerId];
                }
                this.objectiveDetailMgr.refresh();
            },
        });
    });