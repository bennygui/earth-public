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
    "ebg/core/gamegui",
    g_gamethemeurl + "modules/BX/js/AnimationTrait.js",
    g_gamethemeurl + "modules/BX/js/ButtonTrait.js",
    g_gamethemeurl + "modules/BX/js/PreferenceTrait.js",
    g_gamethemeurl + "modules/BX/js/UITrait.js",
    g_gamethemeurl + "modules/BX/js/UtilTrait.js",
],
    function (dojo, declare) {
        return declare("bx.GameBase", [
            ebg.core.gamegui,
            bx.AnimationTrait,
            bx.ButtonTrait,
            bx.PreferenceTrait,
            bx.UITrait,
            bx.UtilTrait,
        ], {
            DEFAULT_PLAYER_YELLOW_COLOR: 'fbff00',
            DEFAULT_PLAYER_YELLOW_BACK_COLOR: 'bbbbbb',

            constructor() {
                this.alwaysFixTopActions = false;
                this.alwaysFixTopActionsMaximum = 30;
                this.seenStateSet = new Set();
                this.seenStateList = [];
                // Format: ['notif', delay]
                if (this.notificationsToRegister === undefined) {
                    this.notificationsToRegister = [];
                }
                this.notificationsToRegister.push(['message', null]);
                this.notificationsToRegister.push(['NTF_CHANGE_PRIVATE_STATE', 1]);
                this.notificationsToRegister.push(['NTF_UNDO_PRIVATE_STATE', 1]);
                this.notificationsToRegister.push(['NTF_MULTI_ACTIVE_ARGS', 1]);
                this.notificationsToRegister.push(['NTF_UNDO_BEGIN', 1]);
                this.notificationsToRegister.push(['NTF_DEBUG_RELOAD', 1]);

                if (this.htmlTextForLogKeys === undefined) {
                    this.htmlTextForLogKeys = [];
                }
            },

            setup(gamedatas) {
                this.inherited(arguments);
                this.setupBrowserDetection();
                this.setupNotifications();
            },

            // [Undocumented] Override BGA framework functions to call onLoadingComplete when loading is done
            setLoader(value, max) {
                this.inherited(arguments);
                if (!this.isLoadingComplete && value >= 100) {
                    this.isLoadingComplete = true;
                    this.onLoadingComplete();
                }
            },

            onLoadingComplete() {
                this.inherited(arguments);
                if (!this.isReadOnly()) {
                    this.showWelcomeMessage();
                }
            },

            showWelcomeMessage() { },

            isReadOnly() {
                return this.isSpectator || typeof g_replayFrom != 'undefined' || g_archive_mode;
            },

            isGameSolo() {
                return (Object.keys(this.gamedatas.players).length == 1);
            },

            getAllPlayerIds() {
                const playerIds = Object.keys(this.gamedatas.players);
                playerIds.sort((p1, p2) => this.gamedatas.players[p1].player_no - this.gamedatas.players[p2].player_no);
                return playerIds.map((pId) => parseInt(pId));
            },

            updatePlayerOrdering() {
                this.inherited(arguments);
            },

            onScreenWidthChange() {
                this.inherited(arguments);
            },

            setAlwaysFixTopActions(alwaysFixed = true, maximum = 30) {
                this.alwaysFixTopActions = alwaysFixed;
                this.alwaysFixTopActionsMaximum = maximum;
                this.adaptStatusBar();
            },

            adaptStatusBar() {
                this.inherited(arguments);

                if (this.alwaysFixTopActions) {
                    const afterTitleElem = document.getElementById('after-page-title');
                    const titleElem = document.getElementById('page-title');
                    let zoom = getComputedStyle(titleElem).zoom;
                    if (!zoom) {
                        zoom = 1;
                    }

                    const titleRect = afterTitleElem.getBoundingClientRect();
                    if (titleRect.top < 0 && (titleElem.offsetHeight < (window.innerHeight * this.alwaysFixTopActionsMaximum / 100))) {
                        const afterTitleRect = afterTitleElem.getBoundingClientRect();
                        titleElem.classList.add('fixed-page-title');
                        titleElem.style.width = ((afterTitleRect.width - 10) / zoom) + 'px';
                        afterTitleElem.style.height = titleRect.height + 'px';
                    } else {
                        titleElem.classList.remove('fixed-page-title');
                        titleElem.style.width = 'auto';
                        afterTitleElem.style.height = '0px';
                    }
                }
            },

            setModeInstataneous() {
                this.inherited(arguments);
                // From https://bga-devs.github.io/blog/posts/a-real-fast-replay-mode/
                dojo.style('loader_mask', {
                    height: '100vh',
                    position: 'fixed',
                });
                dojo.style('leftright_page_wrapper', 'display', 'none');
            },

            unsetModeInstantaneous() {
                this.inherited(arguments);
                // From https://bga-devs.github.io/blog/posts/a-real-fast-replay-mode/
                dojo.style('leftright_page_wrapper', 'display', 'block');
            },

            onEnteringState(stateName, args) {
                this.clearBxGeneralActionButtons();
                this.seenStateSet.add(stateName);
                this.seenStateList.push(stateName);

                if (!this.getButtonUsesBGAGeneralActions()) {
                    this.removeAllClickable();
                    this.removeAllSelected();
                    this.clearSelectedBeforeRemoveAll();
                    this.clearTopButtonTimer();
                    this.clearActionState();
                    this.clearBxGeneralActionButtons();
                }

                if (args.args && args.args._private && args.args._private.privateStateId && args.args._private.privateStateId != args.id) {
                    this.setPrivateState(args.args._private.privateStateId, args.args._private);
                } else if (this.gamedatas.gamestate.type != 'game') {
                    if (this.previousStateName != stateName || !this.areObjectsEqual(this.previousStateArgs, args.args)) {
                        this.onStateChangedInternal(stateName, args);
                    }
                    this.previousStateName = stateName;
                    this.previousStateArgs = dojo.clone(args.args);
                }
            },

            onLeavingState(stateName) {
                this.seenStateSet.add(stateName);
                this.removeAllClickable();
                this.removeAllSelected();
                this.clearSelectedBeforeRemoveAll();
                this.clearTopButtonTimer();
                this.clearActionState();
                this.clearBxGeneralActionButtons();
            },

            onStateChangedInternal(stateName, args) {
                this.seenStateSet.add(stateName);
                this.onStateChangedBefore(stateName, args);
                this.onStateChangedNow(stateName, args);
                const functionName = this.toCamelCase('ON_' + stateName);
                if (functionName in this) {
                    debug('onStateChangedInternal: ' + stateName + ' (calling ' + functionName + ')');
                    this[functionName](args);
                } else {
                    debug('onStateChangedInternal: ' + stateName + ' (no function named ' + functionName + ')');
                }
                this.onStateChangedAfter(stateName, args);
            },
            onStateChangedBefore(stateName, args) { },
            onStateChangedNow(stateName, args) { },
            onStateChangedAfter(stateName, args) {
                if (!this.getButtonUsesBGAGeneralActions()) {
                    this.addTopUndoButton(args.args);
                }
            },

            onUpdateActionButtons(stateName, args) {
                this.seenStateSet.add(stateName);
                this.onUpdateActionButtonsBefore(stateName, args);
                this.onUpdateActionButtonsNow(stateName, args);
                const functionName = this.toCamelCase('ON_BUTTONS_' + stateName);
                if (functionName in this) {
                    debug('onUpdateActionButtons: ' + stateName + ' (calling ' + functionName + ')');
                    this[functionName](args);
                } else {
                    debug('onUpdateActionButtons: ' + stateName + ' (no function named ' + functionName + ')');
                }
                this.onUpdateActionButtonsdAfter(stateName, args);
            },
            onUpdateActionButtonsBefore(stateName, args) {
                if (this.getButtonUsesBGAGeneralActions()) {
                    this.removeAllClickable();
                    this.removeAllSelected();
                }
            },
            onUpdateActionButtonsNow(stateName, args) { },
            onUpdateActionButtonsdAfter(stateName, args) {
                if (this.getButtonUsesBGAGeneralActions()) {
                    this.addTopUndoButton(args);
                }
            },

            onUndoBegin() {
                this.removeAllClickable();
                this.removeAllSelected();
                this.clearSelectedBeforeRemoveAll();
                this.clearTopButtonTimer();
                this.clearActionState();
            },

            clearActionState() { },

            // @Override: This is a built-in BGA method, overriden to inject html into log items
            format_string_recursive(log, args) {
                try {
                    if (log && args && !args.processed) {
                        args.processed = true;
                        for (const key of this.htmlTextForLogKeys) {
                            if (!(key in args)) {
                                args[key] = '';
                            } else {
                                args[key] = this.getHtmlTextForLogArg(key, args[key]);
                            }
                        }
                    }
                } catch (e) {
                    console.error(log, args, "Exception thrown", e.stack);
                }
                return this.inherited(arguments);
            },

            seenMoreThanOnePrivateState() {
                const privateStateSet = new Set();
                for (const stateId in this.gamedatas.gamestates) {
                    const state = this.gamedatas.gamestates[stateId];
                    if (state.type == 'private') {
                        privateStateSet.add(state.name);
                    }
                }
                return Array.from(this.seenStateSet).filter((s) => privateStateSet.has(s)).length > 1;
            },

            seenMoreThanOneStateList() {
                return this.seenStateList.length > 1;
            },

            setupBrowserDetection() {
                if (!navigator) {
                    return;
                }
                if (
                    (
                        navigator.platform
                        && /iPad|iPhone|iPod/.test(navigator.platform)
                    )
                    ||
                    (
                        /iPad|iPhone|iPod/.test(navigator.userAgent)
                        && !window.MSStream
                    )
                    ||
                    (
                        // Also include Safari on MacOS
                        /^((?!chrome|android).)*safari/i.test(navigator.userAgent)
                    )
                ) {
                    document.body.classList.add('bx-browser-is-ios');
                }
            },

            setupBackColorForPlayerColor(gamedatas, backColor = null, frontColor = null) {
                if (backColor === null) {
                    backColor = this.DEFAULT_PLAYER_YELLOW_BACK_COLOR;
                }
                if (frontColor === null) {
                    frontColor = this.DEFAULT_PLAYER_YELLOW_COLOR;
                }
                frontColor = frontColor.toLowerCase();
                for (const playerId in gamedatas.players) {
                    if (gamedatas.players[playerId].player_color.toLowerCase() == 'fbff00') {
                        gamedatas.players[playerId].color_back = backColor;
                        const playerPanelNameElem = document.querySelector('#player_name_' + playerId + ' a');
                        if (playerPanelNameElem !== null) {
                            playerPanelNameElem.style.backgroundColor = '#' + backColor;
                        }
                        const playAreaNameElems = document.querySelectorAll('#game_play_area .player-name[data-player-id="' + playerId + '"]');
                        for (const elem of playAreaNameElems) {
                            elem.style.backgroundColor = '#' + backColor;
                        }
                    }
                }
            },

            getHtmlTextForLogArg(key, value) {
                return '';
            },

            setupNotifications() {
                for (const notif of this.notificationsToRegister) {
                    const notifId = notif[0];
                    const delay = notif[1];
                    const functionName = 'notif_' + this.toPascalCase(notifId.replace(/^NTF_/, ''));
                    debug('Registering notification ' + notifId + ' (' + functionName + ')');
                    dojo.subscribe(notifId, this, (args) => {
                        if (notifId != 'message') {
                            const wasSentPrivate = args
                                && args.args
                                && args.args.playerId == this.player_id
                                && this.isTrue(args.args.wasSentPrivate);
                            if (wasSentPrivate) {
                                if (delay !== null && delay < 0) {
                                    this.notifqueue.setSynchronousDuration(0);
                                }
                            } else {
                                this.onBeforeNotification(notifId, args);
                                let promise = this[functionName](args);
                                if (promise instanceof Array) {
                                    promise = Promise.all(promise);
                                } else if (!promise || !promise.then) {
                                    promise = Promise.resolve();
                                }
                                promise.then(() => {
                                    this.onAfterNotification(notifId, args);
                                    if (delay !== null && delay < 0) {
                                        this.notifqueue.setSynchronousDuration(0);
                                    }
                                });
                            }
                        }
                    });
                    if (delay !== null) {
                        if (delay < 0) {
                            this.notifqueue.setSynchronous(notifId);
                        } else {
                            this.notifqueue.setSynchronous(notifId, delay);
                        }
                    }
                }
            },
            onBeforeNotification(notifId, args) { },
            onAfterNotification(notifId, args) { },

            notif_ChangePrivateState(notif) {
                this.setPrivateState(notif.args.stateId, notif.args.stateArgs);
            },

            notif_UndoPrivateState(notif) {
                const stateName = this.gamedatas.gamestates[notif.args.stateId].name;
                const functionName = this.toCamelCase('ON_UNDO_' + stateName);
                if (functionName in this) {
                    debug('notif_UndoPrivateState: ' + stateName + ' (calling ' + functionName + ')');
                    this[functionName]();
                } else {
                    debug('notif_UndoPrivateState: ' + stateName + ' (no function named ' + functionName + ')');
                }
            },
            notif_MultiActiveArgs(notif) {
                const newState = dojo.clone(this.gamedatas.gamestate);
                newState.args = notif.args;
                this.setClientState(this.gamedatas.gamestate['name'], newState);
            },
            notif_UndoBegin(notif) {
                debug('notif_UndoBegin');
                this.onUndoBegin();
            },
            notif_DebugReload(notif) {
                debug('notif_DebugReload');
                window.location.reload();
            },

            setPrivateState(stateId, privateStateArgs) {
                const privateState = this.gamedatas.gamestates[stateId];
                const privateArgs = dojo.clone(privateState);
                // Switch from 'privateState' to 'activeplayer' so that the player is considered active
                privateArgs.type = 'activeplayer';
                if (!privateArgs.descriptionmyturn) {
                    privateArgs.descriptionmyturn = privateArgs.description;
                }
                privateArgs.args = dojo.clone(privateStateArgs);
                delete privateArgs.args.privateStateId;
                privateArgs.id = stateId;
                // Setup for "${you}" parameter (Code copied from updatePageTitle())
                const playerColor = this.gamedatas.players[this.player_id].color;
                let playerBack = ''
                if (this.gamedatas.players[this.player_id].color_back) {
                    playerBack = "background-color:#" + this.gamedatas.players[this.player_id].color_back + ";";
                }
                privateArgs.args['you'] = '<span style="font-weight:bold;color:#' + playerColor + ";" + playerBack + '">' + __("lang_mainsite", "You") + "</span>";
                this.setClientState(privateArgs.name, privateArgs);
            },

            serverAction(action, args, reEnterStateOnError = false) {
                if (!args) {
                    args = [];
                }
                args = dojo.clone(args);
                delete args.action;
                if (!args.hasOwnProperty('lock') || args.lock) {
                    args.lock = true;
                } else {
                    delete args.lock;
                }
                if (args.skipCheckInterfaceLocked !== true) {
                    if (this.isInterfaceLocked()) {
                        const errorMsg = _('Please wait, an action is already in progress');
                        this.showMessage(errorMsg, 'error');
                        return new Promise((resolve, reject) => {
                            reject(errorMsg);
                        })
                    }
                }
                delete args.skipCheckInterfaceLocked;
                // Please wait, an action is already in progress
                const name = this.game_name;
                const promise = new Promise((resolve, reject) => {
                    this.ajaxcall(
                        "/" + name + "/" + name + "/" + action + ".html",
                        args,
                        this,
                        (data) => resolve(data),
                        (isError, msg, code) => {
                            if (isError) {
                                reject(msg, code);
                            }
                        }
                    );
                });

                if (reEnterStateOnError) {
                    promise.catch(() => this.onEnteringState(this.gamedatas.gamestate.name, this.gamedatas.gamestate));
                }

                return promise;
            },
        });
    });