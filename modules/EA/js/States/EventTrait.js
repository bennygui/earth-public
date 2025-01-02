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
        return declare("ea.EventTrait", null, {
            addTopPlayEventButton(args) {
                let canPlayEvent = false;
                if (args && args.args && args.args.canPlayEvent !== undefined && args.args.canPlayEvent !== null) {
                    canPlayEvent = args.args.canPlayEvent;
                } else if (args.args && args.args._private && args.args._private.canPlayEvent !== undefined && args.args._private.canPlayEvent !== null) {
                    canPlayEvent = args.args._private.canPlayEvent;
                }
                if (canPlayEvent) {
                    this.addTopButtonSecondary(
                        'ea-button-play-event',
                        this.format_string_recursive(_('Event ${cardTypeEventIcon}'), { cardTypeEventIcon: '' }),
                        () => this.serverAction('eventPlay')
                    );
                }
            },

            onStateEventChooseCard(args) {
                debug('onStateEventChooseCard');
                debug(args);

                for (const cardId of args.args.eventCardIds) {
                    const cardElem = this.cardMgr.getCardSelectionElementById(cardId);
                    gameui.addClickable(cardElem, () => {
                        this.serverAction('eventChooseCard', { cardId: cardId });
                    });
                }
            },

            onStateEventSelectGain(args) {
                debug('onStateEventSelectGain');
                debug(args);
                this.onAbilityGain('eventGain', args.args);

                const cardElem = gameui.cardMgr.getCardSelectionElementById(args.args.activatedAfterCopyCardId);
                gameui.addSelected(cardElem);
            },

            onStateEventSelectPayment(args) {
                debug('onStateEventSelectPayment');
                debug(args);
                this.onAbilityPayment('eventPay', args.args);

                const cardElem = gameui.cardMgr.getCardSelectionElementById(args.args.activatedAfterCopyCardId);
                gameui.addSelected(cardElem);
            },
        });
    });