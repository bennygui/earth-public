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
        return declare("bx.UITrait", null, {
            constructor() {
                this.selectedElementBeforeRemoveAll = [];
                this.basicTooltips = [];
            },

            addClickable(element, callback, options = {}) {
                const config = Object.assign({
                    border: true,
                    outline: false,
                    childEventSelector: null,
                    checkInterfaceLock: true,
                    checkDraggingLock: true,
                }, options);

                element.classList.add('bx-clickable');
                if (config.border) {
                    element.classList.add('bx-border');
                }
                if (config.outline) {
                    element.classList.add('bx-outline');
                }
                let realCallback = () => {
                    if (config.checkInterfaceLock && this.isInterfaceLocked()) {
                        const errorMsg = _('Please wait, an action is already in progress');
                        this.showMessage(errorMsg, 'error');
                        return;
                    }
                    if (config.checkDraggingLock && element.closest('.bx-is-dragging') !== null) {
                        return;
                    }
                    callback();
                };
                if (config.childEventSelector === null) {
                    this.connect(element, 'onclick', realCallback);
                } else {
                    for (const child of element.querySelectorAll(config.childEventSelector)) {
                        this.connect(child, 'onclick', realCallback);
                    }
                }
            },
            removeAllClickable() {
                this.disconnectAll();
                const elements = document.querySelectorAll('.bx-clickable');
                for (const e of elements) {
                    e.classList.remove('bx-clickable');
                    this.removeBorder(e);
                }
            },
            removeClickable(element) {
                this.disconnect(element, 'onclick')
                element.classList.remove('bx-clickable');
                this.removeBorder(element);
            },
            removeClickableFromElementAndChilds(parentElement) {
                this.removeClickable(parentElement);
                const elements = parentElement.querySelectorAll('.bx-clickable');
                for (const e of elements) {
                    this.removeClickable(e);
                }
            },
            addSelected(element, options = {}) {
                const config = Object.assign({
                    border: true,
                    outline: false,
                    secondary: false,
                }, options);

                element.classList.add('bx-selected');
                if (config.border) {
                    element.classList.add('bx-border');
                }
                if (config.outline) {
                    element.classList.add('bx-outline');
                }
                if (config.secondary) {
                    element.classList.add('bx-selected-secondary');
                }
            },
            removeSelected(element) {
                if (!element) {
                    return;
                }
                element.classList.remove('bx-selected');
                element.classList.remove('bx-selected-secondary');
                this.removeBorder(element);
            },
            removeAllSelected() {
                const elements = document.querySelectorAll('.bx-selected');
                for (const e of elements) {
                    this.selectedElementBeforeRemoveAll.push(e);
                    this.removeSelected(e);
                }
            },
            removeSelectedFromElementAndChilds(parentElement) {
                this.removeSelected(parentElement);
                const elements = parentElement.querySelectorAll('.bx-selected');
                for (const e of elements) {
                    this.removeSelected(e);
                }
            },
            elementWasSelectedBeforeRemoveAll(element) {
                return (this.selectedElementBeforeRemoveAll.includes(element));
            },
            clearSelectedBeforeRemoveAll(element) {
                this.selectedElementBeforeRemoveAll = [];
            },
            removeBorder(element) {
                if (
                    !element.classList.contains('bx-clickable')
                    && !element.classList.contains('bx-selected')
                ) {
                    element.classList.remove('bx-border');
                    element.classList.remove('bx-outline');
                }
            },

            addPlayerPanel() {
                const playerPanelContainer = document.getElementById('player_boards');
                const newPanel = document.createElement('div');
                newPanel.classList.add('player-board');
                playerPanelContainer.appendChild(newPanel);
                return newPanel;
            },

            createCheckboxSwitch(id, caption = '') {
                const label = document.createElement('label');
                label.classList.add('bx-checkbox-switch');

                const checkbox = document.createElement('input');
                checkbox.id = id;
                checkbox.type = 'checkbox';

                const i = document.createElement('i');

                const span = document.createElement('span');
                span.innerText = caption;

                label.appendChild(span);
                label.appendChild(checkbox);
                label.appendChild(i);

                return label;
            },

            createFAIcon(icon) {
                const elem = document.createElement('i');
                elem.classList.add('fa');
                elem.classList.add('fa-' + icon);
                return elem;
            },

            createPlayerColorElement(playerId, text = null) {
                const span = document.createElement('span');
                if (playerId in gameui.gamedatas.players) {
                    const playerInfo = gameui.gamedatas.players[playerId];
                    span.style.color = '#' + playerInfo.player_color;
                    if (playerInfo.color_back !== null) {
                        span.style.backgroundColor = '#' + playerInfo.color_back;
                    }
                }
                if (text !== null) {
                    span.innerText = text;
                }
                return span;
            },

            createPlayerColorNameElement(playerId) {
                if (playerId in gameui.gamedatas.players) {
                    return this.createPlayerColorElement(
                        playerId,
                        gameui.gamedatas.players[playerId].name
                    );
                } else {
                    return this.createPlayerColorElement(playerId);
                }
            },

            addBasicTooltipToElement(elements, title) {
                if (gameui.bHideTooltips) {
                    return;
                }
                const newToolTip = new dijit.Tooltip({
                    connectId: (elements instanceof Array) ? elements : [elements],
                    getContent: function (matchedNode) {
                        return title;
                    }
                });
                this.basicTooltips.push(newToolTip);
                return newToolTip;
            },

            closeAllTooltips() {
                if (gameui && gameui.tooltips) {
                    for (const tooltipId in gameui.tooltips) {
                        if (gameui.tooltips[tooltipId]) {
                            gameui.tooltips[tooltipId].close();
                        }
                    }
                }
                for (const tooltip of this.basicTooltips) {
                    tooltip.close();
                }
            },

            showInformationDialog(title, paragraphArray, params = {}) {
                let html = '<div>';
                if ('before' in params) {
                    html += params['before'];
                }
                let nextIsHeader = false;
                for (const p of paragraphArray) {
                    if (nextIsHeader) {
                        nextIsHeader = false;
                        html += '<h3>' + dojo.string.substitute(p, params) + '</h3>'
                    } else if (p.length == 0) {
                        nextIsHeader = true;
                    } else {
                        html += '<p>' + dojo.string.substitute(p, params) + '</p>'
                    }
                }
                if ('after' in params) {
                    html += params['after'];
                }
                html += '</div>'
                const dialog = new bx.ModalDialog('ea-information-dialog', {
                    title: title,
                    contentsTpl: html,
                    closeWhenClickAnywhere: true,
                });
                dialog.show();
            },

            showConfirmDialog(title) {
                return new Promise((resolve, reject) => {
                    this.confirmationDialog(
                        title,
                        () => resolve(),
                        () => reject()
                    );
                })
            },

            showConfirmDialogCondition(title, condition) {
                if (condition) {
                    return this.showConfirmDialog(title);
                } else {
                    return Promise.resolve();
                }
            },
        });
    });