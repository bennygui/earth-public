/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * earth implementation : © Guillaume Benny bennygui@gmail.com
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * earth.js
 *
 * earth user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

var isDebug = window.location.host == 'studio.boardgamearena.com' || window.location.hash.indexOf('debug') > -1;
var debug = isDebug ? console.info.bind(window.console) : function () { };

define([
    "dojo",
    "dojo/_base/declare",
    "ebg/counter",
    "ebg/core/gamegui",
    g_gamethemeurl + "modules/BX/js/GameBase.js",
    g_gamethemeurl + "modules/BX/js/Numbers.js",
    g_gamethemeurl + "modules/BX/js/PlayerScoreTrait.js",
    g_gamethemeurl + "modules/EA/js/Mgr/PlayerPanelMgr.js",
    g_gamethemeurl + "modules/EA/js/Mgr/CardMgr.js",
    g_gamethemeurl + "modules/EA/js/Mgr/HandMgr.js",
    g_gamethemeurl + "modules/EA/js/Mgr/DeckMgr.js",
    g_gamethemeurl + "modules/EA/js/Mgr/LeafTokenMgr.js",
    g_gamethemeurl + "modules/EA/js/Mgr/FaunaBoardMgr.js",
    g_gamethemeurl + "modules/EA/js/Mgr/PlayerBoardMgr.js",
    g_gamethemeurl + "modules/EA/js/Mgr/PaymentMgr.js",
    g_gamethemeurl + "modules/EA/js/Mgr/GainMgr.js",
    g_gamethemeurl + "modules/EA/js/Mgr/TableauMgr.js",
    g_gamethemeurl + "modules/EA/js/Mgr/ScoreMgr.js",
    g_gamethemeurl + "modules/EA/js/Mgr/GaiaBoardMgr.js",
    g_gamethemeurl + "modules/EA/js/Mgr/CardDetailMgr.js",
    g_gamethemeurl + "modules/EA/js/Mgr/ObjectiveDetailMgr.js",
    g_gamethemeurl + "modules/EA/js/States/PlayerSetupTrait.js",
    g_gamethemeurl + "modules/EA/js/States/MainActionTrait.js",
    g_gamethemeurl + "modules/EA/js/States/AbilityTrait.js",
    g_gamethemeurl + "modules/EA/js/States/ActivationTrait.js",
    g_gamethemeurl + "modules/EA/js/States/EventTrait.js",
    g_gamethemeurl + "modules/EA/js/States/ConversionTrait.js",
    g_gamethemeurl + "modules/EA/js/States/ConfirmTrait.js",
    g_gamethemeurl + "modules/EA/js/NotificationTrait.js",
],
    function (dojo, declare) {
        return declare("bgagame.earth", [
            bx.GameBase,
            bx.PlayerScoreTrait,
            ea.PlayerSetupTrait,
            ea.MainActionTrait,
            ea.AbilityTrait,
            ea.ActivationTrait,
            ea.EventTrait,
            ea.ConversionTrait,
            ea.ConfirmTrait,
            ea.NotificationTrait,
        ], {
            CARD_WIDTH: 300,
            CARD_HEIGHT: 465,

            CARD_LOCATION_DECK: 0,
            CARD_LOCATION_DISCARD: 1,
            CARD_LOCATION_COMPOST: 2,
            CARD_LOCATION_HAND: 3,
            CARD_LOCATION_TABLEAU: 4,
            CARD_LOCATION_FAUNA_BOARD_FAUNA: 5,
            CARD_LOCATION_FAUNA_BOARD_ECOSYSTEM: 6,
            CARD_LOCATION_BOX: 7,
            CARD_LOCATION_PLAYER_BOARD: 8,
            CARD_LOCATION_GAIA_DECK: 9,
            CARD_LOCATION_GAIA_TABLEAU: 10,
            CARD_LOCATION_GAIA_COMPOST: 11,
            CARD_LOCATION_GAIA_DISCARD: 12,

            CARD_TYPE_TREE: 1,
            CARD_TYPE_HERB: 2,
            CARD_TYPE_MUSHROOM: 4,
            CARD_TYPE_BUSH: 8,
            CARD_TYPE_JOKER: 15,
            CARD_TYPE_TERRAIN: 16,
            CARD_TYPE_EVENT: 32,
            CARD_TYPE_ISLAND: 64,
            CARD_TYPE_CLIMATE: 128,
            CARD_TYPE_ECOSYSTEM: 256,
            CARD_TYPE_FAUNA: 512,
            CARD_TYPE_GAIA: 1024,

            LEAF_LOCATION_ID_PLAYER_BOARD: 0,
            LEAF_LOCATION_ID_ACTION: 1,
            LEAF_LOCATION_ID_FAUNA_BOARD_FAUNA: 2,
            LEAF_LOCATION_ID_FAUNA_BOARD_TABLEAU_BONUS: 3,

            MAIN_ACTION_ID_PLANT: 0,
            MAIN_ACTION_ID_COMPOST: 1,
            MAIN_ACTION_ID_WATER: 2,
            MAIN_ACTION_ID_GROW: 3,

            ABILITY_DRAW_CARD_FROM_DECK: 1,
            ABILITY_GROWTH: 3,
            ABILITY_SOIL: 4,
            ABILITY_SPROUT: 5,
            ABILITY_COMPOST_FROM_HAND: 6,
            ABILITY_COMPOST_FROM_DECK: 7,
            ABILITY_COMPOST_DESTROY: 8,
            ABILITY_PLANT_PAY_WITH_COMPOST: 9,
            ABILITY_PLANT_PAY_WITH_SPROUT: 10,
            ABILITY_PLANT_PAY_WITH_GROWTH: 11,

            AB_COLOR_RED: 1,
            AB_COLOR_YELLOW: 2,
            AB_COLOR_BLUE: 4,
            AB_COLOR_MULTICOLOR: 7,
            AB_COLOR_GREEN: 8,
            AB_COLOR_BROWN: 16,
            AB_COLOR_BLACK: 32,

            GAIA_PLAYER_ID: 1,

            PLAYER_YELLOW_BACK_COLOR: 'bbbbbb',

            EA_PREF_HAND_ZOOM_FACTOR_ID: 'EA_PREF_HAND_ZOOM_FACTOR_ID',
            EA_PREF_HAND_ZOOM_FACTOR_DEFAULT_VALUE: 70,

            EA_PREF_TABLEAU_ZOOM_FACTOR_ID: 'EA_PREF_TABLEAU_ZOOM_FACTOR_ID',
            EA_PREF_TABLEAU_ZOOM_FACTOR_DEFAULT_VALUE: 50,

            EA_PREF_FAUNA_ZOOM_FACTOR_ID: 'EA_PREF_FAUNA_ZOOM_FACTOR_ID',
            EA_PREF_FAUNA_ZOOM_FACTOR_DEFAULT_VALUE: 50,

            EA_PREF_HAND_PLACEMENT_ID: 'EA_PREF_HAND_PLACEMENT_ID',
            EA_PREF_HAND_PLACEMENT_DEFAULT_VALUE: 1,
            EA_PREF_HAND_PLACEMENT_VALUE_FIXED: 0,
            EA_PREF_HAND_PLACEMENT_VALUE_ABOVE: 1,
            EA_PREF_HAND_PLACEMENT_VALUE_BELOW: 2,

            EA_PREF_COMPACT_CARD_ID: 'EA_PREF_COMPACT_CARD_ID',
            EA_PREF_COMPACT_CARD_DEFAULT_VALUE: false,

            EA_PREF_CARD_HELP_ID: 'EA_PREF_CARD_HELP_ID',
            EA_PREF_CARD_HELP_DEFAULT_VALUE: true,

            EA_PREF_DARK_BACKGROUND_ID: 'EA_PREF_DARK_BACKGROUND_ID',
            EA_PREF_DARK_BACKGROUND_DEFAULT_VALUE: true,

            EA_PREF_CONFIRM_TIMER_ID: 'EA_PREF_CONFIRM_TIMER_ID',
            EA_PREF_CONFIRM_TIMER_DEFAULT_VALUE: true,

            EA_PREF_SHORTCUTS_ID: 'EA_PREF_SHORTCUTS_ID',
            EA_PREF_SHORTCUTS_DEFAULT_VALUE: true,

            EA_PREF_WELCOME_ID: 'EA_PREF_WELCOME_ID',
            EA_PREF_WELCOME_DEFAULT_VALUE: true,

            constructor() {
                this.setAlwaysFixTopActions();
                this.counters = {};

                this.playerPanelMgr = new ea.PlayerPanelMgr();
                this.cardMgr = new ea.CardMgr();
                this.handMgr = new ea.HandMgr();
                this.deckMgr = new ea.DeckMgr();
                this.leafTokenMgr = new ea.LeafTokenMgr();
                this.faunaBoardMgr = new ea.FaunaBoardMgr();
                this.playerBoardMgr = new ea.PlayerBoardMgr();
                this.paymentMgr = new ea.PaymentMgr();
                this.gainMgr = new ea.GainMgr();
                this.tableauMgr = new ea.TableauMgr();
                this.scoreMgr = new ea.ScoreMgr();
                this.gaiaBoardMgr = new ea.GaiaBoardMgr();
                this.cardDetailMgr = new ea.CardDetailMgr();
                this.objectiveDetailMgr = new ea.ObjectiveDetailMgr();

                this.htmlTextForLogKeys.push('cardImage');
                this.htmlTextForLogKeys.push('sproutImage');
                this.htmlTextForLogKeys.push('growthImage');
                this.htmlTextForLogKeys.push('mainActionId');
                this.htmlTextForLogKeys.push('compostFromHandIcon');
                this.htmlTextForLogKeys.push('compostFromDeckIcon');
                this.htmlTextForLogKeys.push('sproutIcon');
                this.htmlTextForLogKeys.push('growthIcon');
                this.htmlTextForLogKeys.push('soilIcon');
                this.htmlTextForLogKeys.push('plantIcon');
                this.htmlTextForLogKeys.push('drawFromDeckIcon');
                this.htmlTextForLogKeys.push('cardTypeEventIcon');
                this.htmlTextForLogKeys.push('compostDestroyIcon');

                this.localPreferenceToRegister.push([this.EA_PREF_HAND_ZOOM_FACTOR_ID, this.EA_PREF_HAND_ZOOM_FACTOR_DEFAULT_VALUE, {}]);
                this.localPreferenceToRegister.push([this.EA_PREF_TABLEAU_ZOOM_FACTOR_ID, this.EA_PREF_TABLEAU_ZOOM_FACTOR_DEFAULT_VALUE, {}]);
                this.localPreferenceToRegister.push([this.EA_PREF_FAUNA_ZOOM_FACTOR_ID, this.EA_PREF_FAUNA_ZOOM_FACTOR_DEFAULT_VALUE, {}]);
                this.localPreferenceToRegister.push([this.EA_PREF_HAND_PLACEMENT_ID, this.EA_PREF_HAND_PLACEMENT_DEFAULT_VALUE, {}]);
                this.localPreferenceToRegister.push([this.EA_PREF_COMPACT_CARD_ID, this.EA_PREF_COMPACT_CARD_DEFAULT_VALUE, {}]);
                this.localPreferenceToRegister.push([this.EA_PREF_CARD_HELP_ID, this.EA_PREF_CARD_HELP_DEFAULT_VALUE, {}]);
                this.localPreferenceToRegister.push([this.EA_PREF_DARK_BACKGROUND_ID, this.EA_PREF_DARK_BACKGROUND_DEFAULT_VALUE, {}]);
                this.localPreferenceToRegister.push([this.EA_PREF_CONFIRM_TIMER_ID, this.EA_PREF_CONFIRM_TIMER_DEFAULT_VALUE, {}]);
                this.localPreferenceToRegister.push([this.EA_PREF_SHORTCUTS_ID, this.EA_PREF_SHORTCUTS_DEFAULT_VALUE, {}]);
                this.localPreferenceToRegister.push([this.EA_PREF_WELCOME_ID, this.EA_PREF_WELCOME_DEFAULT_VALUE, {}]);
            },

            setup(gamedatas) {
                // Adapt back color for yellow player
                for (const playerId in gamedatas.players) {
                    if (gamedatas.players[playerId].player_color_name == 'yellow') {
                        gamedatas.players[playerId].color_back = this.PLAYER_YELLOW_BACK_COLOR;
                        const playerPanelNameElem = document.querySelector('#player_name_' + playerId + ' a');
                        if (playerPanelNameElem !== null) {
                            playerPanelNameElem.style.backgroundColor = '#' + this.PLAYER_YELLOW_BACK_COLOR;
                        }
                    }
                }

                // Preload images
                const preloadImageArray = [
                    'background/insideup.jpg',
                    'board/player_bottom.jpg',
                    'board/player.jpg',
                    'card/back.jpg',
                    'card/climate-01.jpg',
                    'card/island-01.jpg',
                    'icon/game-icon.jpg',
                    'icon/icons.png',
                    'icon/ui.png',
                    'scorepad.jpg',
                    'token/active_player.jpg',
                    'token/first_player.png',
                    'token/growth.png',
                    'token/leafs.png',
                    'token/soil.png',
                    'token/sprouts.png',
                    'bird.png',
                    'card/earth-01.jpg',
                    'card/earth-02.jpg',
                    'card/earth-03.jpg',
                    'card/earth-04.jpg',
                    'card/earth-05.jpg',
                    'card/earth-06.jpg',
                    'card/earth-07.jpg',
                    'card/earth-08.jpg',
                    'card/earth-09.jpg',
                    'card/earth-10.jpg',
                    'card/earth-11.jpg',
                    'card/earth-12.jpg',
                    'card/ecosystem-01.jpg',
                    'card/ecosystem-02.jpg',
                    'card/ecosystem-03.jpg',
                    'card/fauna-01.jpg',
                    'card/fauna-02.jpg',
                    'colorblind/blue.png',
                    'colorblind/red.png',
                    'colorblind/green.png',
                    'colorblind/multicolor.png',
                    'colorblind/black.png',
                    'colorblind/yellow.png',
                    'colorblind/brown.png',
                ];
                if (this.isGameSolo()) {
                    preloadImageArray.push('board/gaia.jpg');
                    preloadImageArray.push('card/gaia-01.jpg');
                    if (gamedatas.gaiaEasySide) {
                        preloadImageArray.push('board/player_solo_easy.jpg');
                    } else {
                        preloadImageArray.push('board/player_solo_hard.jpg');
                    }
                }
                if (gamedatas.isGameModeBeginner) {
                    preloadImageArray.push('board/fauna_beginner.jpg');
                } else {
                    preloadImageArray.push('board/fauna.jpg');
                }
                this.ensureSpecificGameImageLoading(preloadImageArray);

                for (const playerId in gamedatas.players) {
                    this.counters[playerId] = {
                        hand: new bx.Numbers(),
                        soil: new bx.Numbers(),
                        compost: new bx.Numbers(),
                        event: new bx.Numbers(),
                        sprout: new bx.Numbers([0, 0]),
                        growth: new bx.Numbers([0, 0]),
                        cardTree: new bx.Numbers(),
                        cardHerb: new bx.Numbers(),
                        cardMushroom: new bx.Numbers(),
                        cardBush: new bx.Numbers(),
                        cardJoker: new bx.Numbers(),
                        cardTerrain: new bx.Numbers(),
                        habitatSunny: new bx.Numbers(),
                        habitatWet: new bx.Numbers(),
                        habitatRocky: new bx.Numbers(),
                        habitatCold: new bx.Numbers(),
                        fauna: new bx.Numbers([0, 4]),
                    };
                }
                this.counters.gaiaSoil = new bx.Numbers();
                this.counters.gaiaSprout = new bx.Numbers();
                this.counters.gaiaGrowth = new bx.Numbers([0, 0]);
                this.counters.gaiaCompost = new bx.Numbers();
                this.counters.gaiaTableau = new bx.Numbers();
                this.counters.gaiaDeck = new bx.Numbers();
                this.counters.gaiaGaiaCard = new bx.Numbers();
                this.counters.gaiaRound = new bx.Numbers([0, 0]);
                this.counters.gaiaFauna = new bx.Numbers([0, 4]);

                this.counters.gaiaRound.registerOnFinishStepValues((counter) => {
                    const counterElem = counter.getTargetElement();
                    const newElem = document.createElement('div');
                    newElem.innerHTML = counterElem.parentElement.outerHTML;
                    newElem.classList.add('ea-gaia-round-popup');
                    document.body.appendChild(newElem);
                    setTimeout(() => newElem.remove(), 2000);
                });

                this.playerPanelMgr.setup(gamedatas);
                this.cardMgr.setup(gamedatas);
                this.handMgr.setup(gamedatas);
                this.deckMgr.setup(gamedatas);
                this.leafTokenMgr.setup(gamedatas);
                this.faunaBoardMgr.setup(gamedatas);
                this.playerBoardMgr.setup(gamedatas);
                this.paymentMgr.setup(gamedatas);
                this.gainMgr.setup(gamedatas);
                this.tableauMgr.setup(gamedatas);
                this.scoreMgr.setup(gamedatas);
                this.gaiaBoardMgr.setup(gamedatas);

                this.displayLastRound(gamedatas.isLastRound);

                this.faunaProgress = gamedatas.faunaProgress;

                // Place current player board at the top
                const areaPlayerElem = document.getElementById('ea-area-player-' + this.player_id);
                if (areaPlayerElem != null) {
                    areaPlayerElem.style.order = 2;
                }

                this.inherited(arguments);
            },

            onLocalPreferenceChanged(prefId, value) {
                switch (prefId) {
                    case this.EA_PREF_HAND_ZOOM_FACTOR_ID:
                        this.handMgr.setZoomFactor(value);
                        break;
                    case this.EA_PREF_TABLEAU_ZOOM_FACTOR_ID:
                        this.tableauMgr.setZoomFactor(value);
                        this.gainMgr.resetGain();
                        this.paymentMgr.resetPayment();
                        break;
                    case this.EA_PREF_FAUNA_ZOOM_FACTOR_ID:
                        this.faunaBoardMgr.setZoomFactor(value);
                        break;
                    case this.EA_PREF_HAND_PLACEMENT_ID:
                        this.handMgr.setHandPlacement(value);
                        this.playerPanelMgr.setupScrollShortcuts();
                        break;
                    case this.EA_PREF_COMPACT_CARD_ID:
                        const compactCheckbox = document.getElementById('ea-compact-card-checkbox');
                        compactCheckbox.checked = value;
                        break;
                    case this.EA_PREF_CARD_HELP_ID:
                        const cardHelpCheckbox = document.getElementById('ea-card-help-checkbox');
                        cardHelpCheckbox.checked = value;
                        this.tableauMgr.refresh();
                        this.gainMgr.resetGain();
                        this.paymentMgr.resetPayment();
                        if (value) {
                            document.documentElement.classList.remove('ea-hide-card-bottom');
                        } else {
                            document.documentElement.classList.add('ea-hide-card-bottom');
                        }
                        break;
                    case this.EA_PREF_DARK_BACKGROUND_ID:
                        const darkBackgroundCheckbox = document.getElementById('ea-dark-background-checkbox');
                        darkBackgroundCheckbox.checked = value;
                        if (value) {
                            document.documentElement.classList.add('ea-background-dark');
                        } else {
                            document.documentElement.classList.remove('ea-background-dark');
                        }
                        break;
                    case this.EA_PREF_SHORTCUTS_ID:
                        const shortcutsCheckbox = document.getElementById('ea-shortcuts-checkbox');
                        shortcutsCheckbox.checked = value;
                        this.playerPanelMgr.setupScrollShortcuts();
                        break;
                    case this.EA_PREF_WELCOME_ID:
                        const welcomeCheckbox = document.getElementById('ea-welcome-checkbox');
                        welcomeCheckbox.checked = value;
                        break;
                }
            },

            getHtmlTextForLogArg(key, value) {
                switch (key) {
                    case 'cardImage': {
                        const element = this.cardMgr.createCardElementFromCardId(value);
                        return element.outerHTML;
                    }
                    case 'sproutImage': {
                        const element = this.createSproutElement();
                        return element.outerHTML;
                    }
                    case 'growthImage': {
                        const element = this.createGrowthElement(0, 2);
                        return element.outerHTML;
                    }
                    case 'mainActionId': {
                        return '<div class="ea-main-action-id-color ea-main-action-id-color-' + value + '"></div>'
                    }
                    case 'compostFromHandIcon': {
                        return '<div class="ea-icon-compost-from-hand"></div>';
                    }
                    case 'compostFromDeckIcon': {
                        return '<div class="ea-icon-compost-from-deck"></div>';
                    }
                    case 'sproutIcon': {
                        return '<div class="ea-icon-sprout"></div>';
                    }
                    case 'growthIcon': {
                        return '<div class="ea-icon-growth"></div>';
                    }
                    case 'soilIcon': {
                        return '<div class="ea-icon-soil"></div>';
                    }
                    case 'plantIcon': {
                        return '<div class="ea-icon-plant"></div>';
                    }
                    case 'drawFromDeckIcon': {
                        return '<div class="ea-icon-draw-from-deck"></div>';
                    }
                    case 'cardTypeEventIcon': {
                        return '<div class="ea-icon-card-type-event"></div>';
                    }
                    case 'compostDestroyIcon': {
                        return '<div class="ea-icon-compost-destroy"></div>';
                    }
                }
                return this.inherited(arguments);
            },

            onStateChangedBefore(stateName, args) {
                this.inherited(arguments);
            },

            onStateChangedAfter(stateName, args) {
                this.inherited(arguments);
            },

            onUpdateActionButtonsBefore(stateName, args) {
                this.inherited(arguments);
                this.removeAllClickable();
                this.removeAllSelected();
                this.cardMgr.hideAllCardCost();
            },

            onUpdateActionButtonsdAfter(stateName, args) {
                this.addTopPlayEventButton(args);
                this.addTopPlayConversionButton(args);
                this.addTopUndoButton(args);
                this.handMgr.addTagClick();
                this.inherited(arguments);
            },

            onUndoBegin() {
                this.inherited(arguments);
                this.removeAllClickable();
                this.removeAllSelected();
                this.clearSelectedBeforeRemoveAll();
                this.clearTopButtonTimer();
                this.tableauMgr.disablePlacementMode();
                this.gainMgr.stop();
                this.paymentMgr.stop();
            },

            onLeavingState(stateName) {
                this.inherited(arguments);
                this.removeAllClickable();
                this.removeAllSelected();
                this.clearSelectedBeforeRemoveAll();
                this.clearTopButtonTimer();
                this.tableauMgr.disablePlacementMode();
                this.gainMgr.stop();
                this.paymentMgr.stop();
            },

            onLoadingComplete() {
                this.inherited(arguments);
                this.showWelcomeMessage();
            },

            showWelcomeMessage() {
                if (this.isReadOnly()) {
                    return;
                }
                if (!this.getLocalPreference(this.EA_PREF_WELCOME_ID)) {
                    return;
                }
                this.showInformationDialog(_('Welcome to Earth!'), [
                    _('${startb}If you do not want to see this message again, close it and disable the related option beside the player panels.${endb}'),
                    _('You can control the display of many aspects of the game in the option panel beside the player panels. You can also control the display of the cards in your hand with the ${caret} icon on the top left of the page.'),
                    '',
                    _('Card Help'),
                    _('A ${startb}?${endb} button is displayed below each cards in the game. Click on it to view the card in full and navigate between your hand, your tableau and the various boards.'),
                    '',
                    _('Card Flag'),
                    _('Three flag ${flag} icons are displayed below each cards in your hand: a Green flag, a Brown flag and a Red flag. This allows you to rank cards in your hand: Green flagged cards are displayed first, then Brown, then cards with no flag, and, finally, Red flags. You can use this however you want to help you remember which card to plant, which card to compost, etc.'),
                    '',
                    _('Undo'),
                    _('If you misclick, you can undo what you did most of the time, but there are exceptions, notably when choosing the main action and when drawing and discarding cards.'),
                    '',
                    _('Have fun!'),
                ], {
                    before: '<div class="ea-game-icon"></div>',
                    caret: '<i class="fa fa-caret-right"></i>',
                    flag: '<i class="fa fa-flag"></i>',
                    startb: '<b>',
                    endb: '</b>',
                });
            },

            getElementCreationElement() {
                return document.getElementById('ea-element-creation');
            },

            usesCompactCards() {
                return this.getLocalPreference(this.EA_PREF_COMPACT_CARD_ID);
            },

            showConfirmDialogIfConfirm(title, condition = true) {
                return this.showConfirmDialogCondition(
                    title,
                    condition && this.mustConfirmActions()
                );
            },

            mustConfirmActions() {
                return (document.querySelector('.ea-pref-confim-actions') !== null);
            },

            displayLastRound(doDisplay = true) {
                if (doDisplay) {
                    document.getElementById('ea-display-last-round').classList.remove('bx-hidden');
                } else {
                    document.getElementById('ea-display-last-round').classList.add('bx-hidden');
                }
            },

            parseCompactCardList(cardList) {
                if (typeof cardList != 'string') {
                    return cardList;
                }
                return cardList.split(';').map((c) => this.parseCompactCard(c));
            },

            parseCompactCard(card) {
                const split = card.split('|').map((f) => this.parseCompactNumber(f));
                return {
                    cardId: split[0],
                    playerId: split[1],
                    locationId: split[2],
                    locationOrder: split[3],
                    locationX: split[4],
                    locationY: split[5],
                    handChoosing: Boolean(split[6]),
                    privateVisibility: Boolean(split[7]),
                    sproutCount: split[8],
                    growthCount: split[9],
                    canPlantOver: Boolean(split[10]),
                };
            },

            parseCompactNumber(string) {
                if (string.length == 0) {
                    return null;
                } else {
                    return parseInt(string);
                }
            },

            createSoilTokenElement() {
                const element = document.createElement('div');
                element.classList.add('ea-token-soil');
                return element;
            },

            createSproutElement() {
                const sproutElement = document.createElement('div');
                sproutElement.classList.add('ea-token-sprout');

                const shadowElement = document.createElement('div');
                shadowElement.classList.add('ea-token-sprout-shadow');

                const containerElement = document.createElement('div');
                containerElement.classList.add('ea-token-sprout-container');
                containerElement.appendChild(sproutElement);
                containerElement.appendChild(shadowElement);

                return containerElement;
            },

            animateSproutElement(sproutElem, appear, isInstantaneous) {
                if (isInstantaneous) {
                    return Promise.resolve();
                }
                return new Promise((resolve, reject) => {
                    const animElem = sproutElem.querySelector('.ea-token-sprout');
                    const animation = new dojo.Animation({
                        curve: appear ? [0, 360] : [360, 0],
                        duration: 300,
                        onAnimate: (v) => {
                            animElem.style.transform = 'rotate(' + v + 'deg) scale(' + Math.min(1, 1.2 * v / 360.0) + ')';
                        },
                        onEnd: () => {
                            animElem.style.transform = '';
                            resolve();
                        },
                    });
                    animation.play();
                });
            },

            createGrowthElement(index, growthMax) {
                const growthElement = document.createElement('div');
                if (index + 1 == growthMax) {
                    growthElement.classList.add('ea-token-canopy');
                } else {
                    growthElement.classList.add('ea-token-trunk');
                }

                const containerElement = document.createElement('div');
                containerElement.classList.add('ea-token-growth-container');
                containerElement.appendChild(growthElement);

                if (index == 0) {
                    const shadowElement = document.createElement('div');
                    if (index + 1 == growthMax) {
                        shadowElement.classList.add('ea-token-canopy-shadow');
                    } else {
                        shadowElement.classList.add('ea-token-trunk-shadow');
                    }
                    containerElement.appendChild(shadowElement);
                }

                containerElement.style.zIndex = index;
                if (index == 0 && growthMax == 1) {
                    containerElement.style.top = 'calc(-10px * var(--ea-zoom))';
                } else {
                    containerElement.style.top = 'calc(' + (-10 * index) + 'px * var(--ea-zoom))';
                }
                return containerElement;
            },

            animateGrowthElement(growthElem, appear, isInstantaneous) {
                if (isInstantaneous) {
                    return Promise.resolve();
                }
                return new Promise((resolve, reject) => {
                    let animElem = growthElem.querySelector('.ea-token-trunk');
                    if (animElem === null) {
                        animElem = growthElem.querySelector('.ea-token-canopy');
                    }
                    const overScaleAnim = new dojo.Animation({
                        curve: appear ? [0.0, 1.5] : [1, 1.5],
                        duration: 200,
                        onAnimate: (v) => {
                            animElem.style.transform = 'scale(' + v + ')';
                        },
                        onEnd: () => {
                            const reScaleAnim = new dojo.Animation({
                                curve: appear ? [1.5, 1] : [1.5, 0],
                                duration: 200,
                                onAnimate: (v) => {
                                    animElem.style.transform = 'scale(' + v + ')';
                                },
                                onEnd: () => {
                                    animElem.style.transform = '';
                                    resolve();
                                },
                            });
                            reScaleAnim.play();
                        },
                    });
                    overScaleAnim.play();
                });
            },
        });
    });