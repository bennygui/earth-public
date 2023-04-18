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
    g_gamethemeurl + "modules/BX/js/Numbers.js",
],
    function (dojo, declare) {
        return declare("ea.DeckMgr", null, {
            MAX_CARDS: 5,

            constructor() {
                this.deckCounter = bx.Numbers();
                this.discardCounter = bx.Numbers();
            },

            setup(gamedatas) {
                this.deckCounter.addTarget(this.getDeckCountElement());
                this.discardCounter.addTarget(this.getDiscardCountElement());
                this.updateDeckCount(gamedatas.cardCounts.deckCount, true);
                this.updateDiscardCount(gamedatas.cardCounts.discardCount, true);
            },

            getAreaDeckCardsElement() {
                return document.getElementById('ea-area-deck-cards');
            },

            getDeckCountElement() {
                return document.getElementById('ea-deck-count-number');
            },

            updateDeckCount(deckCount, isInstantaneous = false) {
                this.updateCount(deckCount, this.deckCounter, this.getAreaDeckCardsElement(), 1, isInstantaneous);
            },

            getAreaDiscardCardsElement() {
                return document.getElementById('ea-area-discard-cards');
            },

            getDiscardCountElement() {
                return document.getElementById('ea-discard-count-number');
            },

            updateDiscardCount(discardCount, isInstantaneous = false) {
                this.updateCount(discardCount, this.discardCounter, this.getAreaDiscardCardsElement(), discardCount == 0 ? 1 : 2, isInstantaneous);
            },

            updateCount(count, counter, areaElement, minCount, isInstantaneous) {
                counter.toValue(count, isInstantaneous);
                for (const c of areaElement.querySelectorAll('.ea-card[data-is-earth-back-card="true"]')) {
                    c.remove();
                }
                for (let i = 0; i < Math.max(minCount, Math.min(count, this.MAX_CARDS)); ++i) {
                    const card = gameui.cardMgr.createEarthBackCardElement();
                    areaElement.insertBefore(card, areaElement.firstChild);
                }
            },

            moveElementToDeck(element, isInstantaneous = false) {
                const areaElem = this.getAreaDeckCardsElement();
                return gameui.slide(element, areaElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            moveCardIdToDeck(cardId, isInstantaneous = false) {
                const cardElem = gameui.cardMgr.getCardElementById(cardId);
                return this.moveElementToDeck(cardElem, isInstantaneous);
            },

            moveElementToDiscard(element, isInstantaneous = false) {
                const areaElem = this.getAreaDiscardCardsElement();
                return gameui.slide(element, areaElem, {
                    phantom: false,
                    isInstantaneous: isInstantaneous,
                }).then(() => {
                    element.remove();
                });
            },

            moveCardIdToDiscard(cardId, isInstantaneous = false) {
                const cardElem = gameui.cardMgr.getCardElementById(cardId);
                return this.moveElementToDiscard(cardElem, isInstantaneous);
            },
        });
    });