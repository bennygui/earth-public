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
        return declare("ea.PaymentMgr", null, {
            setup(gamedatas) {
                this.pausedConfig = null;
                this.config = null;
                this.onUpdateTableau = null;
            },

            registerOnUpdateTableau(callback) {
                this.onUpdateTableau = callback
            },

            startPayment(onPaymentUpdated) {
                if (this.config !== null) {
                    this.onPaymentUpdated = onPaymentUpdated;
                    this.config.onPaymentUpdated();
                    gameui.tableauMgr.refreshCurrentPlayer().then(() => {
                        this.refreshTableau();
                    });
                    return;
                }
                this.config = {
                    onPaymentUpdated: onPaymentUpdated,
                    // Sprout
                    targetSproutCount: 0,
                    payedSproutCount: 0,
                    sproutCards: [],
                    // Growth
                    targetGrowthCount: 0,
                    payedGrowthCount: 0,
                    growthCards: [],
                    // Compost from hand
                    selectedCardIds: new Set(),
                    targetCompostFromHandCount: 0,
                }
                this.config.onPaymentUpdated();

                for (const cardId of gameui.handMgr.getCardHandCardIds()) {
                    const cardElem = gameui.cardMgr.getCardSelectionElementById(cardId);
                    if (gameui.elementWasSelectedBeforeRemoveAll(cardElem)) {
                        this.onSelectCardId(cardElem.dataset.cardId);
                    }
                }
                gameui.clearSelectedBeforeRemoveAll();
            },

            pause() {
                this.pausedConfig = this.config;
                this.config = null;
                gameui.tableauMgr.refreshCurrentPlayer().then(() => {
                    this.refreshTableau();
                });
            },

            resume() {
                this.config = this.pausedConfig;
                this.pausedConfig = null;
                gameui.tableauMgr.refreshCurrentPlayer().then(() => {
                    this.refreshTableau();
                });
            },

            stop() {
                this.config = null;
                this.pausedConfig = null;
                this.onUpdateTableau = null;
                gameui.tableauMgr.refreshCurrentPlayer().then(() => {
                    this.refreshTableau();
                });
            },

            resetPayment() {
                if (this.config === null) {
                    return;
                }
                // Compost from hand
                this.config.selectedCardIds = new Set();
                for (const cardId of gameui.handMgr.getCardHandCardIds()) {
                    const cardElem = gameui.cardMgr.getCardSelectionElementById(cardId);
                    gameui.removeSelected(cardElem);
                }
                // Sprout
                this.config.payedSproutCount = 0;
                for (const card of this.config.sproutCards) {
                    card.payed = 0;
                }
                // Growth
                this.config.payedGrowthCount = 0;
                for (const card of this.config.growthCards) {
                    card.payed = 0;
                }
                this.config.onPaymentUpdated();
                gameui.tableauMgr.refreshCurrentPlayer().then(() => {
                    this.refreshTableau();
                });
            },

            compostFromHandCardIds() {
                if (this.config === null) {
                    return [];
                }
                return Array.from(this.config.selectedCardIds);
            },

            compostFromHandCount() {
                if (this.config === null) {
                    return 0;
                }
                return this.config.selectedCardIds.size;
            },

            sproutCount() {
                if (this.config === null) {
                    return 0;
                }
                return this.config.payedSproutCount;
            },

            getPayedSproutForCardId(cardId) {
                if (this.config === null) {
                    return 0;
                }
                if (this.config.payedSproutCount == 0) {
                    return 0;
                }
                for (const card of this.config.sproutCards) {
                    if (cardId == card.cardId) {
                        return card.payed;
                    }
                }
                return 0;
            },

            getPayedSproutList() {
                if (this.config === null) {
                    return [];
                }
                const list = [];
                for (const card of this.config.sproutCards) {
                    if (card.payed > 0) {
                        list.push(card.cardId);
                        list.push(card.payed);
                    }
                }
                return list;
            },

            growthCount() {
                if (this.config === null) {
                    return 0;
                }
                return this.config.payedGrowthCount;
            },

            getPayedGrowthForCardId(cardId) {
                if (this.config === null) {
                    return 0;
                }
                if (this.config.payedGrowthCount == 0) {
                    return 0;
                }
                for (const card of this.config.growthCards) {
                    if (cardId == card.cardId) {
                        return card.payed;
                    }
                }
                return 0;
            },

            getPayedGrowthList() {
                if (this.config === null) {
                    return [];
                }
                const list = [];
                for (const card of this.config.growthCards) {
                    if (card.payed > 0) {
                        list.push(card.cardId);
                        list.push(card.payed);
                    }
                }
                return list;
            },

            addCompostFromHand(handCardIds, compostFromHandCount) {
                this.config.targetCompostFromHandCount = compostFromHandCount;
                if (this.config.targetCompostFromHandCount == 0) {
                    return;
                }
                for (const cardId of handCardIds) {
                    const cardElem = gameui.cardMgr.getCardSelectionElementById(cardId);
                    if (cardElem === null) {
                        continue;
                    }
                    gameui.addClickable(cardElem, () => this.onSelectCardId(cardId));
                }
                this.config.onPaymentUpdated();
            },

            addSprout(sproutCards, sproutCount) {
                this.config.targetSproutCount = sproutCount;
                if (this.config.sproutCards.length != sproutCards.length) {
                    this.config.sproutCards = sproutCards;
                    if (this.config.targetSproutCount == 0) {
                        return;
                    }
                    for (const card of this.config.sproutCards) {
                        card.count = parseInt(card.count);
                        card.payed = 0;
                    }
                }
                gameui.tableauMgr.refreshCurrentPlayer().then(() => {
                    this.refreshTableau();
                });
                this.config.onPaymentUpdated();
            },

            addGrowth(growthCards, growthCount) {
                this.config.targetGrowthCount = growthCount;
                if (this.config.growthCards.length != growthCards.length) {
                    this.config.growthCards = growthCards;
                    if (this.config.targetGrowthCount == 0) {
                        return;
                    }
                    for (const card of this.config.growthCards) {
                        card.count = parseInt(card.count);
                        card.payed = 0;
                    }
                }
                gameui.tableauMgr.refreshCurrentPlayer().then(() => {
                    this.refreshTableau();
                });
                this.config.onPaymentUpdated();
            },

            refreshTableau() {
                if (this.onUpdateTableau !== null) {
                    this.onUpdateTableau();
                }
                if (this.config === null) {
                    return;
                }
                // Sprout
                if (this.config.targetSproutCount - this.config.payedSproutCount > 0) {
                    for (const card of this.config.sproutCards) {
                        if (card.count - card.payed <= 0) {
                            continue;
                        }
                        const container = gameui.cardMgr.getCardSproutContainerElementById(card.cardId);
                        gameui.addClickable(container, () => {
                            this.config.payedSproutCount += 1;
                            card.payed += 1;
                            this.config.onPaymentUpdated();
                            gameui.tableauMgr.refreshCurrentPlayer().then(() => {
                                this.refreshTableau();
                            });
                        });
                    }
                }
                // Growth
                if (this.config.targetGrowthCount - this.config.payedGrowthCount > 0) {
                    for (const card of this.config.growthCards) {
                        if (card.count - card.payed <= 0) {
                            continue;
                        }
                        const container = gameui.cardMgr.getCardGrowthContainerElementById(card.cardId);
                        gameui.addClickable(container, () => {
                            this.config.payedGrowthCount += 1;
                            card.payed += 1;
                            this.config.onPaymentUpdated();
                            gameui.tableauMgr.refreshCurrentPlayer().then(() => {
                                this.refreshTableau();
                            });
                        });
                    }
                }
            },

            isPaymentValid() {
                if (this.config === null) {
                    return false;
                }
                return (
                    this.compostFromHandCount() == this.config.targetCompostFromHandCount
                    && this.sproutCount() == this.config.targetSproutCount
                    && this.growthCount() == this.config.targetGrowthCount
                );
            },

            onSelectCardId(cardId) {
                if (this.config === null) {
                    return;
                }
                cardId = parseInt(cardId);
                const cardElem = gameui.cardMgr.getCardSelectionElementById(cardId);
                if (this.config.selectedCardIds.has(cardId)) {
                    this.config.selectedCardIds.delete(cardId);
                    gameui.removeSelected(cardElem);
                } else {
                    this.config.selectedCardIds.add(cardId);
                    gameui.addSelected(cardElem);
                }
                this.config.onPaymentUpdated();
            },
        });
    });