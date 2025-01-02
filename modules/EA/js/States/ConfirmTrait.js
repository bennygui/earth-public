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

            onStateConfirmEndPhase(args) {
                if (this.isTimerEnabled() && this.seenMoreThanOnePrivateState() && !this.isTrue(args.args.askSkipEndTurn)) {
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
                    if (this.isTrue(args.args.askSkipEndTurn)) {
                        this.addTopButtonImportant(
                            'button-end-phase-skip-end-turn',
                            _('End phase, Skip End of Turn'),
                            () => {
                                this.serverAction('confirmEndPhaseSkipEndOfTurn');
                            }
                        );
                    }
                }
            },

            onStateChangedAfter(stateName, args) {
                this.inherited(arguments);
                if (!this.isReadOnly() && stateName == 'STATE_CONFIRM_END_PHASE') {
                    this.addTopCheckbox(
                        'ea-enable-timer-checkbox',
                        _('Enable timer'),
                        this.isTimerEnabled(),
                        (checked) => {
                            this.removeThinkButton();
                            this.setLocalPreference(this.EA_PREF_CONFIRM_TIMER_ID, checked);
                        }
                    );
                }
            },

            onStateGameEndingLastChanceConfirm(args) {
                this.addConfirmEndGameButton(
                    'button-end-game',
                    _('End Game'),
                    args,
                    () => this.serverAction('confirmEndGame')
                );

                if (this.isTrue(args.args.canPlaceExchangeSprout)) {
                    this.addTopButtonSecondary(
                        'ea-place-exchange-sprout',
                        this.format_string_recursive(
                            _('Place ${exchangeSproutCount} Stored ${sproutIcon}'),
                            {
                                'exchangeSproutCount': args.args.exchangeSproutCount,
                                'sproutIcon': _('sprout(s)'),
                            }
                        ),
                        () => this.serverAction('endTurnPlaceExchangeSprout')
                    );
                }
            },

            addConfirmEndGameButton(id, text, args, callback) {
                this.addTopButtonImportant(
                    id,
                    text,
                    () => {
                        let confirm = Promise.resolve();
                        let moreInfoText = '';
                        if (this.isTrue(args.args.hasLeafs)) {
                            moreInfoText += '<p><b>' + _('You have leafs that are worth no points and that could be converted to seeds.') + '</b></p>';
                        }
                        if (this.isTrue(args.args.hasSeeds)) {
                            moreInfoText += '<p><b>' + _('You have seeds that are worth no points and that could be converted for points.') + '</b></p>';
                        }
                        if (moreInfoText.length > 0) {
                            confirm = gameui.showConfirmDialog(_('Are you sure you want to end the game?') + moreInfoText);
                        }
                        confirm.then(() => callback());
                    }
                );
            },
        });
    });