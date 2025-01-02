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
        return declare("ea.GaiaBoardMgr", null, {
            MAX_CARDS: 5,
            SOIL_SCALE: 0.7,
            MAX_ROUNDS: 6 * 2,

            SPROUT_SIZE: 70 * 0.75,
            SPROUT_PADDING: [44 * 0.75, 16 * 0.75],
            SPROUT_PER_LINE: 7,
            SPROUT_SECTION_COUNT: 7 * 5,
            SPROUT_SECTION_XY: [[70, 110], [70, 450]],

            GROWTH_XY: [1067, 155],
            GROWTH_PADDING: [105, 172],
            GROWTH_PER_LINE: 5,
            GROWTH_COUNT: 5 * 4,
            GROWTH_HEIGHTS: [5, 4, 3, 2, 1],

            setup(gamedatas) {
                this.gaiaTableauCardIds = [];
                const areaElement = this.getAreaGaiaBoardElement();
                if (!gameui.isGameSolo()) {
                    areaElement.classList.add('bx-hidden');
                    return;
                }

                gameui.counters.gaiaSoil.addTarget(document.getElementById('ea-gaia-soil-count'));
                gameui.counters.gaiaSprout.addTarget(document.getElementById('ea-gaia-board-sprout-count'));
                gameui.counters.gaiaGrowth.addTarget(document.getElementById('ea-gaia-board-growth-count'));
                gameui.counters.gaiaCompost.addTarget(document.getElementById('ea-gaia-board-compost-count'));
                gameui.counters.gaiaTableau.addTarget(document.getElementById('ea-gaia-board-tableau-count'));
                gameui.counters.gaiaDeck.addTarget(document.getElementById('ea-gaia-board-deck-count'));
                gameui.counters.gaiaGaiaCard.addTarget(document.getElementById('ea-gaia-board-gaia-card-count'));
                gameui.counters.gaiaRound.addTarget(document.getElementById('ea-gaia-board-round-count'));

                for (const helpElement of document.querySelectorAll('.ea-gaia-board-help')) {
                    helpElement.addEventListener('click', () => {
                        gameui.cardDetailMgr.show(helpElement);
                    });
                }

                for (const tokenId in gamedatas.leafs) {
                    const leaf = gamedatas.leafs[tokenId];
                    if (leaf.playerId == gameui.GAIA_PLAYER_ID && leaf.locationId == gameui.LEAF_LOCATION_ID_PLAYER_BOARD) {
                        this.moveLeafTokenIdToGaiaBoard(tokenId, leaf.locationX, true);
                    } else if (leaf.locationId == gameui.LEAF_LOCATION_ID_GAIA_ABUNDANCE) {
                        this.moveLeafTokenIdToGaiaAbundance(tokenId, true);
                    }
                }

                this.createSpoutContainer(0);
                this.createSpoutContainer(1);
                this.createGrowthContainer();

                this.updateGaiaDeckCount(gamedatas.cardCounts.gaiaDeckCount, true);
                this.updateGaiaCount(gamedatas.gaia, true);

                const gaiaTableauCards = [];
                for (const cardId in gamedatas.cards) {
                    const card = gamedatas.cards[cardId];
                    if (card.locationId == gameui.CARD_LOCATION_GAIA_TABLEAU) {
                        gaiaTableauCards.push(card);
                    }
                }
                this.buildGaiaTableauFromCards(gaiaTableauCards, true);

                const gaiaDicardCards = [];
                for (const cardId in gamedatas.cards) {
                    const card = gamedatas.cards[cardId];
                    if (card.locationId == gameui.CARD_LOCATION_GAIA_DISCARD) {
                        gaiaDicardCards.push(card);
                    }
                }
                this.buildGaiaDiscardFromCards(gaiaDicardCards, true);
            },

            getAreaGaiaBoardElement() {
                return document.getElementById('ea-area-gaia-board');
            },

            getGaiaBoardElement() {
                return document.getElementById('ea-gaia-board');
            },

            getGaiaTableauCardIds() {
                return this.gaiaTableauCardIds;
            },

            moveLeafTokenIdToGaiaAbundance(tokenId, isInstantaneous = false) {
                const leafBoardElem = document.getElementById('ea-gaia-board-abundance-leaf');
                const leafElem = gameui.leafTokenMgr.getLeafElementByTokenId(tokenId);
                return gameui.slide(leafElem, leafBoardElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            moveLeafTokenIdToGaiaBoard(tokenId, x, isInstantaneous = false) {
                const leafBoardElem = document.getElementById('ea-gaia-board-leaf-board-' + x);
                const leafElem = gameui.leafTokenMgr.getLeafElementByTokenId(tokenId);
                return gameui.slide(leafElem, leafBoardElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            moveElementToGaiaCompost(element, isInstantaneous = false) {
                const areaElem = document.getElementById('ea-gaia-board-compost-cards');
                return gameui.slide(element, areaElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            moveCardIdToGaiaDeck(cardId, isInstantaneous = false) {
                const areaElem = document.getElementById('ea-gaia-board-deck-cards');
                const cardElem = gameui.cardMgr.getCardElementById(cardId);
                return gameui.slide(cardElem, areaElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            moveCardIdToGaiaDiscard(cardId, isInstantaneous = false) {
                const areaElem = document.getElementById('ea-gaia-board-gaia-card-cards');
                const cardElem = gameui.cardMgr.getCardElementById(cardId);
                return gameui.slide(cardElem, areaElem, {
                    phantom: false,
                    isInstantaneous: isInstantaneous,
                });
            },

            buildGaiaDiscardFromCards(cards, isInstantaneous = false) {
                const areaElem = document.getElementById('ea-gaia-board-gaia-card-cards');
                areaElem.innerHTML = '';
                cards.sort((c1, c2) => c1.locationOrder - c2.locationOrder);
                for (let i = 0; i < cards.length; ++i) {
                    let cardElem = gameui.cardMgr.getCardElementById(cards[i].cardId);
                    if (cardElem !== null) {
                        cardElem.remove();
                    }
                    if (cardElem === null) {
                        cardElem = gameui.cardMgr.createCardElement(cards[i], true);
                    }
                    areaElem.appendChild(cardElem);
                }
                // Update count
                gameui.counters.gaiaGaiaCard.toValue(cards.length, isInstantaneous);
                // Show or hide help button
                const helpElement = document.getElementById('ea-gaia-board-gaia-card-help');
                if (cards.length == 0) {
                    helpElement.classList.add('bx-hidden');
                } else {
                    helpElement.classList.remove('bx-hidden');
                }
            },

            moveCardIdToGaiaTableau(cardId, isInstantaneous = false) {
                const areaElem = document.getElementById('ea-gaia-board-tableau-cards');
                const cardElem = gameui.cardMgr.getCardElementById(cardId);
                return gameui.slide(cardElem, areaElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            buildGaiaTableauFromCards(cards, isInstantaneous = false) {
                cards.sort((c1, c2) => c1.locationOrder - c2.locationOrder);
                this.gaiaTableauCardIds = cards.map((c) => c.cardId);
                this.gaiaTableauCardIds.reverse();
                const areaElem = document.getElementById('ea-gaia-board-tableau-cards');
                areaElem.innerHTML = '';
                for (let i = 0; i < cards.length; ++i) {
                    let cardElem = gameui.cardMgr.getCardElementById(cards[i].cardId);
                    if (cardElem !== null) {
                        cardElem.remove();
                    }
                    if (i < cards.length - this.MAX_CARDS) {
                        continue;
                    }
                    if (cardElem === null) {
                        cardElem = gameui.cardMgr.createCardElement(cards[i], true);
                    }
                    areaElem.appendChild(cardElem);
                }
                // Update count
                gameui.counters.gaiaTableau.toValue(cards.length, isInstantaneous);
                // Show or hide help button
                const helpElement = document.getElementById('ea-gaia-board-tableau-help');
                if (cards.length == 0) {
                    helpElement.classList.add('bx-hidden');
                } else {
                    helpElement.classList.remove('bx-hidden');
                }
            },

            updateGaiaCount(gaia, isInstantaneous = false) {
                this.updateGaiaSoilCount(gaia.soilCount, isInstantaneous);
                this.updateGaiaSproutCount(gaia.sproutCount, isInstantaneous);
                this.updateGaiaGrowthCount(gaia.growthCount, isInstantaneous);
                this.updateGaiaRoundCount(gaia.roundCount, isInstantaneous);
                this.updateGaiaFaunaCount(gaia.faunaCount, isInstantaneous);
                return this.updateGaiaCompostCount(gaia.compostCount, isInstantaneous);
            },

            updateGaiaRoundCount(roundCount, isInstantaneous = false) {
                gameui.counters.gaiaRound.toValue([roundCount, this.MAX_ROUNDS], isInstantaneous);
            },

            updateGaiaFaunaCount(faunaCount, isInstantaneous = false) {
                gameui.counters.gaiaFauna.toValue([faunaCount, 4], isInstantaneous);
            },

            updateGaiaSoilCount(soilCount, isInstantaneous = false) {
                gameui.counters.gaiaSoil.toValue(soilCount, isInstantaneous);
                const soilBoxElem = document.getElementById('ea-gaia-soil-box');
                for (const e of soilBoxElem.querySelectorAll('.ea-token-soil')) {
                    e.remove();
                }
                for (let i = 0; i < soilCount; ++i) {
                    const soilElem = gameui.createSoilTokenElement();
                    soilBoxElem.appendChild(soilElem);
                    soilElem.style.position = 'absolute';
                    soilElem.style.transform = 'scale(' + this.SOIL_SCALE + ') rotate(' + (Math.random() * 360) + 'deg)';
                    soilElem.style.top = Math.floor(Math.random() * (soilBoxElem.offsetHeight - soilElem.offsetHeight * this.SOIL_SCALE)) + 'px';
                    soilElem.style.left = Math.floor(Math.random() * (soilBoxElem.offsetWidth - soilElem.offsetWidth * this.SOIL_SCALE)) + 'px';
                }
            },

            updateGaiaDeckCount(deckCount, isInstantaneous = false) {
                gameui.counters.gaiaDeck.toValue(deckCount, isInstantaneous);
                const areaElement = document.getElementById('ea-gaia-board-deck-cards');
                for (const c of areaElement.querySelectorAll('.ea-card[data-is-gaia-back-card="true"]')) {
                    c.remove();
                }
                for (let i = 0; i < deckCount; ++i) {
                    const card = gameui.cardMgr.createGaiaBackCardElement();
                    areaElement.insertBefore(card, areaElement.firstChild);
                }
            },

            updateGaiaCompostCount(compostCount, isInstantaneous = false) {
                const prevCount = gameui.counters.gaiaCompost.getValues();
                const movements = []
                if (!isInstantaneous) {
                    for (let i = prevCount; i < compostCount; ++i) {
                        const cardElem = gameui.cardMgr.createEarthBackCardElement();
                        gameui.deckMgr.moveElementToDeck(cardElem, true);
                        movements.push(
                            gameui.wait(50 * (i - prevCount)).then(() => this.moveElementToGaiaCompost(cardElem))
                        );
                    }
                }
                return Promise.all(movements).then(() => {
                    gameui.counters.gaiaCompost.toValue(compostCount, isInstantaneous);
                    const areaElement = document.getElementById('ea-gaia-board-compost-cards');
                    for (const c of areaElement.querySelectorAll('.ea-card[data-is-earth-back-card="true"]')) {
                        c.remove();
                    }
                    for (let i = 0; i < Math.min(compostCount, this.MAX_CARDS); ++i) {
                        const card = gameui.cardMgr.createEarthBackCardElement();
                        areaElement.insertBefore(card, areaElement.firstChild);
                    }
                });
            },

            updateGaiaSproutCount(sproutCount, isInstantaneous = false) {
                sproutCount = parseInt(sproutCount);
                gameui.counters.gaiaSprout.toValue(sproutCount, isInstantaneous);

                const containerElem = this.getAreaGaiaBoardElement();
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
                        const spotElem = document.getElementById('ea-gaia-board-sprout-' + i);
                        if (spotElem === null) {
                            continue;
                        }
                        spotElem.appendChild(sproutElem);
                        gameui.animateSproutElement(sproutElem, true, isInstantaneous);
                    }
                }
            },

            createSpoutContainer(section) {
                const boardElem = this.getGaiaBoardElement();
                for (let i = 0; i < this.SPROUT_SECTION_COUNT; ++i) {
                    const x = Math.floor(i % this.SPROUT_PER_LINE);
                    const y = Math.floor(i / this.SPROUT_PER_LINE);
                    const sproutElem = document.createElement('div');
                    sproutElem.id = 'ea-gaia-board-sprout-' + (section * this.SPROUT_SECTION_COUNT + i);
                    sproutElem.style.position = 'absolute';
                    sproutElem.style.top = 'calc(' + (this.SPROUT_SECTION_XY[section][1] + y * this.SPROUT_SIZE + y * this.SPROUT_PADDING[1]) + 'px * var(--ea-zoom))';
                    sproutElem.style.left = 'calc(' + (this.SPROUT_SECTION_XY[section][0] + x * this.SPROUT_SIZE + x * this.SPROUT_PADDING[0]) + 'px * var(--ea-zoom))';
                    sproutElem.style.width = 'calc(' + this.SPROUT_SIZE + 'px * var(--ea-zoom))';
                    sproutElem.style.height = 'calc(' + this.SPROUT_SIZE + 'px * var(--ea-zoom))';
                    boardElem.appendChild(sproutElem);
                }
            },

            updateGaiaGrowthCount(growthCount, isInstantaneous = false) {
                growthCount = parseInt(growthCount);
                gameui.counters.gaiaGrowth.toValue(growthCount, isInstantaneous);

                const containerElem = this.getAreaGaiaBoardElement();
                const existingGrowths = Array.from(containerElem.querySelectorAll('.ea-token-growth-container'));
                if (existingGrowths.length > growthCount) {
                    let nbToDelete = (existingGrowths.length - growthCount);
                    for (let i = existingGrowths.length - 1; i >= 0 && nbToDelete > 0; --i, --nbToDelete) {
                        const growthElem = existingGrowths[i];
                        gameui.animateGrowthElement(growthElem, false, isInstantaneous).then(() => {
                            const parentElem = growthElem.parentElement;
                            growthElem.remove();
                            const countElem = parentElem.querySelector('.ea-counter');
                            const count = parentElem.querySelectorAll('.ea-token-growth-container').length;
                            countElem.innerText = count;
                            countElem.style.opacity = (count == 0) ? 0 : 1;
                        });
                    }
                } else if (existingGrowths.length < growthCount) {
                    for (let i = existingGrowths.length; i < growthCount; ++i) {
                        const pos = this.getGaiaGrowthPos(i);
                        const idx = pos.y * this.GROWTH_PER_LINE + pos.x;
                        const growthElem = gameui.createGrowthElement(pos.height, pos.max);
                        const spotElem = document.getElementById('ea-gaia-board-growth-' + idx);
                        if (spotElem === null) {
                            continue;
                        }
                        const countElem = spotElem.querySelector('.ea-counter');
                        countElem.innerText = (pos.height + 1);
                        countElem.style.opacity = 1;
                        spotElem.appendChild(growthElem);
                        gameui.animateGrowthElement(growthElem, true, isInstantaneous);
                    }
                }
            },

            getGaiaGrowthPos(growthIdx) {
                const pos = {
                    x: 0,
                    y: 0,
                    height: 0,
                    max: this.GROWTH_HEIGHTS[0],
                    heightIdx: 0,
                };
                for (let i = 0; i < growthIdx; ++i) {
                    pos.height += 1;
                    if (pos.height >= pos.max) {
                        pos.height = 0;
                        pos.heightIdx += 1;
                        if (pos.heightIdx >= this.GROWTH_HEIGHTS.length) {
                            pos.heightIdx = 0;
                        }
                        pos.max = this.GROWTH_HEIGHTS[pos.heightIdx];
                        pos.x += 1;
                        if (pos.x >= this.GROWTH_PER_LINE) {
                            pos.x = 0;
                            pos.y += 1;
                        }
                    }
                }
                return pos;
            },

            createGrowthContainer() {
                const boardElem = this.getGaiaBoardElement();

                for (let i = 0; i < this.GROWTH_COUNT; ++i) {
                    const growthElem = document.createElement('div');
                    let x = Math.floor(i % this.GROWTH_PER_LINE);
                    const y = Math.floor(i / this.GROWTH_PER_LINE);
                    if ((y % 2) == 1) {
                        x = this.GROWTH_PER_LINE - x - 1;
                    }
                    growthElem.id = 'ea-gaia-board-growth-' + i;
                    growthElem.classList.add('ea-gaia-growth-container');
                    switch (i % 4) {
                        case 0:
                            growthElem.classList.add('ea-token-canopy-red');
                            break;
                        case 1:
                            growthElem.classList.add('ea-token-canopy-green');
                            break;
                        case 2:
                            growthElem.classList.add('ea-token-canopy-purple');
                            break;
                        case 3:
                            growthElem.classList.add('ea-token-canopy-yellow');
                            break;
                    }
                    growthElem.style.top = 'calc(' + (this.GROWTH_XY[1] + y * this.GROWTH_PADDING[1]) + 'px * var(--ea-zoom))';
                    growthElem.style.left = 'calc(' + (this.GROWTH_XY[0] + x * this.GROWTH_PADDING[0]) + 'px * var(--ea-zoom))';
                    const growthCountElem = document.createElement('div');
                    growthCountElem.classList.add('ea-counter');
                    growthCountElem.innerText = '0';
                    growthElem.appendChild(growthCountElem);
                    boardElem.appendChild(growthElem);
                }
            },
        });
    });