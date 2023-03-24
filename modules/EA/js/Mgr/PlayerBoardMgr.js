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
        return declare("ea.PlayerBoardMgr", null, {
            MAX_CARDS: 5,
            MAX_SOIL_COUNT: 10,
            SOIL_SCALE: 0.7,
            SOIL_ANIMATION_DELAY: 50,

            setup(gamedatas) {
                this.eventCardIds = {};
                if (gameui.isGameSolo()) {
                    gameui.addBasicTooltipToElement(
                        Array.from(document.querySelectorAll('.ea-player-board-action-0')),
                        gameui.format_string_recursive(
                            _('Plant (green).${br}You: Plant 2 cards, then draw 4 cards and keep 1.${br}Gaia: Your 3 discarded cards and placed on the Gaia board.${br}You: Activate green abilities.'),
                            { 'br': '<br/>' }
                        )
                    );

                    if (gamedatas.gaiaEasySide) {
                        const soloBoardEasyElem = document.querySelector('.ea-player-board-solo-easy');
                        soloBoardEasyElem.classList.remove('bx-hidden');

                        gameui.addBasicTooltipToElement(
                            Array.from(document.querySelectorAll('.ea-player-board-action-1')),
                            gameui.format_string_recursive(
                                _('Compost (red).${br}You: Gain 5 soil and compost 2 cards from deck.${br}Gaia: Compost 2 cards, plus 1 card per 2 soil you gained this turn.${br}You: Activate red and multicolor abilities.'),
                                { 'br': '<br/>' }
                            )
                        );
                        gameui.addBasicTooltipToElement(
                            Array.from(document.querySelectorAll('.ea-player-board-action-2')),
                            gameui.format_string_recursive(
                                _('Water (blue).${br}You: Gain 6 sprouts and gain 2 soil.${br}Gaia: Gain 1 sprout per blue abilities you have.${br}You: Activate blue and multicolor abilities.'),
                                { 'br': '<br/>' }
                            )
                        );
                        gameui.addBasicTooltipToElement(
                            Array.from(document.querySelectorAll('.ea-player-board-action-3')),
                            gameui.format_string_recursive(
                                _('Grow (yellow).${br}You: Draw 4 cards and gain 2 growth.${br}Gaia: Gain 1 growth per card you draw this turn.${br}You: Activate yellow and multicolor abilities.'),
                                { 'br': '<br/>' }
                            )
                        );
                    } else {
                        const soloBoardHardElem = document.querySelector('.ea-player-board-solo-hard');
                        soloBoardHardElem.classList.remove('bx-hidden');

                        gameui.addBasicTooltipToElement(
                            Array.from(document.querySelectorAll('.ea-player-board-action-1')),
                            gameui.format_string_recursive(
                                _('Compost (red).${br}You: Gain 5 soil and compost 2 cards from deck.${br}Gaia: Compost 5 cards, plus 1 card per 2 soil you gained this turn.${br}You: Activate red and multicolor abilities.'),
                                { 'br': '<br/>' }
                            )
                        );
                        gameui.addBasicTooltipToElement(
                            Array.from(document.querySelectorAll('.ea-player-board-action-2')),
                            gameui.format_string_recursive(
                                _('Water (blue).${br}You: Gain 6 sprouts and gain 2 soil.${br}Gaia: Gain 3 sprouts and gain 1 sprout per blue abilities you have.${br}You: Activate blue and multicolor abilities.'),
                                { 'br': '<br/>' }
                            )
                        );
                        gameui.addBasicTooltipToElement(
                            Array.from(document.querySelectorAll('.ea-player-board-action-3')),
                            gameui.format_string_recursive(
                                _('Grow (yellow).${br}You: Draw 4 cards and gain 2 growth.${br}Gaia: Gain 3 growth and gain 1 growth per card you draw this turn.${br}You: Activate yellow and multicolor abilities.'),
                                { 'br': '<br/>' }
                            )
                        );
                    }
                } else {
                    gameui.addBasicTooltipToElement(
                        Array.from(document.querySelectorAll('.ea-player-board-action-0')),
                        gameui.format_string_recursive(
                            _('Plant (green).${br}Active player: Plant 2 cards, then draw 4 cards and keep 1.${br}Other players: Plant 1 card, then draw 1 card.${br}All players: Activate green abilities.'),
                            { 'br': '<br/>' }
                        )
                    );
                    gameui.addBasicTooltipToElement(
                        Array.from(document.querySelectorAll('.ea-player-board-action-1')),
                        gameui.format_string_recursive(
                            _('Compost (red).${br}Active player: Gain 5 soil and compost 2 cards from deck.${br}Other players: Gain 2 soil or compost 2 cards from deck.${br}All players: Activate red and multicolor abilities.'),
                            { 'br': '<br/>' }
                        )
                    );
                    gameui.addBasicTooltipToElement(
                        Array.from(document.querySelectorAll('.ea-player-board-action-2')),
                        gameui.format_string_recursive(
                            _('Water (blue).${br}Active player: Gain 6 sprouts and gain 2 soil.${br}Other players: Gain 2 sprouts or gain 2 soil.${br}All players: Activate blue and multicolor abilities.'),
                            { 'br': '<br/>' }
                        )
                    );
                    gameui.addBasicTooltipToElement(
                        Array.from(document.querySelectorAll('.ea-player-board-action-3')),
                        gameui.format_string_recursive(
                            _('Grow (yellow).${br}Active player: Draw 4 cards and gain 2 growth.${br}Other players: Draw 2 cards or gain 2 growth.${br}All players: Activate yellow and multicolor abilities.'),
                            { 'br': '<br/>' }
                        )
                    );
                }
                for (const playerId in gamedatas.players) {
                    this.eventCardIds[playerId] = [];
                    gameui.counters[playerId].soil.addTarget(this.getPlayerIdBoardSoilCountElem(playerId));
                    gameui.counters[playerId].compost.addTarget(this.getPlayerIdBoardCompostCountElem(playerId));
                    gameui.counters[playerId].event.addTarget(this.getPlayerIdBoardEventCountElem(playerId));
                }
                for (const cardId in gamedatas.cards) {
                    const card = gamedatas.cards[cardId];
                    if (card.locationId == gameui.CARD_LOCATION_PLAYER_BOARD && card.locationOrder === null) {
                        this.moveCardIdToPlayerIdBoard(card.playerId, cardId, card.locationX, null, true);
                    }
                }
                for (const playerId in gamedatas.eventPerPlayerId) {
                    const cards = gamedatas.eventPerPlayerId[playerId];
                    this.buildPlayerEventFromCards(playerId, cards, true);
                }
                for (const tokenId in gamedatas.leafs) {
                    const leaf = gamedatas.leafs[tokenId];
                    if (leaf.playerId == gameui.GAIA_PLAYER_ID) {
                        continue;
                    }
                    if (leaf.locationId == gameui.LEAF_LOCATION_ID_ACTION) {
                        this.moveLeafTokenIdToPlayerAction(tokenId, leaf.playerId, leaf.locationX, true);
                    } else if (leaf.locationId == gameui.LEAF_LOCATION_ID_PLAYER_BOARD) {
                        this.moveLeafTokenIdToPlayerBoard(tokenId, leaf.playerId, leaf.locationX, true);
                    }
                }
                for (const playerId in gamedatas.soilCountByPlayerId) {
                    const soilCount = gamedatas.soilCountByPlayerId[playerId];
                    this.updateSoilCountForPlayerId(playerId, soilCount, true);
                }
                this.updateCompostCount(gamedatas.cardCounts.compostCountByPlayerId, true);
                this.updateMainAction(gamedatas.mainActionId, gamedatas.activePlayerId);
                for (const helpElement of document.querySelectorAll('.ea-player-board-event-help')) {
                    helpElement.addEventListener('click', () => {
                        gameui.cardDetailMgr.show(helpElement);
                    });
                }
            },

            updateCompostCount(compostCountByPlayerId, isInstantaneous = false) {
                for (const playerId in compostCountByPlayerId) {
                    const compostCount = compostCountByPlayerId[playerId];
                    this.updateCompostCountForPlayerId(playerId, compostCount, isInstantaneous)
                }
            },

            updateCompostCountForPlayerId(playerId, compostCount, isInstantaneous = false) {
                gameui.counters[playerId].compost.toValue(compostCount, isInstantaneous);
                const areaElement = this.getPlayerIdBoardCompostCardsElem(playerId);
                for (const c of areaElement.querySelectorAll('.ea-card[data-is-earth-back-card="true"]')) {
                    c.remove();
                }
                for (let i = 0; i < Math.min(compostCount, this.MAX_CARDS); ++i) {
                    const card = gameui.cardMgr.createEarthBackCardElement();
                    areaElement.insertBefore(card, areaElement.firstChild);
                }
            },

            getPlayerIdBoardCompostCountElem(playerId) {
                return document.querySelector('#ea-area-player-' + playerId + ' .ea-player-board-compost-count');
            },

            getPlayerIdBoardCompostCardsElem(playerId) {
                return document.querySelector('#ea-area-player-' + playerId + ' .ea-player-board-compost-cards');
            },

            getPlayerIdBoardEventCountElem(playerId) {
                return document.querySelector('#ea-area-player-' + playerId + ' .ea-player-board-event-count');
            },

            buildPlayerEventFromCards(playerId, cards, isInstantaneous = false) {
                this.eventCardIds[playerId] = cards.map((c) => c.cardId);
                this.eventCardIds[playerId].reverse();
                const areaElem = this.getPlayerIdAreaCardElement(playerId, null, 0);
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
                gameui.counters[playerId].event.toValue(cards.length, isInstantaneous);
                // Show or hide help button
                const helpElement = this.getPlayerIdEventCardHelpElement(playerId);
                if (cards.length == 0) {
                    helpElement.classList.add('bx-hidden');
                } else {
                    helpElement.classList.remove('bx-hidden');
                }
            },

            getEventCardIds(playerId) {
                return this.eventCardIds[playerId];
            },

            updateSoilCountForPlayerId(playerId, soilCount, isInstantaneous = false) {
                const soilCountElem = this.getPlayerIdBoardSoilCountElem(playerId);
                const prevSoilCount = parseInt(soilCountElem.innerText);
                gameui.counters[playerId].soil.toValue(soilCount, isInstantaneous);
                const soilBoxElem = this.getPlayerIdBoardSoilBoxElem(playerId);
                for (const e of soilBoxElem.querySelectorAll('.ea-token-soil')) {
                    e.remove();
                }
                for (let i = 0; i < Math.min(soilCount, this.MAX_SOIL_COUNT); ++i) {
                    const soilElem = gameui.createSoilTokenElement();
                    soilBoxElem.appendChild(soilElem);
                    soilElem.style.position = 'absolute';
                    soilElem.style.transform = 'scale(' + this.SOIL_SCALE + ') rotate(' + (Math.random() * 360) + 'deg)';
                    soilElem.style.top = Math.floor(Math.random() * (soilBoxElem.offsetHeight - soilElem.offsetHeight * this.SOIL_SCALE)) + 'px';
                    soilElem.style.left = Math.floor(Math.random() * (soilBoxElem.offsetWidth - soilElem.offsetWidth * this.SOIL_SCALE)) + 'px';
                }
                return (prevSoilCount != soilCount);
            },

            animateSoilToElement(soilCount, fromElement, toElement) {
                const movements = [];
                for (let i = 0; i < Math.min(soilCount, this.MAX_SOIL_COUNT); ++i) {
                    const soilElem = gameui.createSoilTokenElement();
                    soilElem.style.position = 'relative';
                    fromElement.appendChild(soilElem);
                    gameui.placeOnObject(soilElem, fromElement);
                    movements.push(
                        gameui.slide(soilElem, toElement, {
                            delay: i * this.SOIL_ANIMATION_DELAY,
                        }).then(
                            () => soilElem.remove()
                        )
                    );
                }
                return Promise.all(movements);
            },

            animateSoilFromCardIdToPlayerId(soilCount, fromCardId, toPlayerId) {
                if (fromCardId === undefined || fromCardId === null) {
                    return Promise.resolve();
                }
                const cardElem = gameui.cardMgr.getCardElementById(fromCardId);
                if (cardElem === null) {
                    return Promise.resolve();
                }
                const soilBoxElem = this.getPlayerIdBoardSoilBoxElem(toPlayerId);
                return this.animateSoilToElement(soilCount, cardElem, soilBoxElem);
            },

            animateSoilFromPlayerIdToCardId(soilCount, fromPlayerId, toCardId) {
                if (toCardId === undefined || toCardId === null) {
                    return Promise.resolve();
                }
                const cardElem = gameui.cardMgr.getCardElementById(toCardId);
                if (cardElem === null) {
                    return Promise.resolve();
                }
                const soilBoxElem = this.getPlayerIdBoardSoilBoxElem(fromPlayerId);
                return this.animateSoilToElement(soilCount, soilBoxElem, cardElem);
            },

            animateSoilFromMainActionIdToPlayerId(soilCount, fromMainActionId, toPlayerId) {
                if (fromMainActionId === undefined || fromMainActionId === null) {
                    return Promise.resolve();
                }
                const actionElem = this.getPlayerIdActionElem(toPlayerId, fromMainActionId);
                if (actionElem === null) {
                    return Promise.resolve();
                }
                const soilBoxElem = this.getPlayerIdBoardSoilBoxElem(toPlayerId);
                return this.animateSoilToElement(soilCount, actionElem, soilBoxElem);
            },

            animateSoilFromConversionToPlayerId(soilCount, toPlayerId) {
                const conversionBoxElem = this.getPlayerIdConversionBoxElem(toPlayerId);
                const soilBoxElem = this.getPlayerIdBoardSoilBoxElem(toPlayerId);
                return this.animateSoilToElement(soilCount, conversionBoxElem, soilBoxElem);
            },

            getPlayerIdConversionBoxElem(playerId) {
                return document.querySelector('#ea-area-player-' + playerId + ' .ea-player-soil-conversion-box');
            },

            getPlayerIdBoardSoilCountElem(playerId) {
                return document.querySelector('#ea-area-player-' + playerId + ' .ea-player-soil-count');
            },

            getPlayerIdBoardSoilBoxElem(playerId) {
                return document.querySelector('#ea-area-player-' + playerId + ' .ea-player-soil-box');
            },

            getPlayerIdBoardLeafElem(playerId, x) {
                return document.querySelector('#ea-area-player-' + playerId + ' .ea-player-board-leaf-board-' + x);
            },

            getPlayerIdActionLeafElem(playerId, x) {
                return document.querySelector('#ea-area-player-' + playerId + ' .ea-player-board-leaf-action-' + x);
            },

            getPlayerIdActionElem(playerId, x) {
                return document.querySelector('#ea-area-player-' + playerId + ' .ea-player-board-action-' + x);
            },

            moveLeafTokenIdToPlayerAction(tokenId, playerId, x, isInstantaneous = false) {
                const leafActionElem = this.getPlayerIdActionLeafElem(playerId, x);
                const leafElem = gameui.leafTokenMgr.getLeafElementByTokenId(tokenId);
                return gameui.slide(leafElem, leafActionElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            moveLeafTokenIdToPlayerBoard(tokenId, playerId, x, isInstantaneous = false) {
                const leafBoardElem = this.getPlayerIdBoardLeafElem(playerId, x);
                const leafElem = gameui.leafTokenMgr.getLeafElementByTokenId(tokenId);
                return gameui.slide(leafElem, leafBoardElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            getPlayerIdEventCardHelpElement(playerId) {
                return document.querySelector('#ea-area-player-' + playerId + ' .ea-player-board-event-help');
            },

            getPlayerIdAreaCardElement(playerId, x, order) {
                if (order === null) {
                    return document.querySelector('#ea-area-player-' + playerId + ' .ea-player-board-card-' + x);
                } else {
                    return document.querySelector('#ea-area-player-' + playerId + ' .ea-player-board-event-cards');
                }
            },

            moveCardIdToPlayerIdBoard(playerId, cardId, x, order, isInstantaneous = false) {
                const areaElem = this.getPlayerIdAreaCardElement(playerId, x, order);
                const cardElem = gameui.cardMgr.getCardElementById(cardId);
                return gameui.slide(cardElem, areaElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            getPlayerIdAreaCompostCardsElement(playerId) {
                return document.querySelector('#ea-area-player-' + playerId + ' .ea-player-board-compost-cards');
            },

            moveElementToPlayerIdCompost(playerId, element, isInstantaneous = false, delay = 0) {
                const areaElem = this.getPlayerIdAreaCompostCardsElement(playerId);
                return gameui.slide(element, areaElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                    delay: delay,
                }).then(() => {
                    element.remove();
                });
            },
            moveElementToPlayerIdCompostNoRemove(playerId, element, isInstantaneous = false, delay = 0) {
                const areaElem = this.getPlayerIdAreaCompostCardsElement(playerId);
                return gameui.slide(element, areaElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                    delay: delay,
                });
            },
            moveCardIdToPlayerIdCompost(playerId, cardId, isInstantaneous = false) {
                const cardElem = gameui.cardMgr.getCardElementById(cardId);
                return this.moveElementToPlayerIdCompost(playerId, cardElem, isInstantaneous);
            },

            updateMainAction(mainActionId, activePlayerId) {
                for (const elem of document.querySelectorAll('.ea-area-player-board .ea-token-active-player')) {
                    elem.classList.add('ea-token-inactive');
                }
                if (mainActionId === null || activePlayerId === null) {
                    return;
                }
                const activePlayerElem = document.querySelector('#ea-area-player-' + activePlayerId + ' .ea-area-player-board .ea-token-active-player.ea-player-board-active-player-action-' + mainActionId);
                if (activePlayerElem !== null) {
                    activePlayerElem.classList.remove('ea-token-inactive');
                }
            },
        });
    });