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
        return declare("ea.PlayerSetupTrait", null, {
            onButtonsStatePlayerSetupChooseInitialCards(args) {
                debug('onButtonsStatePlayerSetupChooseInitialCards');
                debug(args);
                const chosenCardIds = new Set();
                const ID_CHOOSE_CARDS = 'button-choose-cards';
                this.addTopButtonPrimaryWithValid(
                    ID_CHOOSE_CARDS,
                    _('Choose cards'),
                    args.nbCardsToSelect == 3
                    ?  _('You must choose one Island, one Climate and one Ecosystem')
                    :  _('You must choose one Island and one Climate'),
                    () => {
                        this.serverAction('playerSetupChoose', { cardIds: Array.from(chosenCardIds).join(',') });
                    }
                );
                this.setTopButtonValid(ID_CHOOSE_CARDS, false);
                for (const cardId in args.cardIdGroups) {
                    const cardElem = this.cardMgr.getCardSelectionElementById(cardId);
                    const onClick = () => {
                        const wasSelected = (chosenCardIds.has(cardId));
                        for (const otherCardId of args.cardIdGroups[cardId]) {
                            const otherCardElem = this.cardMgr.getCardSelectionElementById(otherCardId);
                            this.removeSelected(otherCardElem);
                            chosenCardIds.delete(otherCardId);
                        }
                        if (!wasSelected) {
                            this.addSelected(cardElem);
                            chosenCardIds.add(cardId);
                        }
                        this.setTopButtonValid(ID_CHOOSE_CARDS, chosenCardIds.size == args.nbCardsToSelect);
                    };
                    this.addClickable(cardElem, onClick);
                    if (this.elementWasSelectedBeforeRemoveAll(cardElem)) {
                        onClick();
                    }
                }
                this.clearSelectedBeforeRemoveAll();
            },

            onButtonsStatePlayerSetupCompostCards(args) {
                debug('onButtonsStatePlayerSetupCompostCards');
                debug(args);
                const ID_COMPOST_CARDS = 'button-compost-cards';
                this.addTopButtonPrimaryWithValid(
                    ID_COMPOST_CARDS,
                    _('Compost cards'),
                    this.format_string_recursive(_('You must choose ${compostFromHandCount} card(s) to compost from your hand'), args),
                    () => {
                        this.serverAction('playerSetupCompost', { cardIds: this.paymentMgr.compostFromHandCardIds().join(',') });
                    }
                );
                this.paymentMgr.startPayment(() => {
                    this.setTopButtonValid(ID_COMPOST_CARDS, this.paymentMgr.isPaymentValid());
                });
                this.paymentMgr.addCompostFromHand(args.handCardIds, args.compostFromHandCount);
            },
        });
    });