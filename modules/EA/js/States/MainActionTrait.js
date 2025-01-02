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
    g_gamethemeurl + "modules/BX/js/UtilTrait.js",
],
    function (dojo, declare) {
        return declare("ea.MainActionTrait", bx.UtilTrait, {
            showMainActionConfirmDialog(name, condition = true) {
                return this.showConfirmDialogIfConfirm(_('Confirm main action:') + ' <b>' + name + '</b>', condition);
            },

            // Main
            onStateMainActionChoose(args) {
                debug('onStateMainActionChoose');
                debug(args);
                const actions = [
                    {
                        id: gameui.MAIN_ACTION_ID_PLANT,
                        shortname: _('Plant'),
                        name: this.format_string_recursive(
                            _('Plant +2 ${plantIcon} +4 ${drawFromDeckIcon}/1'),
                            { plantIcon: '', drawFromDeckIcon: '', }
                        )
                    },
                    {
                        id: gameui.MAIN_ACTION_ID_COMPOST,
                        shortname: _('Compost'),
                        terrainId: 10178,
                        name: this.format_string_recursive(
                            _('Compost +5 ${soilIcon} +2 ${compostFromDeckIcon}'),
                            { soilIcon: '', compostFromDeckIcon: '', }
                        )
                    },
                    {
                        id: gameui.MAIN_ACTION_ID_WATER,
                        shortname: _('Water'),
                        terrainId: 10180,
                        name: this.format_string_recursive(
                            _('Water +6 ${sproutIcon} +2 ${soilIcon}'),
                            { sproutIcon: '', soilIcon: '', }
                        )
                    },
                    {
                        id: gameui.MAIN_ACTION_ID_GROW,
                        shortname: _('Grow'),
                        terrainId: 10179,
                        name: this.format_string_recursive(
                            _('Grow +4 ${drawFromDeckIcon} +2 ${growthIcon}'),
                            { drawFromDeckIcon: '', growthIcon: '' }
                        )
                    },
                ];
                for (const action of actions) {
                    let serverAction = null;
                    if (args.args.mainActionIds.indexOf(action.id) < 0) {
                        serverAction = () => {
                            this.showMessage(
                                this.format_string_recursive(
                                    _('You cannot choose ${actionName}: your ${terrainName} forbids you from doing so'),
                                    {
                                        actionName: action.shortname,
                                        terrainName: _(this.gamedatas.carddefs[action.terrainId].name),
                                    }
                                ),
                                'error'
                            );
                        };
                    } else {
                        serverAction = () => {
                            this.showMainActionConfirmDialog(action.shortname).then(() => {
                                this.serverAction('mainActionChoose', { mainActionId: action.id });
                            });
                        };
                    }
                    const buttonId = 'button-main-action-' + action.id;
                    this.addTopButtonPrimary(
                        buttonId,
                        action.name,
                        serverAction
                    );
                    const buttonElem = document.getElementById(buttonId);
                    buttonElem.classList.add('ea-main-action-id-color-' + action.id);
                    buttonElem.classList.add('ea-bgabutton-black-border');
                    const playerBoardActionElem = this.playerBoardMgr.getPlayerIdActionElem(this.player_id, action.id);
                    if (playerBoardActionElem !== null) {
                        this.addClickable(playerBoardActionElem, serverAction);
                    }
                }
            },

            // Compost
            onStateActionCompostChoose(args) {
                debug('onStateActionCompostChoose');
                debug(args);
                const actions = [
                    {
                        id: 'compostActionChooseGainSoil',
                        confirm: false,
                        name: this.format_string_recursive(_('Gain 2 ${soilIcon}'), { soilIcon: 'soil' })
                    },
                    {
                        id: 'compostActionChooseCompostFromDeck',
                        confirm: true,
                        name: this.format_string_recursive(_('Compost 2 cards from deck ${compostFromDeckIcon}'), { compostFromDeckIcon: '' })
                    },
                ];
                for (const action of actions) {
                    const serverAction = () => {
                        this.showMainActionConfirmDialog(action.name, action.confirm).then(() => {
                            this.serverAction(action.id);
                        });
                    };
                    this.addTopButtonPrimary(
                        'button-compost-choose-' + action.id,
                        action.name,
                        serverAction
                    );
                }
            },

            // Water
            onStateActionWaterChoose(args) {
                debug('onStateActionWaterChoose');
                debug(args);
                const actions = [
                    { id: 'waterActionChooseGainSoil', name: this.format_string_recursive(_('Gain 2 ${soilIcon}'), { soilIcon: 'soil' }) },
                    {
                        id: 'waterActionChoosePlaceSprout',
                        name: this.format_string_recursive(
                            _('Gain 2 ${sproutIcon}'),
                            {
                                sproutIcon: _('sprout(s)'),
                            }
                        )
                    },
                ];
                for (const action of actions) {
                    const serverAction = () => {
                        this.serverAction(action.id);
                    };
                    this.addTopButtonPrimary(
                        'button-water-choose-' + action.id,
                        action.name,
                        serverAction
                    );
                }
            },
            onStateActionWaterPlaceSprout(args) {
                debug('onStateActionWaterPlaceSprout');
                debug(args);
                this.onAbilityGain('waterActionPlaceSprout', args.args);
            },

            // Grow
            onStateActionGrowChoose(args) {
                debug('onStateActionGrowChoose');
                debug(args);
                const actions = [
                    {
                        id: 'growActionChooseDrawCard',
                        confirm: true,
                        name: this.format_string_recursive(
                            _('Draw 2 ${drawFromDeckIcon}'),
                            { drawFromDeckIcon: '', }
                        )
                    },
                    {
                        id: 'growActionChoosePlaceGrowth',
                        confirm: false,
                        name: this.format_string_recursive(
                            _('Gain 2 ${growthIcon}'),
                            {
                                growthIcon: _('growth(s)'),
                            }
                        )
                    },
                ];
                for (const action of actions) {
                    const serverAction = () => {
                        this.showMainActionConfirmDialog(action.name, action.confirm).then(() => {
                            this.serverAction(action.id);
                        });
                    };
                    this.addTopButtonPrimary(
                        'button-grow-choose-' + action.id,
                        action.name,
                        serverAction
                    );
                }
            },
            onStateActionGrowPlaceGrowth(args) {
                debug('onStateActionGrowPlaceGrowth');
                debug(args);
                this.onAbilityGain('growActionPlaceGrowth', args.args);
            },

            // Plant
            onStateActionPlantActiveFirstCard(args) {
                this.onPlantCard(args.args);
            },
            onStateActionPlantActiveSecondCard(args) {
                this.onPlantCard(args.args, true);
            },
            onStateActionPlantInactiveCard(args) {
                this.onPlantCard(args.args);
            },
            onStateActionPlantAdditionalCard(args) {
                this.onPlantCard(args.args);
            },
            onPlantCard(args, plantingSecondCard = false) {
                debug('onPlantCard');
                debug(args);
                for (const cardId in args.tableauPerCardId) {
                    args.tableauPerCardId[cardId] = gameui.parseCompactCardList(args.tableauPerCardId[cardId]);
                }
                const ID_PLANT_CARD = 'button-plant-card';
                this.addTopButtonPrimaryWithValid(
                    ID_PLANT_CARD,
                    this.format_string_recursive(_('Plant card ${plantIcon}'), { plantIcon: '' }),
                    _('You must select a card from your hand and a position in your tableau'),
                    () => {
                        this.serverAction('plantActionPlanCard', {
                            cardId: this.tableauMgr.getSelectedCardId(),
                            posX: this.tableauMgr.getPosX(),
                            posY: this.tableauMgr.getPosY(),
                        });
                    }
                );
                this.setTopButtonValid(ID_PLANT_CARD, false);
                this.addTopButtonImportant(
                    'button-skip-plant',
                    plantingSecondCard
                        ? _('Do not plant second card')
                        : _('Do not plant any cards'),
                    () => {
                        gameui.showConfirmDialog(
                            plantingSecondCard
                                ? _('You have chosen not to plant your second card, are you sure?')
                                : _('You have chosen to plant nothing, are you sure?')
                        ).then(() => {
                            this.serverAction('plantActionSkipPlanting');
                        });
                    }
                );
                if (Object.keys(args.tableauPerCardId).length > 0) {
                    this.tableauMgr.enablePlacementMode((x, y) => {
                        this.setTopButtonValid(ID_PLANT_CARD, x !== null & y !== null);
                        const selectedCardId = this.tableauMgr.getSelectedCardId();
                        if (selectedCardId !== null) {
                            const costContainer = this.cardMgr.getCardCostContainerElementById(selectedCardId);
                            costContainer.classList.remove('ea-card-cost-inactive');
                            const costCount = this.cardMgr.getCardCostCountElementById(selectedCardId);
                            costCount.innerText = args.costPerCardId[selectedCardId];
                            for (const card of args.tableauPerCardId[selectedCardId]) {
                                if (card.cardId !== null && card.canPlantOver && card.locationX == x && card.locationY == y) {
                                    costCount.innerText = 0;
                                    break;
                                }
                            }
                        }
                    });
                }
                const unselectHandCards = () => {
                    for (const cardId in args.tableauPerCardId) {
                        const cardElem = this.cardMgr.getCardSelectionElementById(cardId);
                        this.removeSelected(cardElem);
                        const costContainer = this.cardMgr.getCardCostContainerElementById(cardId);
                        costContainer.classList.add('ea-card-cost-inactive');
                    }
                };
                for (const cardId in args.tableauPerCardId) {
                    const cardElem = this.cardMgr.getCardSelectionElementById(cardId);
                    const onClick = () => {
                        unselectHandCards();
                        if (this.tableauMgr.getSelectedCardId() == cardId) {
                            this.tableauMgr.setSelectedCardId(null);
                        } else {
                            this.addSelected(cardElem);
                            this.tableauMgr.setSelectedCardId(cardId);
                            const costContainer = this.cardMgr.getCardCostContainerElementById(cardId);
                            costContainer.classList.remove('ea-card-cost-inactive');
                            const costCount = this.cardMgr.getCardCostCountElementById(cardId);
                            costCount.innerText = args.costPerCardId[cardId];
                            const x = this.tableauMgr.getPosX();
                            const y = this.tableauMgr.getPosY();
                            if (x !== null && y !== null) {
                                for (const card of args.tableauPerCardId[cardId]) {
                                    if (card.cardId !== null && card.canPlantOver && card.locationX == x && card.locationY == y) {
                                        costCount.innerText = 0;
                                        break;
                                    }
                                }
                            }
                        }
                        this.tableauMgr.buildPlayerTableauFromCards(this.player_id, args.tableauPerCardId[cardId]);
                    };
                    this.addClickable(cardElem, onClick);
                    if (this.elementWasSelectedBeforeRemoveAll(cardElem)) {
                        onClick();
                    }
                }
                this.clearSelectedBeforeRemoveAll();
            },

            onStateActionPlantActiveKeepCard(args) {
                debug('onStateActionPlantActiveKeepCard');
                debug(args);
                this.addTopButtonSelection(
                    args.args.nbCards >= 2
                        ? _('Keep selected cards')
                        : _('Keep selected card'),
                    this.format_string_recursive(
                        _('You must choose ${nbCards} card(s) to keep'),
                        {
                            nbCards: args.args.nbCards,
                        }
                    ),
                    {
                        ids: args.args.handCardIds,
                        onElement: (id) => this.cardMgr.getCardSelectionElementById(id),
                        onClick: (ids) => {
                            if (!(ids instanceof Array)) {
                                if (ids === null) {
                                    ids = [];
                                } else {
                                    ids = [ids];
                                }
                            }
                            this.serverAction('planActionKeepOneDrawnCard', { cardIds: ids.join(',') })
                        },
                    },
                    parseInt(args.args.nbCards)
                );
            },

            onStateActionPlantSelectGain(args) {
                debug('onStateActionPlantSelectGain');
                debug(args);

                this.onAbilityGain('plantActionGain', args.args);

                this.gainMgr.registerOnUpdateTableau(() => {
                    const cardElem = gameui.cardMgr.getCardSelectionElementById(args.args.activatedAfterCopyCardId);
                    gameui.addSelected(cardElem);
                });
            },

            onStateActionPlantSelectPayment(args) {
                debug('onStateActionPlantSelectPayment');
                debug(args);
                const BUTTON_PAY_PLACE_ID = 'button-pay-place';

                const placeListLogs = [];
                const placeListArgs = {};

                placeListLogs.push('${payedSoil} ${soilIcon}');
                placeListArgs['payedSoil'] = args.args.totalCost;
                placeListArgs['soilIcon'] = _('soil');

                const updateButton = () => {
                    const buttonElem = document.getElementById(BUTTON_PAY_PLACE_ID);
                    buttonElem.innerHTML = this.format_string_recursive(
                        _('Pay ${totalCost}: ${placeList}'),
                        {
                            totalCost: args.args.totalCost,
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
                    _('You must pay exactly the required total'),
                    () => {
                        const payedSproutList = this.paymentMgr.getPayedSproutList().join(',');
                        const payedGrowthList = this.paymentMgr.getPayedGrowthList().join(',');
                        const payedCompostFromHandCardIds = this.paymentMgr.compostFromHandCardIds().join(',');
                        this.paymentMgr.pause();
                        this.serverAction('plantActionPlanCardWithPayment', {
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
                    placeListArgs['payedSoil'] = Math.max(
                        0,
                        args.args.totalCost
                        - this.paymentMgr.sproutCount()
                        - this.paymentMgr.growthCount()
                        - this.paymentMgr.compostFromHandCount()
                    );
                    gameui.setTopButtonValid(
                        BUTTON_PAY_PLACE_ID,
                        args.args.soilCount
                        + this.paymentMgr.sproutCount()
                        + this.paymentMgr.growthCount()
                        + this.paymentMgr.compostFromHandCount() >= args.args.totalCost
                        &&
                        this.paymentMgr.sproutCount()
                        + this.paymentMgr.growthCount()
                        + this.paymentMgr.compostFromHandCount() <= args.args.totalCost
                    );
                    updateButton();
                });
                switch (args.args.paymentType) {
                    case gameui.ABILITY_PLANT_PAY_WITH_SPROUT:
                        placeListLogs.push('${payedSprout} ${sproutIcon}');
                        placeListArgs['payedSprout'] = 0;
                        placeListArgs['sproutIcon'] = _('sprout(s)');
                        this.paymentMgr.addSprout(args.args.sproutCards, args.args.totalCost);
                        break;
                    case gameui.ABILITY_PLANT_PAY_WITH_GROWTH:
                        placeListLogs.push('${payedGrowth} ${growthIcon}');
                        placeListArgs['payedGrowth'] = 0;
                        placeListArgs['growthIcon'] = _('growth(s)');
                        this.paymentMgr.addGrowth(args.args.growthCards, args.args.totalCost);
                        break;
                    case gameui.ABILITY_PLANT_PAY_WITH_COMPOST:
                        placeListLogs.push('${payedCompostFromHand} ${compostFromHandIcon}');
                        placeListArgs['payedCompostFromHand'] = 0;
                        placeListArgs['compostFromHandIcon'] = _('compost');
                        this.paymentMgr.addCompostFromHand(args.args.handCardIds, args.args.totalCost);
                        break;
                }
            },

            // Solo Fauna
            onStateActionSoloFaunaChoose(args) {
                debug('onStateActionSoloFaunaChoose');
                debug(args);
                const actions = [
                    {
                        pos: [0, 0],
                        title: _('Top-Left Fauna'),
                    },
                    {
                        pos: [0, 1],
                        title: _('Top-Right Fauna'),
                    },
                    {
                        pos: [1, 0],
                        title: _('Bottom-Left Fauna'),
                    },
                    {
                        pos: [1, 1],
                        title: _('Bottom-Right Fauna'),
                    },
                ];
                for (const pos of args.args.faunaPositions) {
                    const elem = this.faunaBoardMgr.getFaunaBoardFaunaCardElement(pos[0], pos[1]);
                    this.addClickable(
                        elem.querySelector('.ea-card-selection'),
                        () => this.serverAction('soloFaunaChoose', { x: pos[0], y: pos[1] })
                    );
                    for (const action of actions) {
                        if (this.areArraysEqual(action.pos, pos)) {
                            this.addTopButtonPrimary(
                                'button-solo-fauna-choose-' + pos[0] + '-' + pos[1],
                                action.title,
                                () => this.serverAction('soloFaunaChoose', { x: pos[0], y: pos[1] })
                            );
                            break;
                        }
                    }
                }
            },
        });
    });