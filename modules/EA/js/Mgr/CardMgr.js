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
    g_gamethemeurl + "modules/BX/js/ModalDialog.js",
],
    function (dojo, declare) {
        return declare("ea.CardMgr", null, {
            CARD_ID_EARTH_BACK: 99999,
            CARD_ID_GAIA_BACK: 99998,
            SPROUT_SIZE: 55 * 0.75,
            SPROUT_PADDING: 7 * 0.75,

            setup(gamedatas) {
                const elemCreationElem = gameui.getElementCreationElement();
                for (const cardId in gamedatas.cards) {
                    const card = gamedatas.cards[cardId];
                    const cardElem = this.createCardElement(card, true);
                    elemCreationElem.appendChild(cardElem);
                }
            },

            canCardIdBeCompact(cardId) {
                if (cardId == this.CARD_ID_EARTH_BACK) {
                    return true;
                }
                if (!(cardId in gameui.gamedatas.carddefs)) {
                    return false;
                }
                return (gameui.gamedatas.carddefs[cardId].type < gameui.CARD_TYPE_FAUNA);
            },

            createCardDetailElementFromCardId(cardId) {
                const cardElement = document.createElement('div');
                cardElement.classList.add('ea-card');
                cardElement.dataset.cardId = cardId;
                return cardElement;
            },

            createCardBottomDetailElementFromCardId(cardId) {
                const cardElement = document.createElement('div');
                cardElement.classList.add('ea-card');
                cardElement.classList.add('ea-card-objective-bottom');
                cardElement.dataset.cardId = cardId;
                return cardElement;
            },

            createEarthBackCardElement(canBeCompact = false) {
                const cardElement = document.createElement('div');
                cardElement.classList.add('ea-card');
                cardElement.dataset.cardId = this.CARD_ID_EARTH_BACK;
                cardElement.dataset.isEarthBackCard = true;
                if (canBeCompact && gameui.usesCompactCards()) {
                    cardElement.classList.add('ea-card-compact');
                }
                return cardElement;
            },

            createGaiaBackCardElement() {
                const cardElement = document.createElement('div');
                cardElement.classList.add('ea-card');
                cardElement.dataset.cardId = this.CARD_ID_GAIA_BACK;
                cardElement.dataset.isGaiaBackCard = true;
                return cardElement;
            },

            createCardElementForSize() {
                return this.createCardElementFromCardId(this.CARD_ID_EARTH_BACK);
            },

            createCardElementFromCardId(cardId) {
                // Sprout container
                const sproutElem = document.createElement('div');
                sproutElem.classList.add('ea-card-sprout-container');
                if (cardId in gameui.gamedatas.carddefs) {
                    const sproutMax = gameui.gamedatas.carddefs[cardId].sproutMax;
                    if (sproutMax !== null && sproutMax > 0) {
                        const sproutWidth = sproutMax * this.SPROUT_SIZE + (sproutMax - 1) * this.SPROUT_PADDING;
                        sproutElem.style.width = 'calc(' + sproutWidth + 'px * var(--ea-zoom))';
                    }
                }

                // Growth container
                const growthElem = document.createElement('div');
                growthElem.classList.add('ea-card-growth-container');
                const growthCountElem = document.createElement('div');
                growthCountElem.classList.add('ea-counter');
                growthCountElem.innerText = '0';
                growthElem.appendChild(growthCountElem);

                // Cost container
                const costElem = document.createElement('div');
                costElem.classList.add('ea-card-cost');
                costElem.classList.add('ea-card-cost-inactive');
                const costCountElem = document.createElement('div');
                costCountElem.classList.add('ea-card-cost-count')
                const costSoilElem = document.createElement('div');
                costSoilElem.classList.add('ea-icon-soil')
                costElem.appendChild(costCountElem);
                costElem.appendChild(costSoilElem);

                const colorBlindContainer = document.createElement('div');
                colorBlindContainer.classList.add('ea-card-colorblind-container');
                if (cardId in gameui.gamedatas.carddefs) {
                    const cardDef = gameui.gamedatas.carddefs[cardId];
                    for (const ab of cardDef.abilities) {
                        const colorBlindElem = document.createElement('div');
                        switch (ab.color) {
                            case gameui.AB_COLOR_RED:
                                colorBlindElem.classList.add('ea-colorblind-red');
                                break;
                            case gameui.AB_COLOR_YELLOW:
                                colorBlindElem.classList.add('ea-colorblind-yellow');
                                break;
                            case gameui.AB_COLOR_BLUE:
                                colorBlindElem.classList.add('ea-colorblind-blue');
                                break;
                            case gameui.AB_COLOR_MULTICOLOR:
                                colorBlindElem.classList.add('ea-colorblind-multicolor');
                                break;
                            case gameui.AB_COLOR_GREEN:
                                colorBlindElem.classList.add('ea-colorblind-green');
                                break;
                            case gameui.AB_COLOR_BROWN:
                                colorBlindElem.classList.add('ea-colorblind-brown');
                                break;
                            case gameui.AB_COLOR_BLACK:
                                colorBlindElem.classList.add('ea-colorblind-black');
                                break;
                        }
                        if (colorBlindContainer.classList.length > 0) {
                            colorBlindContainer.appendChild(colorBlindElem);
                        }
                    }
                }

                // Card tag
                const cardTag = document.createElement('div');
                cardTag.classList.add('ea-card-tag');
                gameui.addBasicTooltipToElement(cardTag, _('Flag for cards in hand. Green are first in hand, then Brown, then Red.'));

                const cardElement = document.createElement('div');
                cardElement.classList.add('ea-card-selection');
                cardElement.dataset.cardId = cardId;
                if (!gameui.usesCompactCards() || !this.canCardIdBeCompact(cardId)) {
                    cardElement.classList.add('ea-card');
                    cardElement.appendChild(growthElem);
                    cardElement.appendChild(sproutElem);
                    cardElement.appendChild(costElem);
                    cardElement.appendChild(colorBlindContainer);
                    cardElement.appendChild(cardTag);
                } else {
                    cardElement.classList.add('ea-card-compact');
                    const cardTopElement = document.createElement('div');
                    cardTopElement.classList.add('ea-card');
                    cardTopElement.classList.add('ea-card-compact-top');
                    cardTopElement.dataset.cardId = cardId;
                    cardTopElement.appendChild(growthElem);
                    cardTopElement.appendChild(costElem);

                    const cardBottomElement = document.createElement('div');
                    cardBottomElement.classList.add('ea-card');
                    cardBottomElement.classList.add('ea-card-compact-bottom');
                    cardBottomElement.dataset.cardId = cardId;
                    cardBottomElement.appendChild(sproutElem);
                    cardBottomElement.appendChild(colorBlindContainer);
                    cardBottomElement.appendChild(cardTag);

                    cardElement.appendChild(cardTopElement);
                    cardElement.appendChild(cardBottomElement);
                }

                // Bottom element
                const bottomElement = document.createElement('div');
                bottomElement.classList.add('ea-card-bottom');
                const helpElement = document.createElement('div');
                helpElement.classList.add('ea-card-help');
                helpElement.addEventListener('click', () => {
                    gameui.cardDetailMgr.show(helpElement);
                });
                bottomElement.appendChild(helpElement);
                gameui.addBasicTooltipToElement(helpElement, _('Open card detail'));

                const scoreLineElement = document.createElement('div');
                scoreLineElement.classList.add('ea-card-score-line');
                scoreLineElement.classList.add('bx-hidden');
                bottomElement.appendChild(scoreLineElement);
                gameui.addBasicTooltipToElement(scoreLineElement, _('Show score lines for card'));

                const arrows = [];
                const removes = [];
                for (let i = 0; i < 3; ++i) {
                    const arrow = gameui.createFAIcon('flag');
                    arrow.classList.add('ea-card-tag-add');
                    arrow.dataset.cardTag = i;
                    bottomElement.appendChild(arrow);
                    arrows.push(arrow);
                    const remove = gameui.createFAIcon('times-circle');
                    remove.classList.add('ea-card-tag-remove');
                    remove.dataset.cardTag = i;
                    bottomElement.appendChild(remove);
                    removes.push(remove);
                }
                gameui.addBasicTooltipToElement(arrows, _('Flags cards in hand. Green are first in hand, then Brown, then Red.'));
                gameui.addBasicTooltipToElement(removes, _('Remove Flags from cards in hand'));

                const containerElement = document.createElement('div');
                containerElement.classList.add('ea-card-container');
                containerElement.dataset.cardId = cardId;

                containerElement.appendChild(cardElement);
                containerElement.appendChild(bottomElement);
                return containerElement;
            },

            createCardElementForSelectionFromCardId(cardId) {
                const cardElement = document.createElement('div');
                cardElement.classList.add('ea-card-selection');
                cardElement.dataset.cardId = cardId;
                if (!gameui.usesCompactCards() || !this.canCardIdBeCompact(cardId)) {
                    cardElement.classList.add('ea-card');
                } else {
                    cardElement.classList.add('ea-card-compact');
                    const cardTopElement = document.createElement('div');
                    cardTopElement.classList.add('ea-card');
                    cardTopElement.classList.add('ea-card-compact-top');
                    cardTopElement.dataset.cardId = cardId;

                    const cardBottomElement = document.createElement('div');
                    cardBottomElement.classList.add('ea-card');
                    cardBottomElement.classList.add('ea-card-compact-bottom');
                    cardBottomElement.dataset.cardId = cardId;

                    cardElement.appendChild(cardTopElement);
                    cardElement.appendChild(cardBottomElement);
                }
                return cardElement;
            },

            createCardElement(card, setId = false) {
                const element = this.createCardElementFromCardId(card.cardId);
                if (setId) {
                    element.id = 'ea-card-id-' + card.cardId;
                }
                return element;
            },

            getCardElementById(cardId) {
                return document.getElementById('ea-card-id-' + cardId);
            },

            getCardSelectionElementById(cardId) {
                return document.querySelector('#ea-card-id-' + cardId + ' .ea-card-selection');
            },

            getCardSproutContainerElementById(cardId) {
                return document.querySelector('#ea-card-id-' + cardId + ' .ea-card-sprout-container');
            },

            getCardGrowthContainerElementById(cardId) {
                return document.querySelector('#ea-card-id-' + cardId + ' .ea-card-growth-container');
            },

            getCardCostContainerElementById(cardId) {
                return document.querySelector('#ea-card-id-' + cardId + ' .ea-card-cost');
            },

            getCardCostCountElementById(cardId) {
                return document.querySelector('#ea-card-id-' + cardId + ' .ea-card-cost .ea-card-cost-count');
            },

            hideAllCardCost() {
                for (const costElem of document.querySelectorAll('.ea-card-cost')) {
                    costElem.classList.add('ea-card-cost-inactive');
                }
            },

            addScoreToCardId(cardId, score, color = null) {
                const scoreElem = document.createElement('div');
                scoreElem.classList.add('ea-card-score');
                if (color !== null) {
                    scoreElem.style.color = '#' + color;
                }
                scoreElem.innerText = '+' + score;
                const cardElem = this.getCardSelectionElementById(cardId);
                const cardTopElem = cardElem.querySelector('.ea-card-compact-top');
                if (cardTopElem !== null) {
                    cardTopElem.appendChild(scoreElem);
                } else {
                    cardElem.appendChild(scoreElem);
                }
            },
        });
    });