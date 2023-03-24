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
        return declare("ea.ConversionTrait", null, {
            addTopPlayConversionButton(args) {
                let canPlayConversion = false;
                if (args && args.canPlayConversion !== undefined && args.canPlayConversion !== null) {
                    canPlayConversion = args.canPlayConversion;
                } else if (args && args._private && args._private.canPlayConversion !== undefined && args._private.canPlayConversion !== null) {
                    canPlayConversion = args._private.canPlayConversion;
                }
                if (canPlayConversion) {
                    this.addTopButtonSecondary(
                        'ea-button-play-conversion',
                        this.format_string_recursive(
                            _('Conversion -3 ${sproutIcon}: +2 ${soilIcon}'),
                            { sproutIcon: '', soilIcon: '', }
                        ),
                        () => this.serverAction('convertPlay')
                    );
                    const boxElem = this.playerBoardMgr.getPlayerIdConversionBoxElem(this.player_id);
                    if (boxElem !== null) {
                        this.addClickable(boxElem, () => this.serverAction('convertPlay'));
                    }
                }
            },

            onButtonsStateConvertSelectPayment(args) {
                debug('onButtonsStateConvertSelectPayment');
                debug(args);
                const BUTTON_CONVERT_ID = 'button-convert';
                const updateButton = () => {
                    const count = this.paymentMgr.sproutCount();
                    const buttonElem = document.getElementById(BUTTON_CONVERT_ID);
                    buttonElem.innerHTML = this.format_string_recursive(
                        _('Convert ${sproutCount} ${sproutIcon} to ${soilCount} ${soilIcon}'),
                        {
                            sproutCount: count,
                            sproutIcon: _('sprout(s)'),
                            soilCount: 2 * Math.floor(count / 3),
                            soilIcon: _('soil'),
                        },
                    );
                };
                this.addTopButtonPrimaryWithValid(
                    BUTTON_CONVERT_ID,
                    '',
                    _('You must select a multiple of 3 sprouts (3, 6, 9, ...)'),
                    () => {
                        const payedSproutList = this.paymentMgr.getPayedSproutList().join(',');
                        this.paymentMgr.pause();
                        this.serverAction('convertSelectPayment', {
                            payedSproutList: payedSproutList,
                            payedGrowthList: '',
                            payedCompostFromHandCardIds: '',
                        })
                            .catch(() => this.paymentMgr.resume());
                    });

                this.addTopButtonSecondary(
                    'button-convert-reset',
                    _('Reset'),
                    () => this.paymentMgr.resetPayment()
                );

                this.paymentMgr.startPayment(() => {
                    const count = this.paymentMgr.sproutCount();
                    gameui.setTopButtonValid(BUTTON_CONVERT_ID, count > 0 && (count % 3) == 0);
                    updateButton();
                });
                this.paymentMgr.addSprout(args.sproutCards, args.sproutCount);
            },
        });
    });