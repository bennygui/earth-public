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
        return declare("ea.GainMgr", null, {
            setup(gamedatas) {
                this.pausedGainConfig = null;
                this.gainConfig = null;
                this.onUpdateTableau = null;
            },

            registerOnUpdateTableau(callback) {
                this.onUpdateTableau = callback;
            },

            getPlacedSproutForCardId(cardId) {
                if (this.gainConfig === null) {
                    return 0;
                }
                for (const card of this.gainConfig.sproutCards) {
                    if (cardId == card.cardId) {
                        return card.placed;
                    }
                }
                return 0;
            },

            getPlacedSprout() {
                if (this.gainConfig === null) {
                    return 0;
                }
                return this.gainConfig.placedSprout;
            },

            getPlacedSproutList() {
                const list = [];
                for (const card of this.gainConfig.sproutCards) {
                    if (card.placed > 0) {
                        list.push(card.cardId);
                        list.push(card.placed);
                    }
                }
                return list;
            },

            getPlacedGrowthForCardId(cardId) {
                if (this.gainConfig === null) {
                    return 0;
                }
                for (const card of this.gainConfig.growthCards) {
                    if (cardId == card.cardId) {
                        return card.placed;
                    }
                }
                return 0;
            },

            getPlacedGrowth() {
                if (this.gainConfig === null) {
                    return 0;
                }
                return this.gainConfig.placedGrowth;
            },

            getPlacedGrowthList() {
                const list = [];
                for (const card of this.gainConfig.growthCards) {
                    if (card.placed > 0) {
                        list.push(card.cardId);
                        list.push(card.placed);
                    }
                }
                return list;
            },

            getSelectedCompostFromHandCardIds() {
                if (this.gainConfig === null) {
                    return [];
                }
                return this.gainConfig.selectedCompostFromHandCardIds;
            },

            getSelectedHandChoosingCardIds() {
                if (this.gainConfig === null) {
                    return [];
                }
                return this.gainConfig.selectedHandChoosingCardIds;
            },

            isCompostFromHandValid() {
                if (this.gainConfig === null) {
                    return true;
                }
                return (this.gainConfig.selectedCompostFromHandCardIds.length <= this.gainConfig.gainedCompostFromHand);
            },

            isHandChoosingValid() {
                if (this.gainConfig === null) {
                    return true;
                }
                if (this.gainConfig.handChoosingCardIds.length == 0) {
                    return true;
                }
                return (this.gainConfig.selectedHandChoosingCardIds.length == 1);
            },

            hasSproutGainNoDirection() {
                return (this.gainConfig.gainedSprout > 0 && this.gainConfig.gainedCardIdList === null);
            },

            hasMaxedSproutGain() {
                return (this.gainConfig.placedSprout == this.gainConfig.gainedSprout);
            },

            hasPlacedNoGain() {
                if (this.gainConfig === null) {
                    return false;
                }
                if (this.gainConfig.gainedSprout == 0
                    && this.gainConfig.gainedGrowth == 0
                    && this.gainConfig.gainedCompostFromHand == 0
                    && this.gainConfig.handChoosingCardIds.length == 0) {
                    return false;
                }
                return (
                    this.gainConfig.placedSprout == 0
                    && this.gainConfig.placedGrowth == 0
                    && this.gainConfig.selectedCompostFromHandCardIds.length == 0
                    && this.gainConfig.selectedHandChoosingCardIds.length == 0
                );
            },

            hasMaxedGain() {
                if (this.gainConfig === null) {
                    return true;
                }
                return (
                    (this.gainConfig.placedSprout == this.gainConfig.gainedSprout || !this.couldGainMoreSprouts())
                    && (this.gainConfig.placedGrowth == this.gainConfig.gainedGrowth || !this.couldGainMoreGrowth())
                    && (this.gainConfig.selectedCompostFromHandCardIds.length == this.gainConfig.gainedCompostFromHand || !this.couldGainMoreCompostFromHand())
                    && (this.gainConfig.handChoosingCardIds.length == 0 || this.gainConfig.selectedHandChoosingCardIds.length == 1)
                );
            },

            couldGainMoreSprouts() {
                if (this.gainConfig === null) {
                    return false;
                }
                if (this.gainConfig.gainedSprout - this.gainConfig.placedSprout > 0) {
                    for (const card of this.gainConfig.sproutCards) {
                        if (card.count + card.placed >= card.max) {
                            continue;
                        }
                        if (this.gainConfig.gainedCardIdList !== null) {
                            if (this.gainConfig.gainedCardIdList.indexOf(card.cardId) < 0) {
                                continue;
                            }
                            if (this.gainConfig.isGainedCardIdListDivided) {
                                const maxGainedPerCard = Math.floor(this.gainConfig.gainedSprout / this.gainConfig.gainedCardIdList.length);
                                if (card.placed >= maxGainedPerCard) {
                                    continue;
                                }
                            }
                        }
                        return true;
                    }
                }
                return false;
            },

            couldGainMoreGrowth() {
                if (this.gainConfig === null) {
                    return false;
                }
                if (this.gainConfig.gainedGrowth - this.gainConfig.placedGrowth > 0) {
                    for (const card of this.gainConfig.growthCards) {
                        if (card.count + card.placed >= card.max) {
                            continue;
                        }
                        if (this.gainConfig.gainedCardIdList !== null) {
                            if (this.gainConfig.gainedCardIdList.indexOf(card.cardId) < 0) {
                                continue;
                            }
                            if (this.gainConfig.isGainedCardIdListDivided) {
                                const maxGainedPerCard = Math.floor(this.gainConfig.gainedGrowth / this.gainConfig.gainedCardIdList.length);
                                if (card.placed >= maxGainedPerCard) {
                                    continue;
                                }
                            }
                        }
                        return true;
                    }
                }
                return false;
            },

            couldGainMoreCompostFromHand() {
                if (this.gainConfig === null) {
                    return false;
                }
                if (this.gainConfig.selectedCompostFromHandCardIds.length < gameui.handMgr.getCardHandCardIds().length) {
                    return true;
                }
                return false;
            },

            setupGain(options) {
                if (this.gainConfig !== null) {
                    if ('onUpdateGain' in options) {
                        this.gainConfig.onUpdateGain = options.onUpdateGain;
                    }
                    if ('onAbilityCard' in options) {
                        this.gainConfig.onAbilityCard = options.onAbilityCard;
                    }
                    gameui.tableauMgr.refreshCurrentPlayer().then(() => {
                        this.refreshGain();
                    });
                    return;
                }
                this.gainConfig = Object.assign({
                    gainedSprout: 0,
                    placedSprout: 0,
                    sproutCards: [],
                    gainedGrowth: 0,
                    placedGrowth: 0,
                    growthCards: [],
                    gainedCardIdList: null,
                    isGainedCardIdListDivided: false,
                    gainedCompostFromHand: 0,
                    compostFromHandCardIds: [],
                    selectedCompostFromHandCardIds: [],
                    handChoosingCardIds: [],
                    selectedHandChoosingCardIds: [],
                    onUpdateGain: null,
                    abilityCardIds: [],
                    onAbilityCard: null
                },
                    options,
                );
                this.resetGain();
            },

            pause() {
                this.pausedGainConfig = this.gainConfig;
                this.gainConfig = null;
                this.resetGain();
            },

            resume() {
                this.gainConfig = this.pausedGainConfig;
                this.pausedGainConfig = null;
                gameui.tableauMgr.refreshCurrentPlayer().then(() => {
                    this.refreshGain();
                });
            },

            stop() {
                this.resetGain();
                this.gainConfig = null;
                this.pausedGainConfig = null;
                this.onUpdateTableau = null;
            },

            resetGain() {
                if (this.gainConfig !== null) {
                    // Sprout
                    this.gainConfig.gainedSprout = parseInt(this.gainConfig.gainedSprout);
                    this.gainConfig.placedSprout = 0;
                    for (const card of this.gainConfig.sproutCards) {
                        card.cardId = parseInt(card.cardId);
                        card.count = parseInt(card.count);
                        card.max = parseInt(card.max);
                        card.placed = 0;
                    }
                    // Growth
                    this.gainConfig.gainedGrowth = parseInt(this.gainConfig.gainedGrowth);
                    this.gainConfig.placedGrowth = 0;
                    for (const card of this.gainConfig.growthCards) {
                        card.cardId = parseInt(card.cardId);
                        card.count = parseInt(card.count);
                        card.max = parseInt(card.max);
                        card.placed = 0;
                    }
                    if (this.gainConfig.gainedCardIdList !== null) {
                        this.gainConfig.gainedCardIdList = this.gainConfig.gainedCardIdList.map((id) => parseInt(id));
                    }
                    // Compost from hand
                    this.gainConfig.gainedCompostFromHand = parseInt(this.gainConfig.gainedCompostFromHand);
                    this.gainConfig.selectedCompostFromHandCardIds = [];
                    for (const cardId of this.gainConfig.compostFromHandCardIds) {
                        const cardElem = gameui.cardMgr.getCardSelectionElementById(cardId);
                        gameui.removeSelected(cardElem);
                    }
                    // Choose from hand
                    this.gainConfig.selectedHandChoosingCardIds = [];
                    for (const cardId of this.gainConfig.handChoosingCardIds) {
                        const cardElem = gameui.cardMgr.getCardSelectionElementById(cardId);
                        gameui.removeSelected(cardElem);
                    }
                }
                gameui.tableauMgr.refreshCurrentPlayer().then(() => {
                    this.refreshGain();
                });
            },

            refreshGain() {
                if (this.onUpdateTableau !== null) {
                    this.onUpdateTableau();
                }

                if (this.gainConfig === null) {
                    return;
                }

                // Sprout
                if (this.gainConfig.gainedSprout - this.gainConfig.placedSprout > 0) {
                    for (const card of this.gainConfig.sproutCards) {
                        if (card.count + card.placed >= card.max) {
                            continue;
                        }
                        if (this.gainConfig.gainedCardIdList !== null) {
                            if (this.gainConfig.gainedCardIdList.indexOf(card.cardId) < 0) {
                                continue;
                            }
                            if (this.gainConfig.isGainedCardIdListDivided) {
                                const maxGainedPerCard = Math.floor(this.gainConfig.gainedSprout / this.gainConfig.gainedCardIdList.length);
                                if (card.placed >= maxGainedPerCard) {
                                    continue;
                                }
                            }
                        }
                        const container = gameui.cardMgr.getCardSproutContainerElementById(card.cardId);
                        gameui.addClickable(container, () => {
                            this.gainConfig.placedSprout += 1;
                            card.placed += 1;
                            gameui.tableauMgr.refreshCurrentPlayer().then(() => {
                                this.refreshGain();
                            });
                        });
                    }
                }

                // Growth
                if (this.gainConfig.gainedGrowth - this.gainConfig.placedGrowth > 0) {
                    for (const card of this.gainConfig.growthCards) {
                        if (card.count + card.placed >= card.max) {
                            continue;
                        }
                        if (this.gainConfig.gainedCardIdList !== null) {
                            if (this.gainConfig.gainedCardIdList.indexOf(card.cardId) < 0) {
                                continue;
                            }
                            if (this.gainConfig.isGainedCardIdListDivided) {
                                const maxGainedPerCard = Math.floor(this.gainConfig.gainedGrowth / this.gainConfig.gainedCardIdList.length);
                                if (card.placed >= maxGainedPerCard) {
                                    continue;
                                }
                            }
                        }
                        const container = gameui.cardMgr.getCardGrowthContainerElementById(card.cardId);
                        gameui.addClickable(container, () => {
                            this.gainConfig.placedGrowth += 1;
                            card.placed += 1;
                            gameui.tableauMgr.refreshCurrentPlayer().then(() => {
                                this.refreshGain();
                            });
                        });
                    }
                }

                // Compost from hand
                if (this.gainConfig.gainedCompostFromHand > 0) {
                    for (const cardId of this.gainConfig.compostFromHandCardIds) {
                        const cardElem = gameui.cardMgr.getCardSelectionElementById(cardId);
                        gameui.removeClickable(cardElem);
                        gameui.addClickable(cardElem, () => {
                            const index = this.gainConfig.selectedCompostFromHandCardIds.indexOf(cardId);
                            if (index >= 0) {
                                this.gainConfig.selectedCompostFromHandCardIds.splice(index, 1)
                                gameui.removeSelected(cardElem);
                            } else {
                                this.gainConfig.selectedCompostFromHandCardIds.push(cardId);
                                gameui.addSelected(cardElem);
                            }
                            this.refreshGain();
                        });
                        if (this.gainConfig.selectedCompostFromHandCardIds.indexOf(cardId) >= 0) {
                            gameui.addSelected(cardElem);
                        }
                    }
                }

                // Choose from hand
                if (this.gainConfig.handChoosingCardIds.length > 0) {
                    for (const cardId of this.gainConfig.handChoosingCardIds) {
                        const cardElem = gameui.cardMgr.getCardSelectionElementById(cardId);
                        gameui.removeClickable(cardElem);
                        gameui.addClickable(cardElem, () => {
                            const index = this.gainConfig.selectedHandChoosingCardIds.indexOf(cardId);
                            if (index >= 0) {
                                this.gainConfig.selectedHandChoosingCardIds.splice(index, 1)
                                gameui.removeSelected(cardElem);
                            } else {
                                this.gainConfig.selectedHandChoosingCardIds.push(cardId);
                                gameui.addSelected(cardElem);
                            }
                            this.refreshGain();
                        });
                        if (this.gainConfig.selectedHandChoosingCardIds.indexOf(cardId) >= 0) {
                            gameui.addSelected(cardElem);
                        }
                    }
                }

                // Ability
                for (const cardId of this.gainConfig.abilityCardIds) {
                    const cardElem = gameui.cardMgr.getCardSelectionElementById(cardId);
                    gameui.removeClickable(cardElem);
                    gameui.addClickable(cardElem, () => this.gainConfig.onAbilityCard(cardId));
                }

                if (this.gainConfig.onUpdateGain !== null) {
                    this.gainConfig.onUpdateGain();
                }
            },
        });
    });