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
        return declare("bx.ButtonTrait", null, {
            constructor() {
                this.buttonTimer = {
                    timer: null,
                    id: null,
                    title: null,
                    remaining: null,
                    stopped: false,
                };
            },

            addTopButtonPrimary(id, title, callback) {
                this.addActionButton(id, title, callback);
                return document.getElementById(id);
            },
            addTopButtonSecondary(id, title, callback) {
                this.addActionButton(id, title, callback, null, false, 'gray');
                return document.getElementById(id);
            },
            addTopButtonImportant(id, title, callback) {
                this.addActionButton(id, title, callback, null, false, 'red');
                return document.getElementById(id);
            },
            addTopButtonImportantWithTimer(id, title, duration, callback) {
                const timerCallback = () => {
                    this.stopTopButtonTimer();
                    callback();
                };
                if (this.buttonTimer.timer !== null) {
                    this._stopTopButtonTimer();
                }
                this.buttonTimer.id = id;
                this.buttonTimer.title = title;
                if (this.buttonTimer.stopped) {
                    this.buttonTimer.remaining = null;
                    this.buttonTimer.timer = null;
                } else {
                    this.buttonTimer.remaining = duration;
                    this.buttonTimer.timer = setInterval(() => {
                        this.buttonTimer.remaining -= 1;
                        this.updateTopButtonTimerTitle();
                        if (this.buttonTimer.remaining <= 0) {
                            this.stopTopButtonTimer();
                            const button = document.getElementById(id);
                            if (button !== null) {
                                timerCallback();
                            }
                        }
                    }, 1000);
                }
                this.addActionButton(id, title, timerCallback, null, false, 'red');
                this.updateTopButtonTimerTitle();
                return document.getElementById(id);
            },
            updateTopButtonTimerTitle() {
                if (this.buttonTimer.id === null) {
                    return;
                }
                const button = document.getElementById(this.buttonTimer.id);
                if (button !== null) {
                    if (this.buttonTimer.remaining !== null) {
                        button.innerHTML = this.buttonTimer.title + ' (' + this.buttonTimer.remaining + ')';
                    } else {
                        button.innerHTML = this.buttonTimer.title;
                    }
                }
            },
            stopTopButtonTimer() {
                this.buttonTimer.stopped = true;
                this._stopTopButtonTimer();
            },
            _stopTopButtonTimer() {
                clearInterval(this.buttonTimer.timer);
                this.buttonTimer.timer = null;
                this.buttonTimer.remaining = null;
                this.updateTopButtonTimerTitle();
            },
            clearTopButtonTimer() {
                this.stopTopButtonTimer();
                this.buttonTimer = {
                    timer: null,
                    id: null,
                    title: null,
                    remaining: null,
                    stopped: false,
                };
            },
            isButtonTimerRunning() {
                return (this.buttonTimer.timer !== null);
            },
            addTopButtonPrimaryWithValid(id, title, errorMsg, callback) {
                this.addActionButton(id, title, () => {
                    if (this.isTopButtonValid(id)) {
                        callback();
                    } else {
                        this.showMessage(errorMsg, 'error');
                    }
                });
                return document.getElementById(id);
            },
            setTopButtonValid(id, isValid = true) {
                const button = document.getElementById(id);
                if (button === null) {
                    debug('setTopButtonValid cannot change button that does not exist id=' + id);
                    return;
                }
                if (isValid) {
                    button.classList.add('bgabutton_blue');
                    button.classList.remove('bx-top-button-invalid');
                } else {
                    button.classList.remove('bgabutton_blue');
                    button.classList.add('bx-top-button-invalid');
                }
            },
            isTopButtonValid(id) {
                const button = document.getElementById(id);
                return !button.classList.contains('bx-top-button-invalid');
            },
            addTopUndoButton(args) {
                let undoLevel = 0;
                if (args && args.undoLevel !== undefined && args.undoLevel !== null) {
                    undoLevel = args.undoLevel;
                } else if (args && args._private && args._private.undoLevel !== undefined && args._private.undoLevel !== null) {
                    undoLevel = args._private.undoLevel;
                }
                if (undoLevel >= 1) {
                    this.addTopButtonSecondary('bx-button-undo-last', _('Undo'), () => this.serverAction('undoLast'));
                }
                if (undoLevel >= 2) {
                    this.addTopButtonSecondary('bx-button-undo-all', _('Undo All'), () => this.serverAction('undoAll'));
                }
            },

            addTopCheckbox(id, title, checked, callback) {
                const actions = document.getElementById('generalactions');
                const newElem = gameui.createCheckboxSwitch(id, title);
                actions.appendChild(newElem);
                const checkbox = document.getElementById(id);
                checkbox.checked = checked;
                checkbox.addEventListener('change', () => callback(checkbox.checked));
            },
        });
    });