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
        return declare("ea.ActivationTrait", null, {
            ACTIVATION_DIRECTION_ISLAND_CLIMATE_TABLEAU: 0,
            ACTIVATION_DIRECTION_TABLEAU_ISLAND_CLIMATE: 1,

            onStateActivationChooseBoardOrTableau(args) {
                debug('onStateActivationChooseBoardOrTableau');
                debug(args);
                this.addTopButtonPrimary(
                    'button-activate-tableau',
                    this.format_string_recursive(
                        _('Tableau &rArr; Board (${islandIcon} &rArr; ${climateIcon})'),
                        {
                            islandIcon: '<span class="ea-icon-card-type-island"></span>',
                            climateIcon: '<span class="ea-icon-card-type-climate"></span>',
                        }
                    ),
                    () => this.serverAction('activationChooseActivationDirection', {
                        activationDirection: this.ACTIVATION_DIRECTION_TABLEAU_ISLAND_CLIMATE
                    })
                ).style.setProperty('--ea-zoom', 0.3);
                this.addTopButtonPrimary(
                    'button-activate-island',
                    this.format_string_recursive(
                        _('Board (${islandIcon} &rArr; ${climateIcon}) &rArr; Tableau'),
                        {
                            islandIcon: '<span class="ea-icon-card-type-island"></span>',
                            climateIcon: '<span class="ea-icon-card-type-climate"></span>',
                        }
                    ),
                    () => this.serverAction('activationChooseActivationDirection', {
                        activationDirection: this.ACTIVATION_DIRECTION_ISLAND_CLIMATE_TABLEAU
                    })
                ).style.setProperty('--ea-zoom', 0.3);
            },

            onStateActivationChooseActivateOrSkip(args) {
                debug('onStateActivationChooseActivateOrSkip');
                debug(args);
                let buttonTitle = _('Activate ${mainActionId}');
                if (args.args.activationString.length > 0) {
                    buttonTitle = _('Activate');
                    buttonTitle += ' ';
                    buttonTitle += '<div class="ea-main-action-id-color ea-main-action-id-color-' + args.args.mainActionId + '">';
                    buttonTitle += args.args.activationString;
                    buttonTitle += '</div>';
                }
                this.addTopButtonPrimary(
                    'button-activate-card',
                    this.format_string_recursive(
                        buttonTitle,
                        {
                            mainActionId: args.args.mainActionId,
                            drawFromDeckIcon: '',
                            growthIcon: '',
                            soilIcon: '',
                            sproutIcon: '',
                            compostFromHandIcon: '',
                            compostFromDeckIcon: '',
                            compostDestroyIcon: '',
                            seedIcon: '',
                            otherPlayersIcon: '',
                            onePlayerIcon: '',
                        }
                    ),
                    () => this.serverAction('activationActivateCard')
                );
                this.addTopButtonSecondary(
                    'button-skip-activation',
                    _('Skip'),
                    () => this.serverAction('activationSkipCard')
                );
                this.gainMgr.setupGain({
                    abilityCardIds: [args.args.activatedAfterCopyCardId],
                    onAbilityCard: () => {
                        this.gainMgr.pause();
                        this.serverAction('activationActivateCard')
                            .catch(() => this.gainMgr.resume());
                    },
                });
            },

            onStateActivationSelectGain(args) {
                debug('onStateActivationSelectGain');
                debug(args);
                this.onAbilityGain('activationGain', args.args);

                this.gainMgr.registerOnUpdateTableau(() => {
                    const cardElem = gameui.cardMgr.getCardSelectionElementById(args.args.activatedAfterCopyCardId);
                    gameui.addSelected(cardElem);
                    if (args.args.activatedBeforeCopyCardId != args.args.activatedAfterCopyCardId) {
                        const copiedCardElem = gameui.cardMgr.getCardSelectionElementById(args.args.activatedBeforeCopyCardId);
                        gameui.addSelected(copiedCardElem, { secondary: true });
                    }
                });
            },

            onStateActivationSelectPayment(args) {
                debug('onStateActivationSelectPayment');
                debug(args);
                this.onAbilityPayment('activationPay', args.args);

                this.paymentMgr.registerOnUpdateTableau(() => {
                    const cardElem = gameui.cardMgr.getCardSelectionElementById(args.args.activatedAfterCopyCardId);
                    gameui.addSelected(cardElem);
                    if (args.args.activatedBeforeCopyCardId != args.args.activatedAfterCopyCardId) {
                        const copiedCardElem = gameui.cardMgr.getCardSelectionElementById(args.args.activatedBeforeCopyCardId);
                        gameui.addSelected(copiedCardElem, { secondary: true });
                    }
                });
            },

            onStateActivationChooseCardToCopy(args) {
                debug('onStateActivationChooseCardToCopy');
                debug(args);

                this.gainMgr.setupGain({
                    abilityCardIds: args.args.cardIds,
                    onAbilityCard: (cardId) => {
                        this.gainMgr.pause();
                        this.serverAction('activationSelectCardToCopy', { cardId: cardId })
                            .catch(() => this.gainMgr.resume());
                    },
                });

                this.gainMgr.registerOnUpdateTableau(() => {
                    const cardElem = gameui.cardMgr.getCardSelectionElementById(args.args.activatedAfterCopyCardId);
                    gameui.addSelected(cardElem);
                    if (args.args.activatedBeforeCopyCardId != args.args.activatedAfterCopyCardId) {
                        const copiedCardElem = gameui.cardMgr.getCardSelectionElementById(args.args.activatedBeforeCopyCardId);
                        gameui.addSelected(copiedCardElem, { secondary: true });
                    }
                });
            },
        });
    });