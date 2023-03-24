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
        return declare("ea.AbilityTrait", null, {
            onAbilityGain(serverAction, args) {
                const BUTTON_GAIN_PLACE_ID = 'button-gain-place';
                const placeListLogs = [];
                const placeListArgs = {};
                let hasGain = false;
                if (parseInt(args.gainedSprout) > 0) {
                    hasGain = true;
                    placeListLogs.push('${placedSprout}/${gainedSprout} ${sproutIcon}');
                    placeListArgs['placedSprout'] = 0;
                    placeListArgs['gainedSprout'] = args.gainedSprout;
                    placeListArgs['sproutIcon'] = _('sprout(s)');
                }
                if (parseInt(args.gainedGrowth) > 0) {
                    hasGain = true;
                    placeListLogs.push('${placedGrowth}/${gainedGrowth} ${growthIcon}');
                    placeListArgs['placedGrowth'] = 0;
                    placeListArgs['gainedGrowth'] = args.gainedGrowth;
                    placeListArgs['growthIcon'] = _('growth(s)');
                }
                if (parseInt(args.gainedCompostFromHand) > 0) {
                    hasGain = true;
                    placeListLogs.push('${placedCompostFromHand}/${gainedCompostFromHand} ${compostFromHandIcon}');
                    placeListArgs['placedCompostFromHand'] = 0;
                    placeListArgs['gainedCompostFromHand'] = args.gainedCompostFromHand;
                    placeListArgs['compostFromHandIcon'] = _('compost');
                }
                if (args.handChoosingCardIds.length > 0) {
                    hasGain = true;
                    placeListLogs.push(_('${selectedHandChoosingCard} out of ${nbHandChoosingCards} cards'));
                    placeListArgs['selectedHandChoosingCard'] = 0;
                    placeListArgs['nbHandChoosingCards'] = args.handChoosingCardIds.length;
                }
                const updateButton = () => {
                    const buttonElem = document.getElementById(BUTTON_GAIN_PLACE_ID);
                    buttonElem.innerHTML = this.format_string_recursive(
                        hasGain ? _('Gain ${placeList}') : _('Gain nothing'),
                        {
                            placeList: {
                                log: placeListLogs.join(', '),
                                args: placeListArgs,
                            }
                        },
                    );
                };
                args.onUpdateGain = () => {
                    placeListArgs['placedSprout'] = this.gainMgr.getPlacedSprout();
                    placeListArgs['placedGrowth'] = this.gainMgr.getPlacedGrowth();
                    placeListArgs['placedCompostFromHand'] = this.gainMgr.getSelectedCompostFromHandCardIds().length;
                    placeListArgs['selectedHandChoosingCard'] = this.gainMgr.getSelectedHandChoosingCardIds().length;
                    gameui.setTopButtonValid(BUTTON_GAIN_PLACE_ID, this.gainMgr.isCompostFromHandValid() && this.gainMgr.isHandChoosingValid());
                    updateButton();
                };
                this.gainMgr.setupGain(args);
                this.addTopButtonPrimaryWithValid(
                    BUTTON_GAIN_PLACE_ID,
                    '',
                    args.handChoosingCardIds.length > 0
                        ? _('You must select only one card to keep')
                        : _('You must select less cards to compost'),
                    () => {
                        const placedSproutList = this.gainMgr.getPlacedSproutList().join(',');
                        const placedGrowthList = this.gainMgr.getPlacedGrowthList().join(',');
                        const selectedCompostFromHandCardIds = this.gainMgr.getSelectedCompostFromHandCardIds().join(',');
                        const selectedHandChoosingCardIds = this.gainMgr.getSelectedHandChoosingCardIds().join(',');
                        let confirm = Promise.resolve();
                        if (this.gainMgr.hasPlacedNoGain()) {
                            confirm = gameui.showConfirmDialog(_('You have chosen to gain nothing, are you sure?'));
                        } else if (!this.gainMgr.hasMaxedGain()) {
                            confirm = gameui.showConfirmDialog(_('You still have more you could choose to gain, are you sure?'));
                        }
                        confirm.then(() => {
                            this.gainMgr.pause();
                            this.serverAction(serverAction, {
                                placedSproutList: placedSproutList,
                                placedGrowthList: placedGrowthList,
                                selectedCompostFromHandCardIds: selectedCompostFromHandCardIds,
                                selectedHandChoosingCardIds: selectedHandChoosingCardIds,
                            })
                                .catch(() => this.gainMgr.resume());
                        });
                    });
                updateButton();
                this.addTopButtonSecondary(
                    'button-gain-reset',
                    _('Reset'),
                    () => this.gainMgr.resetGain()
                );
            },

            onAbilityPayment(serverAction, args) {
                const BUTTON_PAY_PLACE_ID = 'button-pay-place';
                const placeListLogs = [];
                const placeListArgs = {};
                if (parseInt(args.sproutCount) > 0) {
                    placeListLogs.push('${payedSprout}/${sproutCount} ${sproutIcon}');
                    placeListArgs['payedSprout'] = 0;
                    placeListArgs['sproutCount'] = args.sproutCount;
                    placeListArgs['sproutIcon'] = _('sprout(s)');
                }
                if (parseInt(args.growthCount) > 0) {
                    placeListLogs.push('${payedGrowth}/${growthCount} ${growthIcon}');
                    placeListArgs['payedGrowth'] = 0;
                    placeListArgs['growthCount'] = args.growthCount;
                    placeListArgs['growthIcon'] = _('growth(s)');
                }
                if (parseInt(args.compostFromHandCount) > 0) {
                    placeListLogs.push('${payedCompostFromHand}/${compostFromHandCount} ${compostFromHandIcon}');
                    placeListArgs['payedCompostFromHand'] = 0;
                    placeListArgs['compostFromHandCount'] = args.compostFromHandCount;
                    placeListArgs['compostFromHandIcon'] = _('compost');
                }
                const updateButton = () => {
                    const buttonElem = document.getElementById(BUTTON_PAY_PLACE_ID);
                    buttonElem.innerHTML = this.format_string_recursive(
                        _('Pay ${placeList}'),
                        {
                            placeList: {
                                log: placeListLogs.join(', '),
                                args: placeListArgs,
                            }
                        },
                    );
                };
                this.addTopButtonPrimaryWithValid(
                    BUTTON_PAY_PLACE_ID,
                    '',
                    _('You must select the exact payment'),
                    () => {
                        const payedSproutList = this.paymentMgr.getPayedSproutList().join(',');
                        const payedGrowthList = this.paymentMgr.getPayedGrowthList().join(',');
                        const payedCompostFromHandCardIds = this.paymentMgr.compostFromHandCardIds().join(',');
                        this.paymentMgr.pause();
                        this.serverAction(serverAction, {
                            payedSproutList: payedSproutList,
                            payedGrowthList: payedGrowthList,
                            payedCompostFromHandCardIds: payedCompostFromHandCardIds,
                        })
                            .catch(() => this.paymentMgr.resume());
                    });
                this.addTopButtonSecondary(
                    'button-gain-reset',
                    _('Reset'),
                    () => this.paymentMgr.resetPayment()
                );
                this.paymentMgr.startPayment(() => {
                    placeListArgs['payedSprout'] = this.paymentMgr.sproutCount();
                    placeListArgs['payedGrowth'] = this.paymentMgr.growthCount();
                    placeListArgs['payedCompostFromHand'] = this.paymentMgr.compostFromHandCount();
                    gameui.setTopButtonValid(BUTTON_PAY_PLACE_ID, this.paymentMgr.isPaymentValid());
                    updateButton();
                });
                this.paymentMgr.addCompostFromHand(args.handCardIds, args.compostFromHandCount);
                this.paymentMgr.addSprout(args.sproutCards, args.sproutCount);
                this.paymentMgr.addGrowth(args.growthCards, args.growthCount);
            },
        });
    });