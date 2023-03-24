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
        return declare("ea.CardDetailMgr", null, {
            DIALOG_ID: 'ea-dialog-card-detail',
            DIALOG_CONTENT: `
                <div class='ea-dialog-card-detail-grid'>
                    <div class='ea-dialog-card-detail-jumper'></div>
                    <div class='ea-dialog-card-detail-left'><i class='fa fa-arrow-left'></i></div>
                    <div class='ea-dialog-card-detail-right'><i class='fa fa-arrow-right'></i></div>
                    <div class='ea-dialog-card-detail-container'></div>
                    <div class='ea-dialog-card-detail-flag'></div>
                    <div class='ea-dialog-card-detail-description'></div>
                </div>
            `,
            JUMP_TYPE_HAND: 0,
            JUMP_TYPE_TABLEAU: 1,
            JUMP_TYPE_BOARD: 2,
            JUMP_TYPE_EVENT: 3,
            JUMP_TYPE_FAUNA: 4,
            JUMP_TYPE_GAIA_TABLEAU: 5,
            JUMP_TYPE_GAIA_DISCARD: 6,

            constructor() {
                this.dialog = null;
                this.isFromHand = false;
                this.playerId = null;
                this.cardIds = [];
                this.currentCardId = null;
                this.currentJumpType = null;
                this.arrowListener = [];
            },

            show(startElement, playerId = null) {
                return this.hide().then(() => {
                    this.init(startElement, playerId);
                    this.dialog = new bx.ModalDialog(this.DIALOG_ID, {
                        title: _('Card:'),
                        animationDuration: 100,
                        onShow: () => this.showCurrentCard(),
                        onKeyPress: (e) => this.onKeyPress(e),
                        contentsTpl: this.DIALOG_CONTENT,
                    });
                    return this.dialog.show();
                });
            },

            hide() {
                this.cardIds = [];
                this.currentCardId = null;
                for (const listener of this.arrowListener) {
                    dojo.disconnect(listener);
                }
                this.arrowListener = [];

                const localDialog = this.dialog;
                this.dialog = null;
                if (localDialog !== null && localDialog.id !== null) {
                    return localDialog.destroy();
                } else {
                    return Promise.resolve();
                }
            },

            init(startElement, playerId = null) {
                this.playerId = playerId;
                if (this.playerId === null) {
                    this.playerId = this.getPlayerIdFromStartElement(startElement);
                }
                this.isFromHand = (startElement.closest('#ea-area-card-hand') !== null);
                this.cardIds = this.getCardIdsFromStartElement(startElement);
                this.currentCardId = this.getCurrentCardIdFromStartElement(startElement);
                this.currentJumpType = this.getCurrentJumpTypeFromStartElement(startElement);
            },

            adaptJumper() {
                const jumperElem = document.querySelector('#popin_' + this.DIALOG_ID + ' .ea-dialog-card-detail-jumper');
                jumperElem.innerHTML = '';
                if (this.playerId in gameui.gamedatas.players) {
                    const elem = document.createElement('div');
                    elem.classList.add('ea-dialog-card-detail-player-name');
                    elem.innerText = gameui.gamedatas.players[this.playerId].player_name;
                    jumperElem.appendChild(elem);
                }
                const JUMP = [
                    [this.JUMP_TYPE_HAND, _('Hand'), '#ea-area-card-hand-container'],
                    [this.JUMP_TYPE_TABLEAU, _('Tableau'), '#ea-area-player-' + this.playerId + ' .ea-area-player-tableau-container'],
                    [this.JUMP_TYPE_BOARD, _('Board'), '#ea-area-player-' + this.playerId + ' .ea-player-board .ea-player-board-card'],
                    [this.JUMP_TYPE_EVENT, _('Event'), '#ea-area-player-' + this.playerId + ' .ea-player-board .ea-player-board-event'],
                    [this.JUMP_TYPE_FAUNA, _('Fauna'), '#ea-fauna-board'],
                ];
                if (gameui.isGameSolo()) {
                    JUMP.push([this.JUMP_TYPE_GAIA_TABLEAU, _('Gaia Earth'), '#ea-gaia-board-tableau']);
                    JUMP.push([this.JUMP_TYPE_GAIA_DISCARD, _('Gaia Cards'), '#ea-gaia-board-gaia-card']);
                }
                for (const jump of JUMP) {
                    const jumpType = jump[0];
                    const title = jump[1];
                    const selector = jump[2];
                    if (jumpType == this.JUMP_TYPE_HAND && this.playerId != gameui.player_id) {
                        continue;
                    }
                    if (this.displayJumperElement(selector) === null) {
                        continue;
                    }
                    const elem = document.createElement('div');
                    elem.innerText = title;
                    if (this.currentJumpType == jumpType) {
                        elem.classList.add('ea-dialog-card-detail-jump-selected');
                    }
                    elem.addEventListener('click', () => {
                        const firstHelp = this.displayJumperElement(selector);
                        if (firstHelp !== null) {
                            this.init(firstHelp, this.playerId);
                            this.showCurrentCard();
                        }
                    });
                    jumperElem.appendChild(elem);
                }
            },

            displayJumperElement(selector) {
                const helpElem = document.querySelector(selector + ' .ea-card-help');
                const cardElem = document.querySelector(selector + ' .ea-card-container');
                if (helpElem !== null && cardElem !== null) {
                    return helpElem;
                }
                return null;
            },

            adaptTags() {
                const flagElem = document.querySelector('#popin_' + this.DIALOG_ID + ' .ea-dialog-card-detail-flag');
                if (flagElem === null) {
                    return;
                }
                flagElem.innerHTML = '';
                const cardElem = document.querySelector('#popin_' + this.DIALOG_ID + ' .ea-dialog-card-detail-container .ea-card');
                if (cardElem !== null) {
                    const arrow = cardElem.querySelector('.ea-card-tag-add');
                    if (arrow !== null) {
                        arrow.remove();
                    }
                }
                if (!(this.playerId in gameui.gamedatas.players)) {
                    return;
                }
                if (!this.isFromHand) {
                    return;
                }
                const cardTag = gameui.handMgr.getCardTag(this.currentCardId);
                const arrows = [];
                const removes = [];
                for (let i = 0; i < 3; ++i) {
                    const arrow = gameui.createFAIcon('flag');
                    arrow.classList.add('ea-card-tag-add');
                    arrow.dataset.cardTag = i;
                    arrow.addEventListener('click', () => {
                        gameui.serverAction('tagHandCard', {
                            cardId: this.currentCardId,
                            cardTag: i,
                        });
                    });
                    arrows.push(arrow);
                    if (cardTag !== i) {
                        flagElem.appendChild(arrow);
                    } else {
                        arrow.classList.add('ea-dialog-card-detail-tag-float');
                        cardElem.appendChild(arrow);

                        const remove = gameui.createFAIcon('times-circle');
                        remove.classList.add('ea-card-tag-remove');
                        remove.dataset.cardTag = i;
                        remove.addEventListener('click', () => {
                            gameui.serverAction('tagHandCard', {
                                cardId: this.currentCardId,
                                cardTag: i,
                            });
                        });
                        flagElem.appendChild(remove);
                        removes.push(remove);
                    }
                }
                gameui.addBasicTooltipToElement(arrows, _('Flags cards in hand. Green are first in hand, then Brown, then Red.'));
                gameui.addBasicTooltipToElement(removes, _('Remove Flags from cards in hand'));
            },

            showCurrentCard() {
                this.adaptJumper();

                const gridElem = document.querySelector('#popin_' + this.DIALOG_ID + ' .ea-dialog-card-detail-grid');
                const containerElem = document.querySelector('#popin_' + this.DIALOG_ID + ' .ea-dialog-card-detail-container');
                const leftElem = document.querySelector('#popin_' + this.DIALOG_ID + ' .ea-dialog-card-detail-left');
                const rightElem = document.querySelector('#popin_' + this.DIALOG_ID + ' .ea-dialog-card-detail-right');

                const gridWidth = Math.min(1000, (document.body.offsetWidth * 0.7))
                    - leftElem.offsetWidth * 1.2
                    - rightElem.offsetWidth * 1.2;
                gridElem.style.gridTemplateColumns = 'min-content ' + (gridWidth) + 'px min-content';
                let zoom = Math.min(
                    Math.min(containerElem.offsetWidth, gridWidth) / gameui.CARD_WIDTH,
                    (window.innerHeight * 0.6) / gameui.CARD_HEIGHT
                );
                if (zoom > 1) {
                    zoom = 1;
                }
                containerElem.innerHTML = '';
                const cardElem = gameui.cardMgr.createCardDetailElementFromCardId(this.currentCardId)
                cardElem.style.setProperty('--ea-zoom', zoom);
                containerElem.appendChild(cardElem);

                const cardDef = gameui.gamedatas.carddefs[this.currentCardId];
                const titleElem = document.querySelector('#popin_' + this.DIALOG_ID + '_title');
                titleElem.innerHTML = _('Card:') + ' ' + _(cardDef.name);

                const descElem = document.querySelector('#popin_' + this.DIALOG_ID + ' .ea-dialog-card-detail-description');
                descElem.innerHTML = '';
                descElem.appendChild(this.buildCardDescription(cardDef));
                if (cardDef.scienceName !== null) {
                    const scienceElem = document.createElement('p');
                    scienceElem.innerHTML = _('Scientific name:') + ' <i>' + cardDef.scienceName + '</i>';
                    descElem.appendChild(scienceElem);
                }

                if (this.arrowListener.length == 0) {
                    this.arrowListener.push(dojo.connect(leftElem, 'click', () => {
                        this.currentCardId = this.nextLeftCard();
                        this.showCurrentCard();
                    }));
                    this.arrowListener.push(dojo.connect(rightElem, 'click', () => {
                        this.currentCardId = this.nextRightCard();
                        this.showCurrentCard();
                    }));
                }

                leftElem.style.visibility = 'hidden';
                rightElem.style.visibility = 'hidden';
                if (this.hasLeftCard()) {
                    leftElem.style.visibility = 'visible';
                }
                if (this.hasRightCard()) {
                    rightElem.style.visibility = 'visible';
                }

                this.adaptTags();
            },

            buildCardDescription(cardDef) {
                const abilities = [];
                for (const ab of cardDef.abilities) {
                    let line = null;
                    switch (ab.color) {
                        case gameui.AB_COLOR_RED:
                            line = _("Red (Compost)");
                            break;
                        case gameui.AB_COLOR_YELLOW:
                            line = _("Yellow (Grow)");
                            break;
                        case gameui.AB_COLOR_BLUE:
                            line = _("Blue (Water)");
                            break;
                        case gameui.AB_COLOR_MULTICOLOR:
                            line = _("Multicolor (Blue, Yellow, Red)");
                            break;
                        case gameui.AB_COLOR_GREEN:
                            line = _("Green (Plant)");
                            break;
                        case gameui.AB_COLOR_BROWN:
                            if (cardDef.type == gameui.CARD_TYPE_GAIA) {
                                line = _("Gaia Fauna objective");
                            } else if (ab.scores.length == 0) {
                                line = _("Brown (in-game passive effect)");
                            } else {
                                line = _("Brown (end game scoring)");
                            }
                            break;
                        case gameui.AB_COLOR_BLACK:
                            line = _("Black (instant benefit)");
                            break;
                        default:
                            if (cardDef.type == gameui.CARD_TYPE_ECOSYSTEM) {
                                line = _("Ecosystem (end game scoring)");
                            } else if (cardDef.type == gameui.CARD_TYPE_FAUNA) {
                                line = _("Fauna (in-game scoring objectives)");
                            } else if (cardDef.type == gameui.CARD_TYPE_GAIA) {
                                line = _("Gaia (solo mode opponent)");
                            } else {
                                line = '';
                            }
                    }
                    if (ab.description !== null) {
                        line += ': ' + _(ab.description);
                    } else {
                        line += ': ';

                        const payArray = [];
                        for (const pay of ab.payments) {
                            payArray.push(this.getAbilityString(pay.ability, pay.count));
                        }
                        if (payArray.length > 0) {
                            line += _('Pay: ') + ' ' + payArray.join(', ') + '.';
                        }

                        const gainArray = [];
                        for (const gain of ab.gains) {
                            gainArray.push(this.getAbilityString(gain.ability, gain.count));
                        }
                        if (gainArray.length > 0) {
                            line += ' ' + _('Gain:') + ' ' + gainArray.join(', ') + '.';
                        }
                    }
                    if (ab.clarification) {
                        line += ' ' + _('Clarification:') + ' ' + _(ab.clarification);
                    }
                    abilities.push(line);
                }
                if (abilities.length == 0) {
                    return document.createElement('div');
                } else if (abilities.length == 1) {
                    const p = document.createElement('p');
                    p.innerText = _('Ability:') + ' ' + abilities[0];
                    return p;
                } else {
                    const p = document.createElement('p');
                    p.innerText = _('Abilities:')
                    const ul = document.createElement('ul');
                    for (const ab of abilities) {
                        const li = document.createElement('li');
                        li.innerText = ab;
                        ul.appendChild(li);
                    }
                    p.appendChild(ul);
                    return p;
                }
            },

            getAbilityString(abilityId, count) {
                switch (abilityId) {
                    case gameui.ABILITY_DRAW_CARD_FROM_DECK:
                        return gameui.format_string_recursive(_('draw ${count} card(s)'), { count: count });
                    case gameui.ABILITY_GROWTH:
                        return gameui.format_string_recursive(_('${count} growth'), { count: count });
                    case gameui.ABILITY_SOIL:
                        return gameui.format_string_recursive(_('${count} soil'), { count: count });
                    case gameui.ABILITY_SPROUT:
                        return gameui.format_string_recursive(_('${count} sprout(s)'), { count: count });
                    case gameui.ABILITY_COMPOST_FROM_HAND:
                        return gameui.format_string_recursive(_('compost ${count} card(s) from your hand'), { count: count });
                    case gameui.ABILITY_COMPOST_FROM_DECK:
                        return gameui.format_string_recursive(_('compost ${count} card(s) from the deck'), { count: count });
                    case gameui.ABILITY_COMPOST_DESTROY:
                        return gameui.format_string_recursive(_('discard ${count} card(s) from your compost'), { count: count });
                }
                return '';
            },

            getPlayerIdFromStartElement(startElement) {
                const handElem = startElement.closest('#ea-area-card-hand');
                if (handElem !== null) {
                    return gameui.player_id;
                }
                const playerAreaElem = startElement.closest('.ea-area-player');
                if (playerAreaElem !== null) {
                    return playerAreaElem.dataset.playerId;
                }
                return gameui.player_id;
            },

            getCurrentJumpTypeFromStartElement(startElement) {
                const handElem = startElement.closest('#ea-area-card-hand');
                if (handElem !== null) {
                    return this.JUMP_TYPE_HAND;
                }
                const playerAreaElem = startElement.closest('.ea-area-player');
                const eventElem = startElement.closest('.ea-player-board-event');
                if (eventElem !== null) {
                    return this.JUMP_TYPE_EVENT;
                }
                const tableauElem = startElement.closest('.ea-area-player-tableau');
                if (tableauElem !== null) {
                    return this.JUMP_TYPE_TABLEAU;
                }
                if (playerAreaElem !== null) {
                    return this.JUMP_TYPE_BOARD;
                }
                const faunaBoardElem = startElement.closest('#ea-area-fauna-board');
                if (faunaBoardElem !== null) {
                    return this.JUMP_TYPE_FAUNA;
                }
                const gaiaTableauElem = startElement.closest('#ea-gaia-board-tableau');
                if (gaiaTableauElem !== null) {
                    return this.JUMP_TYPE_GAIA_TABLEAU;
                }
                const gaiaDiscardElem = startElement.closest('#ea-gaia-board-gaia-card');
                if (gaiaDiscardElem !== null) {
                    return this.JUMP_TYPE_GAIA_DISCARD;
                }
                debug('Card from unknown zone...');
                return null;
            },

            getCardIdsFromStartElement(startElement) {
                const handElem = startElement.closest('#ea-area-card-hand');
                if (handElem !== null) {
                    return Array.from(handElem.querySelectorAll('.ea-card-container')).map((e) => e.dataset.cardId);
                }
                const gaiaTableauElem = startElement.closest('#ea-gaia-board-tableau');
                if (gaiaTableauElem !== null) {
                    return Array.from(gameui.gaiaBoardMgr.getGaiaTableauCardIds());
                }
                const gaiaDiscardElem = startElement.closest('#ea-gaia-board-gaia-card');
                if (gaiaDiscardElem !== null) {
                    const cardIds = Array.from(gaiaDiscardElem.querySelectorAll('.ea-card-container')).map((e) => e.dataset.cardId);
                    cardIds.reverse();
                    return cardIds;
                }
                const playerAreaElem = startElement.closest('.ea-area-player');
                const eventElem = startElement.closest('.ea-player-board-event');
                if (eventElem !== null) {
                    return Array.from(gameui.playerBoardMgr.getEventCardIds(playerAreaElem.dataset.playerId));
                }
                const tableauElem = startElement.closest('.ea-area-player-tableau');
                if (tableauElem !== null) {
                    return Array.from(gameui.tableauMgr.getTableauCardIds(playerAreaElem.dataset.playerId));
                }
                if (playerAreaElem !== null) {
                    return Array.from(
                        Array(3).keys().map((i) => playerAreaElem.querySelector('.ea-player-board-card-' + i + ' .ea-card-container'))
                    ).filter((e) => e !== null).map((e) => e.dataset.cardId);
                }
                const faunaBoardElem = startElement.closest('#ea-area-fauna-board');
                if (faunaBoardElem !== null) {
                    return [
                        document.querySelector('#ea-fauna-board-fauna-card-0-0 .ea-card-container'),
                        document.querySelector('#ea-fauna-board-fauna-card-0-1 .ea-card-container'),
                        document.querySelector('#ea-fauna-board-ecosystem-card-0 .ea-card-container'),
                        document.querySelector('#ea-fauna-board-fauna-card-1-0 .ea-card-container'),
                        document.querySelector('#ea-fauna-board-fauna-card-1-1 .ea-card-container'),
                        document.querySelector('#ea-fauna-board-ecosystem-card-1 .ea-card-container'),
                    ].filter((e) => e !== null).map((e) => e.dataset.cardId);
                }
                debug('Card from unknown zone...');
                return [];
            },

            getCurrentCardIdFromStartElement(startElement) {
                const playerAreaElem = startElement.closest('.ea-area-player');
                const eventElem = startElement.closest('.ea-player-board-event');
                if (eventElem !== null) {
                    const cardIds = gameui.playerBoardMgr.getEventCardIds(playerAreaElem.dataset.playerId);
                    if (cardIds.length > 0) {
                        return cardIds[0];
                    } else {
                        return null;
                    }
                }
                const gaiaTableauElem = startElement.closest('#ea-gaia-board-tableau');
                if (gaiaTableauElem !== null) {
                    const cardIds = gameui.gaiaBoardMgr.getGaiaTableauCardIds();
                    if (cardIds.length > 0) {
                        return cardIds[0];
                    } else {
                        return null;
                    }
                }
                const gaiaDiscardElem = startElement.closest('#ea-gaia-board-gaia-card');
                if (gaiaDiscardElem !== null) {
                    const cardIds = Array.from(gaiaDiscardElem.querySelectorAll('.ea-card-container')).map((e) => e.dataset.cardId);
                    if (cardIds.length > 0) {
                        return cardIds[cardIds.length - 1];
                    } else {
                        return null;
                    }
                }
                let elem = startElement;
                while (elem !== null) {
                    if (elem.dataset.cardId !== undefined) {
                        return elem.dataset.cardId;
                    }
                    elem = elem.parentElement;
                }
                return null;
            },

            hasLeftCard() {
                return (this.nextLeftCard() != this.currentCardId);
            },

            hasRightCard() {
                return (this.nextRightCard() != this.currentCardId);
            },

            nextLeftCard() {
                if (this.cardIds.length == 0) {
                    return this.currentCardId;
                }
                const idx = this.cardIds.map((id) => parseInt(id)).indexOf(parseInt(this.currentCardId));
                if (idx <= 0) {
                    return this.currentCardId;
                } else {
                    return this.cardIds[idx - 1];
                }
            },

            nextRightCard() {
                if (this.cardIds.length == 0) {
                    return this.currentCardId;
                }
                const idx = this.cardIds.map((id) => parseInt(id)).indexOf(parseInt(this.currentCardId));
                if (idx < 0 || idx >= this.cardIds.length - 1) {
                    return this.currentCardId;
                } else {
                    return this.cardIds[idx + 1];
                }
            },

            onKeyPress(e) {
                switch (e.key) {
                    case 'ArrowLeft':
                        this.currentCardId = this.nextLeftCard();
                        this.showCurrentCard();
                        break;
                    case 'ArrowRight':
                        this.currentCardId = this.nextRightCard();
                        this.showCurrentCard();
                        break;
                }
            },
        });
    });