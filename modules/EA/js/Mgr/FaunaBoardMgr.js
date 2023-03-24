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
        return declare("ea.FaunaBoardMgr", null, {
            setup(gamedatas) {
                if (gamedatas.isGameModeBeginner) {
                    const faunaBoard = document.getElementById('ea-fauna-board');
                    faunaBoard.classList.add('ea-fauna-board-beginner');
                }
                for (const cardId in gamedatas.cards) {
                    const card = gamedatas.cards[cardId];
                    if (card.locationId == gameui.CARD_LOCATION_FAUNA_BOARD_FAUNA) {
                        this.moveCardIdToFaunaBoardFauna(cardId, card.locationX, card.locationY, true);
                    } else if (card.locationId == gameui.CARD_LOCATION_FAUNA_BOARD_ECOSYSTEM) {
                        this.moveCardIdToFaunaBoardEcosystem(cardId, card.locationY, true);
                    }
                }
                for (const tokenId in gamedatas.leafs) {
                    const leaf = gamedatas.leafs[tokenId];
                    if (leaf.locationId == gameui.LEAF_LOCATION_ID_FAUNA_BOARD_FAUNA) {
                        this.moveLeafTokenIdToFaunaCard(tokenId, leaf.locationX, leaf.locationY, leaf.locationOrder, gamedatas.playerActiveOrder, true);
                    } else if (leaf.locationId == gameui.LEAF_LOCATION_ID_FAUNA_BOARD_TABLEAU_BONUS) {
                        this.moveLeafTokenIdToFaunaTableauBonus(tokenId, true);
                    }
                }

                const progressButton = document.getElementById('ea-fauna-board-fauna-progress-button');
                if (gameui.isReadOnly() || !(gameui.player_id in gamedatas.players)) {
                    progressButton.classList.add('bx-hidden');
                }
                gameui.addBasicTooltipToElement(progressButton, _('Open Fauna progress window'));
                progressButton.addEventListener('click', () => {
                    const button = document.querySelector('#overall_player_board_' + gameui.player_id + ' .ea-icon-card-type-fauna');
                    if (button !== null) {
                        button.click();
                    }
                });
            },

            getFaunaBoardFaunaCardElement(x, y) {
                return document.getElementById('ea-fauna-board-fauna-card-' + x + '-' + y);
            },

            getFaunaBoardEcosystemCardElement(y) {
                return document.getElementById('ea-fauna-board-ecosystem-card-' + y);
            },

            moveCardIdToFaunaBoardFauna(cardId, x, y, isInstantaneous = false) {
                const boardElem = this.getFaunaBoardFaunaCardElement(x, y);
                const cardElem = gameui.cardMgr.getCardElementById(cardId);
                return gameui.slide(cardElem, boardElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            moveCardIdToFaunaBoardEcosystem(cardId, y, isInstantaneous = false) {
                const boardElem = this.getFaunaBoardEcosystemCardElement(y);
                const cardElem = gameui.cardMgr.getCardElementById(cardId);
                return gameui.slide(cardElem, boardElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            getFaunaBoardFaunaLeafElement(x, y, order) {
                return document.getElementById('ea-fauna-board-fauna-leaf-' + x + '-' + y + '-' + order);
            },

            getFaunaBoardFaunaLeafWaitElement(x, y) {
                return document.getElementById('ea-fauna-board-fauna-leaf-' + x + '-' + y + '-wait');
            },

            isLeafTokenOnFaunaBoard(tokenId) {
                const leafElem = gameui.leafTokenMgr.getLeafElementByTokenId(tokenId);
                if (leafElem === null) {
                    return false;
                }
                return (leafElem.closest('#ea-fauna-board') !== null);
            },

            moveLeafTokenIdToFaunaCard(tokenId, locationX, locationY, locationOrder, playerActiveOrder, isInstantaneous = false) {
                const leafElem = gameui.leafTokenMgr.getLeafElementByTokenId(tokenId);
                if (gameui.gamedatas.isGameModeBeginner && locationOrder === null) {
                    for (locationOrder = 0; locationOrder <= 4; locationOrder += 1) {
                        const targetElem = this.getFaunaBoardFaunaLeafElement(locationX, locationY, locationOrder);
                        const targetLeafElem = targetElem.querySelector('.ea-token-leaf');
                        if (targetLeafElem == null || targetLeafElem.dataset.tokenId == tokenId) {
                            break;
                        }
                    }
                }
                if (locationOrder === null) {
                    const targetElem = this.getFaunaBoardFaunaLeafWaitElement(locationX, locationY);
                    return gameui.slide(leafElem, targetElem, {
                        phantom: true,
                        isInstantaneous: isInstantaneous,
                    }).then(() => {
                        const childs = [];
                        for (const childElem of targetElem.querySelectorAll('.ea-token-leaf')) {
                            childs.push(childElem);
                            childElem.remove();
                        }
                        childs.sort((c1, c2) => playerActiveOrder[c1.dataset.playerId] - playerActiveOrder[c2.dataset.playerId]);
                        for (const childElem of childs) {
                            targetElem.appendChild(childElem);
                        }
                        this.updatePlayerPanelFaunaCounters();
                    });
                }
                const targetElem = this.getFaunaBoardFaunaLeafElement(locationX, locationY, locationOrder);
                return gameui.slide(leafElem, targetElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                }).then(() => this.updatePlayerPanelFaunaCounters());
            },

            updatePlayerPanelFaunaCounters() {
                const playerCount = {};
                for (const playerId in gameui.gamedatas.players) {
                    playerCount[playerId] = 0;
                }
                for (const tokenElem of document.querySelectorAll('#ea-area-fauna-board .ea-fauna-board-fauna-leaf-spot .ea-token-leaf')) {
                    const playerId = tokenElem.dataset.playerId;
                    if (playerId == gameui.GAIA_PLAYER_ID) {
                        continue;
                    }
                    playerCount[playerId] += 1;
                }
                for (const playerId in playerCount) {
                    gameui.counters[playerId].fauna.toValue([playerCount[playerId], 4]);
                }
            },

            getFaunaBoardFaunaLeafTableauBonusElement() {
                return document.getElementById('ea-fauna-board-fauna-leaf-tableau-bonus');
            },

            moveLeafTokenIdToFaunaTableauBonus(tokenId, isInstantaneous = false) {
                const leafElem = gameui.leafTokenMgr.getLeafElementByTokenId(tokenId);
                const targetElem = this.getFaunaBoardFaunaLeafTableauBonusElement();
                return gameui.slide(leafElem, targetElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            setZoomFactor(value) {
                document.getElementById('ea-area-common').style.setProperty('--ea-zoom', value / 100);
                document.getElementById('ea-fauna-slider').value = value;
            },
        });
    });