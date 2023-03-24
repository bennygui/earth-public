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
        return declare("ea.ObjectiveDetailMgr", null, {
            DIALOG_ID: 'ea-dialog-objective-detail',
            DIALOG_CONTENT: `
                <div class='ea-dialog-objective-detail-player'>
                    <div class='ea-dialog-objective-detail-player-left'><i class='fa fa-arrow-left'></i></div>
                    <div class='ea-dialog-objective-detail-player-name'></div>
                    <div class='ea-dialog-objective-detail-player-right'><i class='fa fa-arrow-right'></i></div>
                </div>
                <div class='ea-dialog-objective-detail-grid'></div>
                <div class='ea-dialog-objective-detail-description'></div>
            `,

            constructor() {
                this.dialog = null;
                this.playerId = null;
                this.showing = false;
                this.arrowListener = [];
            },

            show(playerId) {
                this.playerId = playerId;
                this.dialog = new bx.ModalDialog(this.DIALOG_ID, {
                    title: gameui.gamedatas.isGameModeBeginner
                        ? _('Fauna Cards')
                        : _('Ecosystem and Fauna Cards'),
                    animationDuration: 100,
                    onShow: () => this.buildCards(),
                    onHide: () => this.hide(),
                    contentsTpl: this.DIALOG_CONTENT,
                });
                return this.dialog.show();
            },

            hide() {
                this.playerId = null;
                this.showing = false;
                const localDialog = this.dialog;
                this.dialog = null;
                if (localDialog !== null && localDialog.id !== null) {
                    return localDialog.destroy();
                } else {
                    return Promise.resolve();
                }
            },

            refresh() {
                if (!this.showing) {
                    return;
                }
                for (const id of ['player-name', 'grid', 'description']) {
                    const elem = document.querySelector('#popin_' + this.DIALOG_ID + '_container .ea-dialog-objective-detail-' + id);
                    if (elem !== null) {
                        elem.innerHTML = '';
                    }
                }
                this.buildCards();
            },

            buildCards() {
                for (const listener of this.arrowListener) {
                    dojo.disconnect(listener);
                }
                this.arrowListener = [];

                this.showing = true;
                const containerElem = document.getElementById('popin_ea-dialog-objective-detail');
                if (containerElem === null) {
                    return;
                }

                const arrowLeftElem = document.querySelector('#popin_' + this.DIALOG_ID + '_container .ea-dialog-objective-detail-player-left');
                if (this.getLeftPlayerId() === null) {
                    arrowLeftElem.classList.add('bx-invisible');
                } else {
                    arrowLeftElem.classList.remove('bx-invisible');
                    this.arrowListener.push(
                        dojo.connect(
                            arrowLeftElem,
                            'click',
                            () => {
                                const newPlayerId = this.getLeftPlayerId();
                                if (newPlayerId !== null) {
                                    this.playerId = newPlayerId;
                                }
                                this.refresh();
                            }
                        )
                    );
                }
                const arrowRightElem = document.querySelector('#popin_' + this.DIALOG_ID + '_container .ea-dialog-objective-detail-player-right');
                if (this.getRightPlayerId() === null) {
                    arrowRightElem.classList.add('bx-invisible');
                } else {
                    arrowRightElem.classList.remove('bx-invisible');
                    this.arrowListener.push(
                        dojo.connect(
                            arrowRightElem,
                            'click',
                            () => {
                                const newPlayerId = this.getRightPlayerId();
                                if (newPlayerId !== null) {
                                    this.playerId = newPlayerId;
                                }
                                this.refresh();
                            }
                        )
                    );
                }

                const gridWidth = Math.min(1000, (document.body.offsetWidth * 0.7));
                let zoom = Math.min(containerElem.offsetWidth, gridWidth) / (gameui.CARD_WIDTH * 2)
                if (zoom > 1) {
                    zoom = 1;
                }
                containerElem.style.setProperty('--ea-zoom', zoom);

                const cards = [];
                const leafs = [];
                cards.push(document.querySelector('#ea-area-player-' + this.playerId + ' .ea-player-board-card-2 .ea-card'));
                leafs.push([]);
                for (let x = 0; x <= 1; ++x) {
                    for (let y = 0; y <= 1; ++y) {
                        cards.push(document.querySelector('#ea-fauna-board-fauna-card-' + x + '-' + y + ' .ea-card'));
                        const posLeaf = [];
                        for (let order = 0; order < 5; ++order) {
                            posLeaf.push(document.querySelector('#ea-fauna-board-fauna-leaf-' + x + '-' + y + '-' + order + ' .ea-token-leaf'));
                        }
                        posLeaf.push(...document.querySelectorAll('#ea-fauna-board-fauna-leaf-' + x + '-' + y + '-wait .ea-token-leaf'));
                        leafs.push(posLeaf);
                    }
                }
                cards.push(document.querySelector('#ea-fauna-board-ecosystem-card-0 .ea-card'));
                leafs.push([]);
                cards.push(document.querySelector('#ea-fauna-board-ecosystem-card-1 .ea-card'));
                leafs.push([]);

                const playerElem = document.querySelector('#popin_' + this.DIALOG_ID + ' .ea-dialog-objective-detail-player-name')
                const playerNameElem = gameui.createPlayerColorNameElement(this.playerId);
                playerElem.appendChild(playerNameElem);

                const gridElem = document.querySelector('#popin_' + this.DIALOG_ID + ' .ea-dialog-objective-detail-grid')
                for (let i = 0; i < cards.length; ++i) {
                    const cardElem = cards[i];
                    if (cardElem !== null) {
                        const leafContainerElem = document.createElement('div');
                        leafContainerElem.classList.add('ea-objective-detail-leaf-container');
                        leafContainerElem.style.setProperty('--ea-zoom', zoom / 2);
                        for (const leafElem of leafs[i]) {
                            if (leafElem !== null) {
                                const newLeafElem = document.createElement('div');
                                newLeafElem.classList = leafElem.classList;
                                leafContainerElem.appendChild(newLeafElem);
                            }
                        }
                        gridElem.appendChild(leafContainerElem);
                        const cardId = cardElem.dataset.cardId;
                        const cardBottomElem = gameui.cardMgr.createCardBottomDetailElementFromCardId(cardId);
                        if (i == 0) {
                            cardBottomElem.style.marginBottom = '20px';
                            cardBottomElem.style.border = '2px #' + gameui.gamedatas.players[this.playerId].player_color + ' solid';
                        }
                        gridElem.appendChild(cardBottomElem);
                        if (this.playerId in gameui.faunaProgress && cardId in gameui.faunaProgress[this.playerId]) {
                            const progress = gameui.faunaProgress[this.playerId][cardId];
                            const progressElem = gameui.createPlayerColorElement(this.playerId);
                            if (progress.hasRequirements) {
                                progressElem.appendChild(gameui.createFAIcon('check'));
                            } else {
                                progressElem.innerText = progress.progress + ' / ' + progress.objective;
                            }
                            const progressContainerElem = document.createElement('div');
                            progressContainerElem.classList.add('ea-objective-detail-progress');
                            progressContainerElem.appendChild(progressElem);
                            gridElem.appendChild(progressContainerElem);
                            gameui.addBasicTooltipToElement(progressElem, _('Current progress towards the Fauna objective'));
                        }
                    }
                }

                const ul = document.createElement('ul');
                for (const cardElem of cards) {
                    if (cardElem === null) {
                        continue;
                    }
                    const cardId = cardElem.dataset.cardId;
                    const cardDef = gameui.gamedatas.carddefs[cardId];
                    for (const ab of cardDef.abilities) {
                        let line = null;
                        if (cardDef.type == gameui.CARD_TYPE_ECOSYSTEM) {
                            line = _("Ecosystem (end game scoring)");
                        } else {
                            line = _("Fauna (in-game scoring objectives)");
                        }
                        line += ': ' + _(ab.description);
                        const li = document.createElement('li');
                        li.innerText = line;
                        ul.appendChild(li);
                    }
                }
                const descriptionElem = document.querySelector('#popin_' + this.DIALOG_ID + ' .ea-dialog-objective-detail-description')
                descriptionElem.appendChild(ul);
            },

            getRightPlayerId() {
                let i = 0;
                for (i = 0; i < gameui.gamedatas.playerorder.length; ++i) {
                    if (gameui.gamedatas.playerorder[i] == this.playerId) {
                        break;
                    }
                }
                i += 1;
                if (i >= gameui.gamedatas.playerorder.length) {
                    return null;
                }
                return gameui.gamedatas.playerorder[i];
            },

            getLeftPlayerId() {
                let i = 0;
                for (i = gameui.gamedatas.playerorder.length - 1; i >= 0; --i) {
                    if (gameui.gamedatas.playerorder[i] == this.playerId) {
                        break;
                    }
                }
                i -= 1;
                if (i < 0) {
                    return null;
                }
                return gameui.gamedatas.playerorder[i];
            },
        });
    });