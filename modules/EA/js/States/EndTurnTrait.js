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
        return declare("ea.EndTurnTrait", null, {
            onStateEndTurnChoose(args) {
                debug('onStateEndTurnChoose');
                debug(args);
                if (this.isTrue(args.args.canPlaceExchangeSprout)) {
                    this.addTopButtonPrimary(
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

                if (this.isTrue(args.args.canPlayEndTurnEvent)) {
                    this.addTopButtonPrimary(
                        'ea-play-end-turn-event',
                        this.format_string_recursive(
                            _('Play End Turn ${cardTypeEventIcon}'),
                            {
                                'cardTypeEventIcon': _('Event'),
                            }
                        ),
                        () => this.serverAction('endTurnPlayEndTurnEvent')
                    );
                }

                if (this.isTrue(args.args.isEndGame)) {
                    this.addConfirmEndGameButton(
                        'ea-end-turn-pass',
                        _('End Game'),
                        args,
                        () => this.serverAction('endTurnPass')
                    );
                } else {
                    this.addTopButtonImportant(
                        'ea-end-turn-pass',
                        _('Confirm and Pass'),
                        () => this.serverAction('endTurnPass')
                    );
                }
            },

            onStateEndTurnPlaceSprout(args) {
                debug('onStateEndTurnPlaceSprout');
                debug(args);
                this.onAbilityGain('endTurnPlaceExchangeSproutGain', args.args);
            },

            onStateEndTurnChooseEndTurnEvent(args) {
                debug('onStateEndTurnChooseEndTurnEvent');
                debug(args);

                for (const cardId of args.args.eventCardIds) {
                    const cardElem = this.cardMgr.getCardSelectionElementById(cardId);
                    gameui.addClickable(cardElem, () => {
                        this.serverAction('endTurnChooseEndTurnEvent', { cardId: cardId });
                    });
                }
            },

            onStateEndTurnEventChoose(args) {
                debug('onStateEndTurnEventChoose');
                debug(args);

                this.addTopButtonPrimary(
                    'ea-play-end-turn-event',
                    this.format_string_recursive(
                        _('Activate First End Turn ${cardTypeEventIcon}'),
                        {
                            'cardTypeEventIcon': _('Event'),
                        }
                    ),
                    () => this.serverAction('endTurnEventActivate')
                );

                this.addTopButtonImportant(
                    'ea-end-turn-pass',
                    _('Confirm and Pass'),
                    () => this.serverAction('endTurnEventPass')
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
        });
    });