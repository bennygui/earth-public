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
        return declare("ea.PlayerPanelMgr", null, {
            constructor() {
            },

            setup(gamedatas) {
                let firstPlayerId = null;
                let firstPlayerNo = null;
                for (const playerId in gamedatas.players) {
                    if (firstPlayerNo === null || gamedatas.players[playerId].player_no < firstPlayerNo) {
                        firstPlayerId = playerId;
                        firstPlayerNo = gamedatas.players[playerId].player_no;
                    }
                }
                for (const playerId in gamedatas.players) {
                    const playerBoardElem = this.getPlayerBoardElem(playerId);

                    let rowElem = null;
                    for (const info of this.getCounters()) {
                        if (info === null) {
                            rowElem = this.createPlayerPanelRow(playerBoardElem);
                            continue;
                        }
                        if (info[4] == 'abundance' && gameui.isFalse(gamedatas.gameHasExpansionAbundance)) {
                            continue;
                        }
                        const pill = this.createPillElem(info[1]);
                        if (info[3]) {
                            pill.classList.add('ea-pill-tableau-related');
                            if (!gameui.isReadOnly()) {
                                pill.classList.add('bx-hidden');
                            }
                        }
                        if (info[4] == 'abundance') {
                            pill.classList.add('ea-pill-abundance-related');
                        }
                        if (info[5] !== undefined) {
                            pill.style.setProperty('--ea-zoom', info[5]);
                        }
                        gameui.counters[playerId][info[0]].addTarget(pill.querySelector('.ea-pill-counter'));
                        rowElem.appendChild(pill);
                        gameui.addBasicTooltipToElement(pill, info[2]);
                    }

                    const objectiveElem = this.createPillElem('ea-icon-card-type-fauna');
                    objectiveElem.classList.add('ea-objective-button')
                    gameui.counters[playerId]['fauna'].addTarget(objectiveElem.querySelector('.ea-pill-counter'));
                    rowElem.appendChild(objectiveElem);
                    gameui.addBasicTooltipToElement(objectiveElem, _('Number of Fauna in-game scoring objectives achieved'));
                    objectiveElem.addEventListener('click', () => {
                        this.clearAllNewFaunaObjectiveIndicator();
                        gameui.objectiveDetailMgr.show(playerId);
                        if (!gameui.isReadOnly() && playerId in gamedatas.players) {
                            gameui.serverAction('seeFaunaObjective', { lock: false, skipCheckInterfaceLocked: true })
                        }
                    });
                    if (playerId != gameui.player_id && gamedatas.newFaunaObjectivePlayerIds.includes(parseInt(playerId))) {
                        this.setNewFaunaObjectiveIndicator(playerId);
                    }

                    rowElem = this.createPlayerPanelRow(playerBoardElem);
                    rowElem.classList.add('ea-player-panel-row-tableau');

                    const tableauOverviewElem = document.createElement('div');
                    tableauOverviewElem.classList.add('ea-player-panel-tableau-overview');
                    rowElem.appendChild(tableauOverviewElem);
                    gameui.addBasicTooltipToElement(tableauOverviewElem, _('Displays color of cards in tableau'));

                    const firstPlayerElem = document.createElement('div');
                    firstPlayerElem.classList.add('ea-token-first-player');
                    if (playerId != firstPlayerId) {
                        firstPlayerElem.classList.add('bx-invisible');
                    }
                    rowElem.appendChild(firstPlayerElem);
                    gameui.addBasicTooltipToElement(firstPlayerElem, _('First player token'));
                    if (gameui.isGameSolo()) {
                        firstPlayerElem.classList.add('bx-hidden');
                    }

                    const activePlayerContainerElem = document.createElement('div');
                    activePlayerContainerElem.classList.add('ea-token-active-player-container');
                    rowElem.appendChild(activePlayerContainerElem);
                    if (playerId in gamedatas.playerActiveOrder && gamedatas.playerActiveOrder[playerId] == 0) {
                        this.moveActivePlayerToken(playerId, true);
                    }
                    if (gameui.isGameSolo()) {
                        activePlayerContainerElem.classList.add('bx-transparent');
                    }

                    const islandClimateOverviewElem = document.createElement('div');
                    islandClimateOverviewElem.classList.add('ea-player-panel-island-climate-overview');
                    rowElem.appendChild(islandClimateOverviewElem);
                    gameui.addBasicTooltipToElement(islandClimateOverviewElem, _('Displays color of Island and Climate cards'));
                }

                // Display left and right icon on left and right players
                if (
                    gameui.player_id in gameui.gamedatas.players
                    && gameui.isTrue(gamedatas.gameHasExpansionAbundance)
                ) {
                    let playerIds = gameui.getAllPlayerIds();
                    if (playerIds.length > 2) {
                        const addIcon = (otherPlayerId, isLeft) => {
                            const lastPill = gameui.getPlayerPanelBoardElem(otherPlayerId).querySelector('.ea-pill-abundance-related:last-child');
                            const newPill = this.createPillElem('ea-icon-player-left');
                            newPill.querySelector('.ea-pill-counter').remove();
                            newPill.style.setProperty('--ea-zoom', 0.5);
                            if (!isLeft) {
                                newPill.style.transform = 'scaleX(-1)';
                            }
                            lastPill.parentElement.appendChild(newPill);
                            gameui.addBasicTooltipToElement(
                                newPill,
                                _('Indicates that this player is on your left or right for some card abilities.')
                            );
                        };
                        playerIds = gameui.rotateValueToFront(playerIds, gameui.player_id);
                        addIcon(playerIds[1], true);
                        addIcon(playerIds[playerIds.length - 1], false);
                    }
                }

                this.setIsGaiaTurn(gamedatas.isGaiaTurn);
                this.setupGaiaPanel();
                this.setupOptionPanel();
                this.setupScrollShortcuts();
            },

            getCounters() {
                return [
                    null,
                    ['hand', 'ea-icon-draw-from-deck', _('Number of cards in hand'), false],
                    ['soil', 'ea-icon-soil', _('Number of soil available'), false],
                    ['compost', 'ea-icon-compost-destroy', _('Number of cards in compost'), false],
                    ['event', 'ea-icon-card-type-event', _('Number of played event cards'), false],
                    ['sprout', 'ea-icon-sprout', _('Number of placed sprouts / Number of sprouts spots in tableau'), true],
                    ['growth', 'ea-icon-growth', _('Number of placed growth / Number of growth spots in tableau'), true],
                    null,
                    ['seed', 'ea-icon-seed', _('Number seeds'), false, 'abundance'],
                    ['exchangeSprout', 'ea-icon-sprout-additional', _('Number of Abundance Sprouts on Player Board'), false, 'abundance'],
                    ['abilityNeighbour', 'ea-icon-player-both', _('Number of cards with Neighbour abilities (not black) in tableau'), false, 'abundance', 0.35],
                    null,
                    ['cardTree', 'ea-icon-card-type-tree', _('Number of Tree cards in tableau'), true],
                    ['cardHerb', 'ea-icon-card-type-herb', _('Number of Herb cards in tableau'), true],
                    ['cardMushroom', 'ea-icon-card-type-mushroom', _('Number of Mushroom cards in tableau'), true],
                    ['cardBush', 'ea-icon-card-type-bush', _('Number of Bush cards in tableau'), true],
                    ['cardJoker', 'ea-icon-card-type-joker', _('Number of cards with more than one type in tableau'), true],
                    ['cardTerrain', 'ea-icon-card-type-terrain', _('Number of Terrain cards in tableau'), true],
                    null,
                    ['habitatSunny', 'ea-icon-habitat-sunny', _('Number of Sunny habitat on tableau, island and climate cards'), true],
                    ['habitatWet', 'ea-icon-habitat-wet', _('Number of Wet habitat on tableau, island and climate cards'), true],
                    ['habitatRocky', 'ea-icon-habitat-rocky', _('Number of Rocky habitat on tableau, island and climate cards'), true],
                    ['habitatCold', 'ea-icon-habitat-cold', _('Number of Cold habitat on tableau, island and climate cards'), true],
                ];
            },

            setupOptionPanel() {
                const optionPanel = gameui.addPlayerPanel();
                optionPanel.classList.add('ea-option-panel');
                let rowElem = null;

                // Welcome message
                rowElem = this.createPlayerPanelRow(optionPanel);
                rowElem.appendChild(gameui.createCheckboxSwitch('ea-welcome-checkbox', _('Welcome message:')));
                const welcomeCheckbox = document.getElementById('ea-welcome-checkbox');
                welcomeCheckbox.addEventListener('change', () => {
                    gameui.setLocalPreference(gameui.EA_PREF_WELCOME_ID, welcomeCheckbox.checked);
                });
                gameui.addBasicTooltipToElement(rowElem, _('Display a welcome message when the page is loaded'));


                // Cards help
                rowElem = this.createPlayerPanelRow(optionPanel);
                rowElem.appendChild(gameui.createCheckboxSwitch('ea-card-help-checkbox', _('Card help / flag:')));
                const cardHelpCheckbox = document.getElementById('ea-card-help-checkbox');
                cardHelpCheckbox.addEventListener('change', () => {
                    gameui.setLocalPreference(gameui.EA_PREF_CARD_HELP_ID, cardHelpCheckbox.checked);
                });
                gameui.addBasicTooltipToElement(rowElem, _('Display Card Help button under cards and also display Card Flags under cards in your hand'));

                // Shortcuts
                rowElem = this.createPlayerPanelRow(optionPanel);
                rowElem.appendChild(gameui.createCheckboxSwitch('ea-shortcuts-checkbox', _('Shortcuts')));
                const shortcutsCheckbox = document.getElementById('ea-shortcuts-checkbox');
                shortcutsCheckbox.addEventListener('change', () => {
                    gameui.setLocalPreference(gameui.EA_PREF_SHORTCUTS_ID, shortcutsCheckbox.checked);
                });
                gameui.addBasicTooltipToElement(rowElem, _('Display a planel of shortcuts to navigate the page'));

                // Dark background
                rowElem = this.createPlayerPanelRow(optionPanel);
                rowElem.appendChild(gameui.createCheckboxSwitch('ea-dark-background-checkbox', _('Dark background:')));
                const darkBackgroundCheckbox = document.getElementById('ea-dark-background-checkbox');
                darkBackgroundCheckbox.addEventListener('change', () => {
                    gameui.setLocalPreference(gameui.EA_PREF_DARK_BACKGROUND_ID, darkBackgroundCheckbox.checked);
                });
                gameui.addBasicTooltipToElement(rowElem, _('Display a dark background or the classic background'));

                // Zoom tableau and player board
                rowElem = this.createPlayerPanelRow(optionPanel);
                rowElem.innerText = _('Tableau/Board Zoom');
                rowElem = this.createPlayerPanelRow(optionPanel);
                const tableauSliderElem = this.createZoomSlider('ea-tableau-slider');
                tableauSliderElem.addEventListener('input', () => {
                    gameui.setLocalPreference(gameui.EA_PREF_TABLEAU_ZOOM_FACTOR_ID, parseInt(tableauSliderElem.value));
                });
                rowElem.appendChild(tableauSliderElem)
                gameui.addBasicTooltipToElement(rowElem, _('Change the size of tableau cards and player boards'));

                // Zoom Fauna board
                rowElem = this.createPlayerPanelRow(optionPanel);
                rowElem.innerText = _('Fauna Board Zoom');
                rowElem = this.createPlayerPanelRow(optionPanel);
                const faunaSliderElem = this.createZoomSlider('ea-fauna-slider');
                faunaSliderElem.addEventListener('input', () => {
                    gameui.setLocalPreference(gameui.EA_PREF_FAUNA_ZOOM_FACTOR_ID, parseInt(faunaSliderElem.value));
                });
                rowElem.appendChild(faunaSliderElem)
                gameui.addBasicTooltipToElement(rowElem, _('Change the size of the Fauna board with Fauna and Ecosystem cards'));

                // Compact cards
                rowElem = this.createPlayerPanelRow(optionPanel);
                rowElem.appendChild(gameui.createCheckboxSwitch('ea-compact-card-checkbox', _('Compact cards (reload):')));
                const compactCheckbox = document.getElementById('ea-compact-card-checkbox');
                compactCheckbox.addEventListener('change', () => {
                    gameui.setLocalPreference(gameui.EA_PREF_COMPACT_CARD_ID, compactCheckbox.checked);
                    gameui.showMessage(_('Done, reload in progress...'), 'info');
                    window.location.reload();
                });
                gameui.addBasicTooltipToElement(rowElem, _('Display cards in a compact way. This cuts parts of the cards that are not required for gameplay. Requires a page reload to be applied.'));

                // Fast notification
                rowElem = this.createPlayerPanelRow(optionPanel);
                rowElem.appendChild(gameui.createCheckboxSwitch('ea-fast-notif-checkbox', _('Reduce movement conflicts:')));
                const fastNotifCheckbox = document.getElementById('ea-fast-notif-checkbox');
                fastNotifCheckbox.addEventListener('change', () => {
                    gameui.setLocalPreference(gameui.EA_PREF_FAST_NOTIF_ID, fastNotifCheckbox.checked);
                });
                gameui.addBasicTooltipToElement(rowElem, _('Cards and resources movements for other players are faster to reduce conflict when your are playing.'));
            },

            setupGaiaPanel() {
                if (!gameui.isGameSolo()) {
                    return;
                }
                const gaiaPanel = gameui.addPlayerPanel();
                const GAIA_COUNTERS = [
                    null,
                    ['gaiaSoil', 'ea-icon-soil', _('Number of soil')],
                    ['gaiaCompost', 'ea-icon-compost-destroy', _('Number of cards in compost')],
                    ['gaiaSprout', 'ea-icon-sprout', _('Number of placed sprouts')],
                    ['gaiaGrowth', 'ea-icon-growth', _('Number of placed growth')],
                    ['gaiaTableau', 'ea-icon-gaia-tableau', _('Number of Earth cards')],
                    null,
                    ['gaiaFauna', 'ea-icon-card-type-fauna', _('Number of Fauna in-game scoring objectives achieved')],
                    ['gaiaRound', 'ea-icon-card-type-gaia', _('Number of played Gaia cards / Number of rounds')],
                ];

                let rowElem = this.createPlayerPanelRow(gaiaPanel);
                const nameElem = document.createElement('div');
                nameElem.classList.add('player-name');
                nameElem.innerText = _('Gaia');
                rowElem.appendChild(nameElem);

                for (const info of GAIA_COUNTERS) {
                    if (info === null) {
                        rowElem = this.createPlayerPanelRow(gaiaPanel);
                        continue;
                    }
                    const pill = this.createPillElem(info[1]);
                    gameui.counters[info[0]].addTarget(pill.querySelector('.ea-pill-counter'));
                    rowElem.appendChild(pill);
                    gameui.addBasicTooltipToElement(pill, info[2]);
                }
            },

            setupScrollShortcuts() {
                const cardHandElem = gameui.handMgr.getCardHandElement();
                cardHandElem.style.paddingLeft = null;
                const areaElem = document.getElementById('ea-shortcut-area');
                if (gameui.getLocalPreference(gameui.EA_PREF_SHORTCUTS_ID)) {
                    document.body.classList.remove('ea-shortcuts-hidden');
                    if (gameui.getLocalPreference(gameui.EA_PREF_HAND_PLACEMENT_ID) != gameui.EA_PREF_HAND_PLACEMENT_VALUE_FIXED) {
                        cardHandElem.style.paddingLeft = areaElem.offsetWidth + 'px';
                    }
                } else {
                    document.body.classList.add('ea-shortcuts-hidden');

                }
                areaElem.innerHTML = '';
                areaElem.appendChild(this.createScrollShortcut(
                    _('Top'),
                    document.body
                ));

                let searchPlayerId = null;
                if (gameui.player_id in gameui.gamedatas.players) {
                    searchPlayerId = gameui.player_id;
                    if (gameui.getLocalPreference(gameui.EA_PREF_HAND_PLACEMENT_ID) != gameui.EA_PREF_HAND_PLACEMENT_VALUE_FIXED) {
                        areaElem.appendChild(this.createScrollShortcut(
                            _('Hand'),
                            document.getElementById('ea-area-card-hand-container')
                        ));
                    }
                    areaElem.appendChild(this.createScrollShortcut(
                        _('Tableau'),
                        document.querySelector('#ea-area-player-' + searchPlayerId + ' .ea-area-player-tableau-container'),
                        document.querySelector('#ea-area-player-' + searchPlayerId + ' .ea-area-player-board')
                    ));
                    areaElem.appendChild(this.createScrollShortcut(
                        _('Board'),
                        document.querySelector('#ea-area-player-' + searchPlayerId + ' .ea-area-player-board')
                    ));
                }
                areaElem.appendChild(this.createScrollShortcut(
                    _('Fauna'),
                    document.getElementById('ea-area-fauna-board')
                ));
                if (gameui.isGameSolo()) {
                    areaElem.appendChild(this.createScrollShortcut(
                        _('Gaia'),
                        document.getElementById('ea-area-gaia-board')
                    ));
                }
                for (const playerId in gameui.gamedatas.players) {
                    if (playerId == searchPlayerId) {
                        continue;
                    }
                    areaElem.appendChild(this.createScrollShortcut(
                        gameui.gamedatas.players[playerId].player_name,
                        document.getElementById('ea-area-player-' + playerId),
                        null,
                        true
                    ));
                }

                if (this.shortcutsScrollListener) {
                    dojo.disconnect(this.shortcutsScrollListener);
                }
                this.shortcutsScrollListener = dojo.connect(window, 'scroll', () => {
                    const shortcutRect = areaElem.getBoundingClientRect();

                    const pageContentElem = document.getElementById('page-content');
                    const pageContentRect = pageContentElem.getBoundingClientRect();
                    if (shortcutRect.bottom < pageContentRect.bottom) {
                        areaElem.classList.remove('bx-invisible');
                    } else {
                        areaElem.classList.add('bx-invisible');
                    }
                });
            },

            createScrollShortcut(title, scrollToElem, fallbackScrollToElem = null, isPlayer = false) {
                const elem = document.createElement('div');
                if (isPlayer) {
                    elem.classList.add('ea-shortcut-is-player');
                }
                elem.addEventListener('click', () => {
                    scrollToElem.scrollIntoView();
                    if (fallbackScrollToElem !== null && scrollToElem.classList.contains('bx-hidden')) {
                        fallbackScrollToElem.scrollIntoView();
                    }
                    window.scrollBy(0, -1 * document.getElementById('page-title').offsetHeight);
                });
                elem.innerText = title;
                return elem;
            },

            createZoomSlider(id) {
                const sliderElem = document.createElement('input');
                sliderElem.id = id;
                sliderElem.type = 'range';
                sliderElem.min = 25;
                sliderElem.max = 100;
                sliderElem.step = 5;
                sliderElem.value = 35;
                return sliderElem;
            },

            createPlayerPanelRow(parentElem) {
                const rowElem = document.createElement('div');
                rowElem.classList.add('ea-player-panel-row');
                parentElem.appendChild(rowElem);
                return rowElem;
            },

            moveActivePlayerToken(playerId, isInstantaneous = false) {
                let elem = document.querySelector('#player_boards .ea-token-active-player');
                if (elem === null) {
                    elem = document.createElement('div');
                    elem.classList.add('ea-token-active-player');
                    gameui.getElementCreationElement().appendChild(elem);
                    gameui.addBasicTooltipToElement(elem, _('Active player token'));
                }
                const targetElem = this.getPlayerBoardElem(playerId).querySelector('.ea-token-active-player-container');
                return gameui.slide(elem, targetElem, {
                    phantom: true,
                    isInstantaneous: isInstantaneous,
                });
            },

            setIsGaiaTurn(isGaiaTurn) {
                if (!gameui.isGameSolo()) {
                    return;
                }
                for (const elem of document.querySelectorAll('#player_boards .ea-token-active-player-container')) {
                    if (isGaiaTurn) {
                        elem.classList.add('bx-transparent');
                    } else {
                        elem.classList.remove('bx-transparent');
                    }
                }
            },

            getPlayerBoardElem(playerId) {
                return document.getElementById('player_board_' + playerId);
            },

            setNewFaunaObjectiveIndicator(playerId) {
                const boardElem = this.getPlayerBoardElem(playerId);
                if (boardElem === null) {
                    return;
                }
                const pillElem = boardElem.querySelector('.ea-objective-button');
                if (pillElem === null) {
                    return;
                }
                pillElem.classList.add('ea-pill-shake');
            },

            clearAllNewFaunaObjectiveIndicator() {
                const allPillsElem = document.querySelectorAll('.ea-objective-button');
                for (const pillElem of allPillsElem) {
                    pillElem.classList.remove('ea-pill-shake');
                }
            },

            showTableauRelatedPills() {
                const allPillsElem = document.querySelectorAll('.ea-pill-tableau-related');
                for (const pillElem of allPillsElem) {
                    pillElem.classList.remove('bx-hidden');
                }
            },

            createPillElem(iconClass) {
                const pillElem = document.createElement('div');
                pillElem.classList.add('ea-player-panel-pill')

                const imgElem = document.createElement('div');
                imgElem.classList.add(iconClass)

                const countElem = document.createElement('div');
                countElem.classList.add('ea-pill-counter')
                countElem.innerText = '0';

                pillElem.appendChild(imgElem);
                pillElem.appendChild(countElem);
                return pillElem;
            },
        });
    });