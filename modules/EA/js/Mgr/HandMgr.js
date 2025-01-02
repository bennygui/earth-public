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
    g_gamethemeurl + "modules/BX/js/DragScroller.js",
],
    function (dojo, declare) {
        return declare("ea.HandMgr", null, {
            HAND_PLACEMENT_BUTTON_IDS: [
                'ea-card-hand-button-fixed',
                'ea-card-hand-button-above',
                'ea-card-hand-button-below',
            ],

            HAND_PLACEMENT_CLASSES: [
                ['ea-hand-one-line', 'ea-hand-compact', 'ea-hand-float',],
                [],
                ['ea-hand-below-tableau'],
            ],

            CARD_TAG_ORDER: {
                0: 0,
                1: 1,
                2: 3,
                999: 2,
            },

            setup(gamedatas) {
                this.handOrder = {};
                this.cardTag = {}
                this.dragScroller = new bx.DragScroller(this.getCardHandElement());

                // Hide hand of card for spectators
                if (!(gameui.player_id in gamedatas.players)) {
                    this.getCardHandContainerElement().classList.add('bx-hidden');
                }
                // Zoom slider
                const sliderElem = this.getCardHandZoomSliderElement();
                sliderElem.addEventListener('input', () => {
                    gameui.setLocalPreference(gameui.EA_PREF_HAND_ZOOM_FACTOR_ID, parseInt(sliderElem.value));
                });
                gameui.addBasicTooltipToElement(sliderElem, _('Change the size of cards in hand'));

                // Hand placement buttons
                const HAND_PLACEMENT_TOOLTIPS = [
                    _('Move hand of cards at a fixed position that moves with the page'),
                    _('Move hand of cards at the top of the page, above your tableau and your player board'),
                    _('Move hand of cards below your tableau and your player board'),
                ]
                for (const idx in this.HAND_PLACEMENT_BUTTON_IDS) {
                    const button = document.getElementById(this.HAND_PLACEMENT_BUTTON_IDS[idx]);
                    button.addEventListener("click", () => {
                        gameui.setLocalPreference(gameui.EA_PREF_HAND_PLACEMENT_ID, idx);
                    });
                    gameui.addBasicTooltipToElement(button, HAND_PLACEMENT_TOOLTIPS[idx]);
                }

                // Open/Close config
                const configController = document.getElementById('ea-area-card-hand-config-controller');
                configController.addEventListener("click", () => {
                    this.getCardHandContainerElement().classList.toggle('ea-config-closed');
                });
                gameui.addBasicTooltipToElement(configController, _('Show display options for cards in hand'));

                // Move cards to hand
                for (const cardId in gamedatas.cards) {
                    const card = gamedatas.cards[cardId];
                    if (card.locationId == gameui.CARD_LOCATION_HAND) {
                        this.moveCardIdToHand(cardId, card.locationX, true);
                    } else if (card.locationId == gameui.CARD_LOCATION_END_TURN) {
                        this.moveCardIdToEndTurn(cardId, card.locationOrder, true);
                    }
                }
                for (const playerId in gamedatas.cardCounts.handCountByPlayerId) {
                    gameui.counters[playerId].hand.setValue(gamedatas.cardCounts.handCountByPlayerId[playerId]);
                }
                this.updateCardTag(gamedatas.cardTags, true)
            },

            getCardTag(cardId) {
                if (cardId in this.cardTag) {
                    return this.cardTag[cardId];
                }
                return null;
            },

            updateCardTag(cardTags, isInstantaneous = false) {
                const changedCardIds = [];
                const prevCardTag = this.cardTag;
                this.cardTag = {}
                for (const id in cardTags) {
                    const tag = cardTags[id];
                    if (!(tag.cardId in prevCardTag) || prevCardTag[tag.cardId] != tag.cardTag) {
                        changedCardIds.push(tag.cardId);
                    }
                    delete prevCardTag[tag.cardId];
                    this.cardTag[tag.cardId] = parseInt(tag.cardTag);
                }
                for (const cardId in prevCardTag) {
                    changedCardIds.push(cardId);
                }
                gameui.cardDetailMgr.adaptTags();
                if (isInstantaneous || changedCardIds.length != 1) {
                    return this.sortHand();
                }
                const cardId = changedCardIds[0];
                const handElem = this.getCardHandElement();
                const taggedCardElem = handElem.querySelector('.ea-card-container[data-card-id="' + cardId + '"]');
                if (taggedCardElem === null) {
                    return this.sortHand();
                }
                const cardElemArray = [];
                for (const cardElem of handElem.querySelectorAll('.ea-card-container')) {
                    if (cardElem.classList.contains('bx-moving')) {
                        continue;
                    }
                    cardElemArray.push(cardElem);
                }
                const idxBeforeSort = cardElemArray.indexOf(taggedCardElem)
                this.sortHandElements(cardElemArray);
                const idxAfterSort = cardElemArray.indexOf(taggedCardElem)
                if (idxAfterSort < 0 || idxBeforeSort == idxAfterSort) {
                    return this.sortHand();
                }
                const beforeTaggedCardElem = (idxAfterSort == taggedCardElem.length - 1 ? handElem.lastElementChild : cardElemArray[idxAfterSort + 1]);
                return gameui.slide(taggedCardElem, handElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                    beforeBrother: beforeTaggedCardElem,
                }).then(() => this.sortHand());
            },

            addTagClick() {
                const handElem = this.getCardHandElement();
                const tagElems = Array.from(
                    handElem.querySelectorAll('.ea-card-bottom .ea-card-tag-add')
                ).concat(
                    Array.from(
                        handElem.querySelectorAll('.ea-card-bottom .ea-card-tag-remove')
                    )
                );
                for (const tagElem of tagElems) {
                    gameui.addClickable(
                        tagElem,
                        () => {
                            gameui.serverAction('tagHandCard', {
                                cardId: tagElem.closest('.ea-card-container').dataset.cardId,
                                cardTag: tagElem.dataset.cardTag,
                            });
                        },
                        {
                            border: false,
                        }
                    );
                }
            },

            getCardHandElement() {
                return document.getElementById('ea-area-card-hand');
            },

            getCardEndTurnElement() {
                return document.getElementById('ea-area-card-end-turn-container');
            },

            getCardHandContainerElement() {
                return document.getElementById('ea-area-card-hand-container');
            },

            getCardHandZoomSliderElement() {
                return document.getElementById('ea-card-hand-slider');
            },

            getCardHandCardIds() {
                return Array.from(document.querySelectorAll('#ea-area-card-hand .ea-card-container')).map((e) => e.dataset.cardId);
            },

            moveCardIdToHand(cardId, order, isInstantaneous = false) {
                const handElem = this.getCardHandElement();
                const cardElem = gameui.cardMgr.getCardElementById(cardId);
                this.handOrder[cardId] = parseInt(order);
                return gameui.slide(cardElem, handElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                }).then(() => this.sortHand());
            },

            sortHandElements(cardElemArray) {
                cardElemArray.sort((c1, c2) => {
                    let tag1 = 999;
                    if (c1.dataset.cardId in this.cardTag) {
                        tag1 = this.cardTag[c1.dataset.cardId];
                    }
                    let tag2 = 999;
                    if (c2.dataset.cardId in this.cardTag) {
                        tag2 = this.cardTag[c2.dataset.cardId];
                    }
                    const tagCmp = this.CARD_TAG_ORDER[tag1] - this.CARD_TAG_ORDER[tag2];
                    if (tagCmp != 0) {
                        return tagCmp;
                    }
                    return this.handOrder[c1.dataset.cardId] - this.handOrder[c2.dataset.cardId];
                });
            },

            sortHand() {
                const handElem = this.getCardHandElement();
                const cardElemArray = [];
                for (const cardElem of handElem.querySelectorAll('.ea-card-container')) {
                    delete cardElem.dataset.cardTag;
                    if (cardElem.classList.contains('bx-moving')) {
                        continue;
                    }
                    cardElemArray.push(cardElem);
                    cardElem.remove();
                }
                this.sortHandElements(cardElemArray);
                for (const cardElem of cardElemArray) {
                    handElem.appendChild(cardElem);
                    const cardId = cardElem.dataset.cardId;
                    if (cardId in this.cardTag) {
                        const tag = this.cardTag[cardId];
                        cardElem.dataset.cardTag = tag;
                    }
                }
                return Promise.resolve();
            },

            moveCardIdToEndTurn(cardId, order, isInstantaneous = false) {
                const endTurnElem = this.getCardEndTurnElement();
                const cardElem = gameui.cardMgr.getCardElementById(cardId);
                cardElem.style.setProperty('--ea-end-turn-order', order);
                return gameui.slide(cardElem, endTurnElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            setZoomFactor(value) {
                this.getCardHandElement().style.setProperty('--ea-zoom', value / 100);
                this.getCardEndTurnElement().style.setProperty('--ea-zoom', value / 100);
                this.getCardHandZoomSliderElement().value = value;
            },

            setHandPlacement(value) {
                if (this.dragScroller !== undefined && this.dragScroller !== null) {
                    if (value == gameui.EA_PREF_HAND_PLACEMENT_VALUE_FIXED) {
                        this.dragScroller.enable();
                    } else {
                        this.dragScroller.disable();
                    }
                }
                for (const idx in this.HAND_PLACEMENT_BUTTON_IDS) {
                    const button = document.getElementById(this.HAND_PLACEMENT_BUTTON_IDS[idx]);
                    button.classList.remove('ea-ui-selected');
                }
                document.getElementById(this.HAND_PLACEMENT_BUTTON_IDS[value]).classList.add('ea-ui-selected');
                const elem = this.getCardHandContainerElement();
                for (const list of this.HAND_PLACEMENT_CLASSES) {
                    for (const cssClass of list) {
                        elem.classList.remove(cssClass);
                    }
                }
                for (const cssClass of this.HAND_PLACEMENT_CLASSES[value]) {
                    elem.classList.add(cssClass);
                }
            },
        });
    });