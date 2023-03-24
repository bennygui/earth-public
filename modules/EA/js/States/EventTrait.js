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
                if (args && args.canPlayEvent !== undefined && args.canPlayEvent !== null) {
                    canPlayEvent = args.canPlayEvent;
                } else if (args && args._private && args._private.canPlayEvent !== undefined && args._private.canPlayEvent !== null) {
                    canPlayEvent = args._private.canPlayEvent;
                }
                if (canPlayEvent) {
                    this.addTopButtonSecondary(
                        'ea-button-play-event',
                        this.format_string_recursive(_('Play Event ${cardTypeEventIcon}'), { cardTypeEventIcon: '' }),
                        () => this.serverAction('eventPlay')
                    );
                }
            },

            onButtonsStateEventChooseCard(args) {
                debug('onButtonsStateEventChooseCard');
                debug(args);

                for (const cardId of args.eventCardIds) {
                    const cardElem = this.cardMgr.getCardSelectionElementById(cardId);
                    gameui.addClickable(cardElem, () => {
                        this.serverAction('eventChooseCard', { cardId: cardId });
                    });
                }
            },

            onButtonsStateEventSelectGain(args) {
                debug('onButtonsStateEventSelectGain');
                debug(args);
                this.onAbilityGain('eventGain', args);

                const cardElem = gameui.cardMgr.getCardSelectionElementById(args.activatedAfterCopyCardId);
                gameui.addSelected(cardElem);
            },

            onButtonsStateEventSelectPayment(args) {
                debug('onButtonsStateEventSelectPayment');
                debug(args);
                this.onAbilityPayment('eventPay', args);

                const cardElem = gameui.cardMgr.getCardSelectionElementById(args.activatedAfterCopyCardId);
                gameui.addSelected(cardElem);
            },
        });
    });