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
        return declare("ea.ConfirmTrait", null, {
            TIMER_DURATION: 10,
            BUTTON_THINK_ID: 'button-think',

            removeThinkButton() {
                this.stopTopButtonTimer();
                const button = document.getElementById(this.BUTTON_THINK_ID);
                if (button !== null) {
                    button.remove();
                }
            },

            isTimerEnabled() {
                return (!this.isReadOnly() && this.getLocalPreference(this.EA_PREF_CONFIRM_TIMER_ID));
            },

            onButtonsStateConfirmEndPhase(args) {
                if (this.isTimerEnabled() && this.seenMoreThanOnePrivateState()) {
                    this.addTopButtonImportantWithTimer(
                        'button-end-phase',
                        _('End phase'),
                        this.TIMER_DURATION,
                        () => {
                            this.removeThinkButton();
                            this.serverAction('confirmEndPhase', { skipCheckInterfaceLocked: true });
                        }
                    );
                    if (this.isButtonTimerRunning()) {
                        this.addTopButtonPrimary(
                            this.BUTTON_THINK_ID,
                            _('Let me think'),
                            () => this.removeThinkButton()
                        );
                    }
                } else {
                    this.addTopButtonImportant(
                        'button-end-phase',
                        _('End phase'),
                        () => {
                            this.serverAction('confirmEndPhase');
                        }
                    );
                }
            },

            onUpdateActionButtonsdAfter(stateName, args) {
                this.inherited(arguments);
                if (!this.isReadOnly() && stateName == 'STATE_CONFIRM_END_PHASE') {
                    this.addTopCheckbox(
                        'ea-enable-timer-checkbox',
                        _('Enable timer'),
                        this.isTimerEnabled(),
                        (checked) => {
                            this.removeThinkButton();
                            this.setLocalPreference(this.EA_PREF_CONFIRM_TIMER_ID, checked);
                            if (checked) {
                                setTimeout(() => {
                                    const actions = document.getElementById('generalactions');
                                    actions.innerHTML = '';
                                    this.onUpdateActionButtons(stateName, args);
                                }, 300);
                            }
                        }
                    );
                }
            },

            onButtonsStateGameEndingLastChanceConfirm(args) {
                this.addTopButtonImportant(
                    'button-end-game',
                    _('End game'),
                    () => {
                        this.serverAction('confirmEndGame');
                    }
                );
            },
        });
    });