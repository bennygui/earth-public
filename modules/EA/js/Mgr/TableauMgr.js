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
        return declare("ea.TableauMgr", null, {
            TABLEAU_PADDING: 10,
            MINI_TABLEAU_WIDTH: 10 + 2,
            MINI_TABLEAU_HEIGHT: 15 + 2,
            MINI_TABLEAU_PADDING: 3,

            setup(gamedatas) {
                this.tableauCardIds = {}
                this.inPlacementMode = false;
                this.selectedCardId = null;
                this.onSelectPosition = null;
                this.selectedPosX = null;
                this.selectedPosY = null;
                this.rebuildTableauFct = {};
                this.tableauCardWidth = null;
                this.tableauCardHeight = null;
                this.playerIslandCardId = {};
                this.playerClimateCardId = {};

                for (const playerId in gamedatas.players) {
                    this.tableauCardIds[playerId] = [];
                    this.playerIslandCardId[playerId] = null;
                    this.playerClimateCardId[playerId] = null;
                }

                for (const cardId in gamedatas.cards) {
                    const card = gamedatas.cards[cardId];
                    if (card.locationId == gameui.CARD_LOCATION_PLAYER_BOARD && card.locationOrder === null) {
                        if (card.locationX == 0) {
                            this.playerIslandCardId[card.playerId] = card.cardId;
                        } else if (card.locationX == 1) {
                            this.playerClimateCardId[card.playerId] = card.cardId;
                        }
                    }
                }

                for (const playerId in gamedatas.tableauPerPlayerId) {
                    this.rebuildTableauFct[playerId] = null;
                    const tableauCards = gamedatas.tableauPerPlayerId[playerId];
                    this.buildPlayerTableauFromCards(playerId, tableauCards, true);
                }
            },

            setIslandCardId(playerId, cardId) {
                this.playerIslandCardId[playerId] = cardId;
                for (const playerId in gameui.gamedatas.players) {
                    if (playerId in this.rebuildTableauFct) {
                        this.rebuildTableauFct[playerId](false, false);
                    }
                }
            },

            setClimateCardId(playerId, cardId) {
                this.playerClimateCardId[playerId] = cardId;
                for (const playerId in gameui.gamedatas.players) {
                    if (playerId in this.rebuildTableauFct) {
                        this.rebuildTableauFct[playerId](false, false);
                    }
                }
            },

            refreshCurrentPlayer() {
                if (!(gameui.player_id in this.rebuildTableauFct)) {
                    return Promise.resolve();
                }
                return this.rebuildTableauFct[gameui.player_id](false, true);
            },

            calcultateTableauSize() {
                if (this.tableauCardWidth !== null && this.tableauCardHeight !== null) {
                    return Promise.resolve();
                }
                return new Promise((resolve, reject) => {
                    const playerIdArray = Object.keys(gameui.gamedatas.players);
                    this.calcultateTableauSizeForPlayerId(playerIdArray, resolve);
                });
            },

            calcultateTableauSizeForPlayerId(playerIdArray, resolve) {
                if (playerIdArray.length == 0) {
                    resolve();
                    return;
                }
                const playerId = playerIdArray.pop();
                const tableauElem = this.getPlayerIdTableauElem(playerId);
                if (tableauElem === null) {
                    this.calcultateTableauSizeForPlayerId(playerIdArray, resolve);
                    return;
                }

                const cardSizeElem = gameui.cardMgr.createCardElementForSize();
                cardSizeElem.style.position = 'absolute';
                cardSizeElem.style.top = '0px';
                cardSizeElem.style.left = '0px';
                cardSizeElem.style.opacity = 0;
                const wasHidden = tableauElem.parentElement.classList.contains('bx-hidden');
                tableauElem.parentElement.classList.remove('bx-hidden');
                tableauElem.parentElement.appendChild(cardSizeElem);
                setTimeout(() => {
                    if (cardSizeElem.offsetWidth > 0) {
                        this.tableauCardWidth = cardSizeElem.offsetWidth;
                    }
                    if (cardSizeElem.offsetHeight > 0) {
                        this.tableauCardHeight = cardSizeElem.offsetHeight;
                    }
                    cardSizeElem.remove();
                    if (wasHidden) {
                        tableauElem.parentElement.classList.add('bx-hidden');
                    }
                    if (this.tableauCardWidth !== null && this.tableauCardHeight !== null) {
                        resolve();
                    } else {
                        this.calcultateTableauSizeForPlayerId(playerIdArray, resolve);
                    }
                }, 1);
            },

            enablePlacementMode(onSelectPosition) {
                this.inPlacementMode = true;
                this.selectedCardId = null;
                this.onSelectPosition = onSelectPosition;
                this.selectedPosX = null;
                this.selectedPosY = null;
                if (this.rebuildTableauFct[gameui.player_id] !== null) {
                    this.rebuildTableauFct[gameui.player_id](true);
                }
            },

            disablePlacementMode() {
                this.inPlacementMode = false;
                this.selectedCardId = null;
                this.onSelectPosition = null;
                this.selectedPosX = null;
                this.selectedPosY = null;
            },

            setSelectedCardId(cardId) {
                this.selectedCardId = cardId;
                if (cardId === null) {
                    this.selectedPosX = null;
                    this.selectedPosY = null;
                }
                if (this.onSelectPosition !== null) {
                    this.onSelectPosition(this.selectedPosX, this.selectedPosY);
                }
            },

            getSelectedCardId() {
                return this.selectedCardId;
            },

            getPosX() {
                return this.selectedPosX;
            },

            getPosY() {
                return this.selectedPosY;
            },

            getPlayerIdTableauElem(playerId) {
                return document.querySelector('#ea-area-player-' + playerId + ' .ea-area-player-tableau');
            },

            getTableauCardIds(playerId) {
                return this.tableauCardIds[playerId];
            },

            buildPlayerTableauFromCards(playerId, tableauCards, isInstantaneous = false) {
                this.rebuildTableauFct[playerId] = (isInstantaneous, isRefreshingCurrentPlayer = false) => {
                    return this.calcultateTableauSize().then(() => {
                        return this._buildPlayerTableauFromCards(playerId, tableauCards, isInstantaneous, isRefreshingCurrentPlayer);
                    });
                };
                return this.rebuildTableauFct[playerId](isInstantaneous);
            },

            _buildPlayerTableauFromCards(playerId, tableauCards, isInstantaneous, isRefreshingCurrentPlayer) {
                this.tableauCardIds[playerId] = tableauCards.filter((c) => c.cardId !== null).map((c) => c.cardId);
                if (this.tableauCardIds[playerId].length > 0) {
                    gameui.playerPanelMgr.showTableauRelatedPills();
                }
                this.updatePlayerTableauCounters(playerId, tableauCards, isInstantaneous);
                this.updatePlayerTableauOverview(playerId, tableauCards);

                const tableauElem = this.getPlayerIdTableauElem(playerId);
                gameui.removeClickableFromElementAndChilds(tableauElem);
                gameui.removeSelectedFromElementAndChilds(tableauElem);
                for (const elem of Array.from(tableauElem.children)) {
                    elem.classList.remove('ea-placement-compost');
                    if (
                        !elem.classList.contains('ea-card-container')
                        &&
                        !elem.classList.contains('ea-scoring-lines')
                    ) {
                        elem.remove();
                    }
                }

                let minX = 0;
                let maxX = 0;
                let minY = 0;
                let maxY = 0;
                for (const card of tableauCards) {
                    if (playerId == gameui.player_id && card.cardId === null) {
                        if (!this.inPlacementMode) {
                            continue;
                        }
                        if (this.selectedCardId === null) {
                            continue;
                        }
                    }
                    minX = Math.min(minX, card.locationX);
                    maxX = Math.max(maxX, card.locationX);
                    minY = Math.min(minY, card.locationY);
                    maxY = Math.max(maxY, card.locationY);
                }
                const gridWidth = Math.abs(maxX - minX) + 1;
                const gridHeight = Math.abs(maxY - minY) + 1;
                tableauElem.style.width = (gridWidth * this.tableauCardWidth + this.TABLEAU_PADDING * (gridWidth - 1)) + 'px';
                tableauElem.style.height = (gridHeight * this.tableauCardHeight + this.TABLEAU_PADDING * (gridHeight - 1)) + 'px';
                tableauElem.parentElement.classList.remove('bx-hidden');

                const movements = [];
                let hasCards = false;
                for (const card of tableauCards) {
                    const y = card.locationY - minY;
                    const x = card.locationX - minX;
                    const setPos = (cardElem, placement) => {
                        cardElem.style.position = 'absolute';
                        cardElem.style.top = (y * this.tableauCardHeight + y * this.TABLEAU_PADDING) + 'px';
                        cardElem.style.left = (x * this.tableauCardWidth + x * this.TABLEAU_PADDING) + 'px';
                        if (card.cardId !== null) {
                            this.adjustCardSprouts(
                                card.cardId,
                                parseInt(card.sproutCount)
                                + gameui.gainMgr.getPlacedSproutForCardId(card.cardId)
                                - gameui.paymentMgr.getPayedSproutForCardId(card.cardId),
                                isInstantaneous
                            );
                            this.adjustCardGrowth(
                                card.cardId,
                                parseInt(card.growthCount)
                                + gameui.gainMgr.getPlacedGrowthForCardId(card.cardId)
                                - gameui.paymentMgr.getPayedGrowthForCardId(card.cardId),
                                isInstantaneous
                            );
                        }
                        if (placement) {
                            if (this.selectedPosX === card.locationX && this.selectedPosY === card.locationY) {
                                gameui.addSelected(cardElem);
                                if (card.cardId !== null) {
                                    cardElem.classList.add('ea-placement-compost');
                                }
                            }
                            gameui.addClickable(cardElem, () => {
                                if (this.selectedPosX === card.locationX && this.selectedPosY === card.locationY) {
                                    this.selectedPosX = null;
                                    this.selectedPosY = null;
                                    this.rebuildTableauFct[gameui.player_id](isInstantaneous);
                                    this.onSelectPosition(null, null)
                                } else {
                                    this.selectedPosX = card.locationX;
                                    this.selectedPosY = card.locationY;
                                    this.rebuildTableauFct[gameui.player_id](isInstantaneous);
                                    this.onSelectPosition(card.locationX, card.locationY)
                                }
                            });
                        }
                    }
                    if (card.cardId === null) {
                        if (playerId == gameui.player_id && this.inPlacementMode) {
                            if (this.selectedCardId !== null) {
                                const cardElem = gameui.cardMgr.createCardElementForSelectionFromCardId(this.selectedCardId);
                                cardElem.classList.add('ea-card-placement');
                                tableauElem.appendChild(cardElem);
                                setPos(cardElem, true);
                                hasCards = true;
                            }
                        } else if (playerId != gameui.player_id) {
                            const cardElem = gameui.cardMgr.createEarthBackCardElement(true);
                            tableauElem.appendChild(cardElem);
                            setPos(cardElem, false);
                            hasCards = true;
                        }
                    } else {
                        const canPlantOver = playerId == gameui.player_id && this.inPlacementMode && card.canPlantOver && this.selectedCardId !== null;
                        const cardElem = gameui.cardMgr.getCardElementById(card.cardId);
                        if (cardElem === null) {
                            const cardElem = gameui.cardMgr.createCardElement(card, true);
                            tableauElem.appendChild(cardElem);
                            setPos(cardElem, canPlantOver);
                            hasCards = true;
                        } else {
                            let pos = null;
                            if (cardElem.parentElement != tableauElem) {
                                pos = {
                                    y: (y * this.tableauCardHeight + y * this.TABLEAU_PADDING) + 'px',
                                    x: (x * this.tableauCardWidth + x * this.TABLEAU_PADDING) + 'px',
                                };
                            }
                            movements.push(
                                gameui.wait(300, isInstantaneous || isRefreshingCurrentPlayer).then(() => gameui.slide(cardElem, tableauElem, {
                                    phantom: false,
                                    pos: pos,
                                    isInstantaneous: isInstantaneous,
                                }).then(() => {
                                    setPos(cardElem, canPlantOver);
                                })
                                )
                            );
                            hasCards = true;
                        }
                    }
                }
                if (!hasCards) {
                    tableauElem.style.width = '0px';
                    tableauElem.style.height = '0px';
                    tableauElem.parentElement.classList.add('bx-hidden');
                }
                const emptyCards = tableauCards.filter((c) => c.cardId === null);
                if (emptyCards.length == 1
                    && this.selectedCardId !== null
                    && this.inPlacementMode
                    && this.selectedPosX === null
                    && this.selectedPosY === null
                ) {
                    const card = emptyCards[0];
                    this.selectedPosX = card.locationX;
                    this.selectedPosY = card.locationY;
                    this.rebuildTableauFct[gameui.player_id](isInstantaneous);
                    this.onSelectPosition(card.locationX, card.locationY)
                }
                return Promise.all(movements);
            },

            updatePlayerTableauCounters(playerId, tableauCards, isInstantaneous) {
                let sprout = 0;
                let sproutMax = 0;
                let growth = 0;
                let growthMax = 0;
                const cardType = {};
                cardType[gameui.CARD_TYPE_TREE] = 0;
                cardType[gameui.CARD_TYPE_HERB] = 0;
                cardType[gameui.CARD_TYPE_MUSHROOM] = 0;
                cardType[gameui.CARD_TYPE_BUSH] = 0;
                cardType[gameui.CARD_TYPE_JOKER] = 0;
                cardType[gameui.CARD_TYPE_TERRAIN] = 0;
                let habitatSunny = 0;
                let habitatWet = 0;
                let habitatRocky = 0;
                let habitatCold = 0;
                if (this.playerIslandCardId[playerId] !== null) {
                    const def = gameui.gamedatas.carddefs[this.playerIslandCardId[playerId]];
                    habitatSunny += (def.isHabitatSunny ? 1 : 0);
                    habitatWet += (def.isHabitatWet ? 1 : 0);
                    habitatRocky += (def.isHabitatRocky ? 1 : 0);
                    habitatCold += (def.isHabitatCold ? 1 : 0);
                }
                if (this.playerClimateCardId[playerId] !== null) {
                    const def = gameui.gamedatas.carddefs[this.playerClimateCardId[playerId]];
                    habitatSunny += (def.isHabitatSunny ? 1 : 0);
                    habitatWet += (def.isHabitatWet ? 1 : 0);
                    habitatRocky += (def.isHabitatRocky ? 1 : 0);
                    habitatCold += (def.isHabitatCold ? 1 : 0);
                }
                for (const card of tableauCards) {
                    if (card.cardId === null) {
                        continue;
                    }
                    const def = gameui.gamedatas.carddefs[card.cardId];

                    cardType[def.type] += 1;

                    habitatSunny += (def.isHabitatSunny ? 1 : 0);
                    habitatWet += (def.isHabitatWet ? 1 : 0);
                    habitatRocky += (def.isHabitatRocky ? 1 : 0);
                    habitatCold += (def.isHabitatCold ? 1 : 0);

                    sprout += parseInt(card.sproutCount);
                    if (def.sproutMax !== null) {
                        sproutMax += parseInt(def.sproutMax);
                    }

                    growth += parseInt(card.growthCount);
                    if (def.growthMax !== null) {
                        growthMax += parseInt(def.growthMax);
                    }
                }
                gameui.counters[playerId].sprout.toValue([sprout, sproutMax], isInstantaneous);
                gameui.counters[playerId].growth.toValue([growth, growthMax], isInstantaneous);

                gameui.counters[playerId].cardTree.toValue(cardType[gameui.CARD_TYPE_TREE], isInstantaneous);
                gameui.counters[playerId].cardHerb.toValue(cardType[gameui.CARD_TYPE_HERB], isInstantaneous);
                gameui.counters[playerId].cardMushroom.toValue(cardType[gameui.CARD_TYPE_MUSHROOM], isInstantaneous);
                gameui.counters[playerId].cardBush.toValue(cardType[gameui.CARD_TYPE_BUSH], isInstantaneous);
                gameui.counters[playerId].cardJoker.toValue(cardType[gameui.CARD_TYPE_JOKER], isInstantaneous);
                gameui.counters[playerId].cardTerrain.toValue(cardType[gameui.CARD_TYPE_TERRAIN], isInstantaneous);

                gameui.counters[playerId].habitatSunny.toValue(habitatSunny, isInstantaneous);
                gameui.counters[playerId].habitatWet.toValue(habitatWet, isInstantaneous);
                gameui.counters[playerId].habitatRocky.toValue(habitatRocky, isInstantaneous);
                gameui.counters[playerId].habitatCold.toValue(habitatCold, isInstantaneous);
            },

            updatePlayerTableauOverview(playerId, tableauCards) {
                const overviewElem = document.querySelector('#overall_player_board_' + playerId + ' .ea-player-panel-tableau-overview');
                overviewElem.innerHTML = '';
                let minX = 0;
                let maxX = 0;
                let minY = 0;
                let maxY = 0;
                for (const card of tableauCards) {
                    if (card.cardId === null) {
                        continue;
                    }
                    minX = Math.min(minX, card.locationX);
                    maxX = Math.max(maxX, card.locationX);
                    minY = Math.min(minY, card.locationY);
                    maxY = Math.max(maxY, card.locationY);
                }
                const gridWidth = Math.abs(maxX - minX) + 1;
                const gridHeight = Math.abs(maxY - minY) + 1;
                overviewElem.style.width = (gridWidth * this.MINI_TABLEAU_WIDTH + this.MINI_TABLEAU_PADDING * (gridWidth - 1)) + 'px';
                overviewElem.style.height = (gridHeight * this.MINI_TABLEAU_HEIGHT + this.MINI_TABLEAU_PADDING * (gridHeight - 1)) + 'px';

                for (const card of tableauCards) {
                    if (card.cardId === null) {
                        continue;
                    }
                    const y = card.locationY - minY;
                    const x = card.locationX - minX;
                    const def = gameui.gamedatas.carddefs[card.cardId];
                    const miniCard = document.createElement('div');
                    miniCard.classList.add('ea-mini-card');
                    miniCard.style.top = (y * this.MINI_TABLEAU_HEIGHT + y * this.MINI_TABLEAU_PADDING) + 'px';
                    miniCard.style.left = (x * this.MINI_TABLEAU_WIDTH + x * this.MINI_TABLEAU_PADDING) + 'px';
                    for (const ability of def.abilities) {
                        let colors = [ability.color]
                        if (ability.color == 7) {
                            colors = [4, 2, 1];
                            miniCard.classList.add('ea-multicolor');
                        }
                        for (const color of colors) {
                            const miniAbility = document.createElement('div');
                            miniAbility.classList.add('ea-mini-ability');
                            miniAbility.classList.add('ea-mini-ability-color-' + color);
                            miniCard.appendChild(miniAbility);
                        }
                    }
                    overviewElem.appendChild(miniCard);
                }
            },

            adjustCardSprouts(cardId, sproutCount, isInstantaneous = false) {
                sproutCount = parseInt(sproutCount);
                const containerElem = gameui.cardMgr.getCardSproutContainerElementById(cardId);
                const existingSprouts = Array.from(containerElem.querySelectorAll('.ea-token-sprout-container'));
                if (existingSprouts.length > sproutCount) {
                    let nbToDelete = (existingSprouts.length - sproutCount);
                    for (let i = existingSprouts.length - 1; i >= 0 && nbToDelete > 0; --i, --nbToDelete) {
                        const sproutElem = existingSprouts[i];
                        gameui.animateSproutElement(sproutElem, false, isInstantaneous).then(() => sproutElem.remove());
                    }
                } else if (existingSprouts.length < sproutCount) {
                    for (let i = existingSprouts.length; i < sproutCount; ++i) {
                        const sproutElem = gameui.createSproutElement();
                        sproutElem.style.zIndex = 10 - i;
                        containerElem.appendChild(sproutElem);
                        gameui.animateSproutElement(sproutElem, true, isInstantaneous);
                    }
                }
            },

            adjustCardGrowth(cardId, growthCount, isInstantaneous = false) {
                growthCount = parseInt(growthCount);
                let growthMax = 0;
                if (cardId in gameui.gamedatas.carddefs) {
                    growthMax = gameui.gamedatas.carddefs[cardId].growthMax;
                }
                const containerElem = gameui.cardMgr.getCardGrowthContainerElementById(cardId);
                const countElem = containerElem.querySelector('.ea-counter');
                countElem.innerText = growthCount;
                countElem.style.opacity = (growthCount == 0) ? 0 : 1;
                const existingGrowth = Array.from(containerElem.querySelectorAll('.ea-token-growth-container'));
                if (existingGrowth.length > growthCount) {
                    let nbToDelete = (existingGrowth.length - growthCount);
                    for (let i = existingGrowth.length - 1; i >= 0 && nbToDelete > 0; --i, --nbToDelete) {
                        const growthElem = existingGrowth[i];
                        gameui.animateGrowthElement(growthElem, false, isInstantaneous).then(() => growthElem.remove());
                    }
                } else if (existingGrowth.length < growthCount) {
                    for (let i = existingGrowth.length; i < growthCount; ++i) {
                        const growthElem = gameui.createGrowthElement(i, growthMax);
                        containerElem.appendChild(growthElem);
                        gameui.animateGrowthElement(growthElem, true, isInstantaneous);
                    }
                }
            },

            setZoomFactor(value) {
                for (const elem of document.querySelectorAll('.ea-area-player')) {
                    elem.style.setProperty('--ea-zoom', value / 100);
                }
                document.getElementById('ea-tableau-slider').value = value;
                this.refresh();
            },

            refresh() {
                this.tableauCardWidth = null;
                this.tableauCardHeight = null;
                this.calcultateTableauSize().then(() => {
                    if (this.rebuildTableauFct !== undefined && this.rebuildTableauFct !== null) {
                        for (const playerId in gameui.gamedatas.players) {
                            if (playerId in this.rebuildTableauFct) {
                                this.rebuildTableauFct[playerId](false, false);
                            }
                        }
                    }
                });
            },
        });
    });