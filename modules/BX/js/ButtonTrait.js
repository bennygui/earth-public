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
            BX_ENABLE_TIMER_CHECKBOX_ID: 'bx-enable-timer-checkbox',
            BX_BUTTON_THINK_ID: 'button-think',

            constructor() {
                this.buttonTimer = {
                    timer: null,
                    id: null,
                    title: null,
                    remaining: null,
                    stopped: false,
                };
                this.showUndoButtonForInactivePlayers = false;
                this.buttonContainerId = 'bx-generalactions';
            },

            setup(gamedatas) {
                this.inherited(arguments);
                const actions = document.getElementById('generalactions');
                const bxActions = document.createElement('div');
                bxActions.id = 'bx-generalactions';
                actions.parentElement.insertBefore(bxActions, actions.nextElementSibling);
            },

            setButtonUsesBGAGeneralActions(useBGA = true) {
                if (useBGA) {
                    this.buttonContainerId = 'generalactions';
                } else {
                    this.buttonContainerId = 'bx-generalactions';
                }
            },

            getButtonUsesBGAGeneralActions() {
                return (this.buttonContainerId == 'generalactions');
            },

            clearBxGeneralActionButtons() {
                const bxActions = document.getElementById('bx-generalactions');
                if (bxActions !== null) {
                    bxActions.innerHTML = '';
                }
            },

            addTopButtonPrimary(id, title, callback) {
                this.addActionButton(id, title, this.createIgnoredDelayCallback(callback), this.buttonContainerId);
                return document.getElementById(id);
            },
            addTopButtonSecondary(id, title, callback) {
                this.addActionButton(id, title, this.createIgnoredDelayCallback(callback), this.buttonContainerId, false, 'gray');
                return document.getElementById(id);
            },
            addTopButtonImportant(id, title, callback) {
                this.addActionButton(id, title, this.createIgnoredDelayCallback(callback), this.buttonContainerId, false, 'red');
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
                this.addActionButton(id, title, this.createIgnoredDelayCallback(timerCallback), this.buttonContainerId, false, 'red');
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
            addTopButtonImportantWithTimerPreference(id, title, duration, hasTimer, args, getPrefFct, setPrefFct, callback) {
                if (hasTimer) {
                    const isTimerEnabled = (!this.isReadOnly() && getPrefFct());
                    if (isTimerEnabled) {
                        this.addTopButtonImportantWithTimer(
                            id,
                            title,
                            duration,
                            () => {
                                this.removeThinkButton();
                                callback();
                            }
                        );
                        if (this.isButtonTimerRunning()) {
                            this.addTopButtonPrimary(
                                this.BX_BUTTON_THINK_ID,
                                _('Let me think'),
                                () => this.removeThinkButton()
                            );
                        }
                    } else {
                        this.addTopButtonImportant(id, title, callback);
                    }
                    this.addTopCheckbox(
                        this.BX_ENABLE_TIMER_CHECKBOX_ID,
                        _('Enable timer'),
                        isTimerEnabled,
                        (checked) => {
                            this.removeThinkButton();
                            setPrefFct(checked);
                            if (checked) {
                                setTimeout(() => {
                                    const actions = document.getElementById(this.buttonContainerId);
                                    actions.innerHTML = '';
                                    this.onUpdateActionButtons(gameui.gamedatas.gamestate.name, args);
                                }, 300);
                            }
                        }
                    );
                } else {
                    this.addTopButtonImportant(id, title, callback);
                }
            },
            removeThinkButton() {
                this.stopTopButtonTimer();
                const button = document.getElementById(this.BX_BUTTON_THINK_ID);
                if (button !== null) {
                    button.remove();
                }
            },


            addTopButtonPrimaryWithValid(id, title, errorMsg, callback) {
                callback = this.createIgnoredDelayCallback(callback);
                this.addActionButton(
                    id,
                    title,
                    () => {
                        if (this.isTopButtonValid(id)) {
                            callback();
                        } else {
                            this.showMessage(errorMsg, 'error');
                        }
                    },
                    this.buttonContainerId
                );
                return document.getElementById(id);
            },
            setTopButtonValid(id, isValid = true) {
                const button = document.getElementById(id);
                if (button === null) {
                    debug('BUG! setTopButtonValid cannot change button that does not exist id=' + id);
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
            allowUndoButtonForInactivePlayers() {
                this.showUndoButtonForInactivePlayers = true;
            },
            addTopUndoButton(args) {
                if (!this.showUndoButtonForInactivePlayers && !this.isCurrentPlayerActive()) {
                    return;
                }
                let undoLevel = 0;
                if (args && args.undoLevel !== undefined && args.undoLevel !== null) {
                    undoLevel = args.undoLevel;
                } else if (args && args._private && args._private.undoLevel !== undefined && args._private.undoLevel !== null) {
                    undoLevel = args._private.undoLevel;
                }
                if (undoLevel >= 1) {
                    const prevButton = document.getElementById('bx-button-undo-last');
                    if (prevButton !== null) {
                        prevButton.remove();
                    }
                    this.addTopButtonSecondary('bx-button-undo-last', _('Undo'), () => this.serverAction('undoLast'));
                }
                if (undoLevel >= 2) {
                    const prevButton = document.getElementById('bx-button-undo-all');
                    if (prevButton !== null) {
                        prevButton.remove();
                    }
                    this.addTopButtonSecondary('bx-button-undo-all', _('Undo All'), () => this.serverAction('undoAll'));
                }
            },

            addTopCheckbox(id, title, checked, callback) {
                const actions = document.getElementById(this.buttonContainerId);
                const newElem = gameui.createCheckboxSwitch(id, title);
                actions.appendChild(newElem);
                const checkbox = document.getElementById(id);
                checkbox.checked = checked;
                checkbox.addEventListener('change', () => callback(checkbox.checked));
            },

            addTopButtonSelection(defaultTitle, errorMsg, optionList, nbSelection = 1, isNbSelectionValid = null) {
                // [ { title, ids, onElement, onClick, onSelect } ]
                if (!(optionList instanceof Array)) {
                    optionList = [optionList];
                }
                if (isNbSelectionValid === null) {
                    isNbSelectionValid = (nb) => (nb == nbSelection);
                }
                const BUTTON_ID = 'bx-button-top-button-selection';
                const updateTitle = (newTitle) => {
                    const button = document.getElementById(BUTTON_ID);
                    if (button !== null) {
                        button.innerHTML = (newTitle === undefined || newTitle === null) ? defaultTitle : newTitle;
                    }
                };
                // [ { index, id, side, e } ]
                let selected = [];
                const indexOfSelected = (index, id, side) => {
                    for (const i in selected) {
                        const o = selected[i];
                        if (index == o.index && id == o.id && side == o.side) {
                            return i;
                        }
                    }
                    return -1;
                };
                const isSelected = (index, id, side) => {
                    return (indexOfSelected(index, id, side) >= 0);
                };
                const removeSelected = (index, id, side) => {
                    const i = indexOfSelected(index, id, side);
                    if (i >= 0) {
                        this.removeSelected(selected[i].e);
                        selected.splice(i, 1);
                    }
                };
                const clearSelected = () => {
                    selected = [];
                    this.removeAllSelected();
                    this.clearSelectedBeforeRemoveAll();
                };
                const addSelected = (index, id, side, e) => {
                    selected.push({ index: index, id: id, side: side, e: e });
                    this.addSelected(e);
                };
                const selectedIds = (index = null) => {
                    if (nbSelection == 1) {
                        if (selected.length == 0) {
                            return null;
                        } else {
                            return selected[0].id;
                        }
                    }
                    return selected.filter((o) => index === null || o.index == index).map((o) => o.id);
                };
                const selectedSides = (index = null) => {
                    if (nbSelection == 1) {
                        if (selected.length == 0) {
                            return null;
                        } else {
                            return selected[0].side;
                        }
                    }
                    return selected.filter((o) => index === null || o.index == index).map((o) => o.side);
                };
                for (const index in optionList) {
                    const option = optionList[index];
                    for (const id of option.ids) {
                        let elements = option.onElement(id);
                        if (typeof elements[Symbol.iterator] === 'function') {
                            elements = Array.from(elements);
                        } else {
                            elements = [elements];
                        }
                        for (const [side, e] of elements.entries()) {
                            this.addClickable(
                                e,
                                () => {
                                    if (option.onSelect) {
                                        option.onSelect(id, side, option);
                                    }
                                    if (isSelected(index, id, side)) {
                                        removeSelected(index, id, side);
                                        this.setTopButtonValid(BUTTON_ID, isNbSelectionValid(selected.length));
                                    } else {
                                        if (nbSelection == 1) {
                                            clearSelected();
                                        }
                                        addSelected(index, id, side, e);
                                        this.setTopButtonValid(BUTTON_ID, isNbSelectionValid(selected.length));
                                    }
                                    if (!isNbSelectionValid(selected.length)) {
                                        updateTitle(defaultTitle);
                                    } else {
                                        updateTitle(
                                            (option.title instanceof Function)
                                                ? option.title(selectedIds(), selectedSides())
                                                : option.title
                                        );
                                    }
                                },
                                (option.clickableOption === undefined || option.clickableOption === null) ? {} : option.clickableOption
                            );
                        }
                    }
                }
                this.addTopButtonPrimaryWithValid(
                    BUTTON_ID,
                    defaultTitle,
                    errorMsg,
                    () => {
                        let clickCalled = false;
                        const seenIndex = new Set();
                        for (const o of selected) {
                            if (seenIndex.has(o.index)) {
                                continue;
                            }
                            seenIndex.add(o.index);
                            if (!optionList[o.index].onClick) {
                                continue;
                            }
                            optionList[o.index].onClick(selectedIds(o.index), selectedSides(o.index));
                            clickCalled = true;
                        }
                        if (!clickCalled && isNbSelectionValid(selected.length)) {
                            optionList[0].onClick(selectedIds(), selectedSides());
                        }
                    }
                );
                this.setTopButtonValid(BUTTON_ID, isNbSelectionValid(selected.length));
                return (() => {
                    clearSelected();
                    this.setTopButtonValid(BUTTON_ID, isNbSelectionValid(selected.length));
                    updateTitle(defaultTitle);
                });
            },

            addTopButtonCheckbox(values, onTitle, onChanged) {
                const CHECKBOX_BUTTON_ID = 'bx-top-button-checkbox-';
                const CHECKBOX_CLASS = 'bx-top-button-checkbox-check';
                if (values.length == 0) {
                    return {
                        reset() { },
                        hasSelectedValue() { return true; },
                        selectedValue() { return null; },
                    };
                }
                let selectedValue = null;
                const updateButtons = () => {
                    for (const e of document.querySelectorAll('.' + CHECKBOX_CLASS)) {
                        e.classList.remove('fa-check-square-o');
                        e.classList.add('fa-square-o');
                    }
                    if (selectedValue !== null) {
                        const e = document.querySelector('#' + CHECKBOX_BUTTON_ID + selectedValue + ' .' + CHECKBOX_CLASS);
                        e.classList.remove('fa-square-o');
                        e.classList.add('fa-check-square-o');
                    }
                };
                for (const v of values) {
                    const box = this.createFAIcon('square-o');
                    box.classList.add(CHECKBOX_CLASS);
                    const title = onTitle(v);
                    this.addTopButtonSecondary(
                        CHECKBOX_BUTTON_ID + v,
                        box.outerHTML + ' ' + title,
                        () => {
                            if (selectedValue == v) {
                                selectedValue = null;
                            } else {
                                selectedValue = v;
                            }
                            updateButtons();
                            onChanged(selectedValue);
                        }
                    );
                }
                return {
                    reset() {
                        selectedValue = null;
                        updateButtons();
                    },
                    hasSelectedValue() { return (selectedValue !== null); },
                    selectedValue() { return selectedValue; },
                };
            },
        });
    });