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
            GERMINATE_SOIL_3_OR_LESS: 1,
            GERMINATE_SOIL_4_OR_MORE: 2,
            GERMINATE_SCORE_3_OR_LESS: 3,
            GERMINATE_SCORE_4_OR_MORE: 4,
            GERMINATE_SCORE_EVEN: 5,
            GERMINATE_SCORE_ODD: 6,
            GERMINATE_SPROUT_SPACE_3_OR_LESS: 7,
            GERMINATE_SPROUT_SPACE_EXACTLY_6: 8,
            GERMINATE_HABITAT_SUNNY: 9,
            GERMINATE_HABITAT_WET: 10,
            GERMINATE_HABITAT_ROCKY: 11,
            GERMINATE_HABITAT_COLD: 12,
            GERMINATE_CARD_TYPE_TREE: 13,
            GERMINATE_CARD_TYPE_HERB: 14,
            GERMINATE_CARD_TYPE_MUSHROOM: 15,
            GERMINATE_CARD_TYPE_BUSH: 16,
            GERMINATE_CARD_TYPE_TERRAIN: 17,
            GERMINATE_CARD_TYPE_EVENT: 18,
            GERMINATE_HABITAT_1_OR_LESS: 19,
            GERMINATE_HABITAT_2_OR_MORE: 20,
            GERMINATE_GROWTH_SCORE_4_OR_LESS: 21,
            GERMINATE_GROWTH_SCORE_5_OR_MORE: 22,
            GERMINATE_GROWTH_CAPACITY_2_OR_LESS: 23,
            GERMINATE_GROWTH_CAPACITY_4_OR_MORE: 24,
            GERMINATE_ABILITY_COLOR_RED: 25,
            GERMINATE_ABILITY_COLOR_YELLOW: 26,
            GERMINATE_ABILITY_COLOR_BLUE: 27,
            GERMINATE_ABILITY_COLOR_MULTICOLOR: 28,
            GERMINATE_ABILITY_COLOR_GREEN: 29,
            GERMINATE_ABILITY_COLOR_BROWN: 30,
            GERMINATE_ABILITY_COLOR_BLACK: 31,
            GERMINATE_ABILITY_2: 32,
            GERMINATE_DIRECTIONAL_AID: 33,
            GERMINATE_CARD_NAME_IS_BOLD: 34,
            GERMINATE_CARD_NAME_IS_ITALIC: 35,
            GERMINATE_CARD_NAME_IS_UNDERLINE: 36,
            GERMINATE_CARD_ABILITY_ICON_GROWTH: 37,
            GERMINATE_CARD_ABILITY_ICON_SPROUT: 38,
            GERMINATE_CARD_ABILITY_ICON_SOIL: 39,
            GERMINATE_CARD_ABILITY_ICON_COMPOST: 40,
            GERMINATE_CARD_ABILITY_ICON_DRAW: 41,
            GERMINATE_CARD_ABILITY_ICON_COLON: 42,

            addTopPlayConversionButton(args) {
                let canPlayConversion = false;
                if (args && args.args && args.args.canPlayConversion !== undefined && args.args.canPlayConversion !== null) {
                    canPlayConversion = args.args.canPlayConversion;
                } else if (args && args.args && args.args._private && args.args._private.canPlayConversion !== undefined && args.args._private.canPlayConversion !== null) {
                    canPlayConversion = args.args._private.canPlayConversion;
                }
                if (canPlayConversion) {
                    this.addTopButtonSecondary(
                        'ea-button-play-conversion',
                        this.format_string_recursive(
                            _('-3 ${sproutIcon} : +2 ${soilIcon}'),
                            { sproutIcon: '', soilIcon: '', }
                        ),
                        () => this.serverAction('convertPlay')
                    );
                    const boxElem = this.playerBoardMgr.getPlayerIdConversionBoxElem(this.player_id);
                    if (boxElem !== null) {
                        this.addClickable(boxElem, () => this.serverAction('convertPlay'));
                    }
                }

                let canUseSeed = false;
                if (args && args.args && args.args.canUseSeed !== undefined && args.args.canUseSeed !== null) {
                    canUseSeed = args.args.canUseSeed;
                } else if (args && args.args && args.args._private && args.args._private.canUseSeed !== undefined && args.args._private.canUseSeed !== null) {
                    canUseSeed = args.args._private.canUseSeed;
                }
                if (canUseSeed && args && args.possibleactions && args.possibleactions.indexOf('convertUseSeed') < 0) {
                    canUseSeed = false;
                }
                if (canUseSeed) {
                    this.addTopButtonSecondary(
                        'ea-button-use-seed',
                        this.format_string_recursive(
                            _('Use ${seedIcon}'),
                            { seedIcon: _('Seed') }
                        ),
                        () => this.serverAction('convertUseSeed')
                    );
                    const boxElem = this.playerBoardMgr.getPlayerIdUseSeedBoxElem(this.player_id);
                    if (boxElem !== null) {
                        this.addClickable(boxElem, () => this.serverAction('convertUseSeed'));
                    }
                }

                let canCreateSeed = false;
                if (args && args.args && args.args.canCreateSeed !== undefined && args.args.canCreateSeed !== null) {
                    canCreateSeed = args.args.canCreateSeed;
                } else if (args && args.args && args.args._private && args.args._private.canCreateSeed !== undefined && args.args._private.canCreateSeed !== null) {
                    canCreateSeed = args.args._private.canCreateSeed;
                }
                if (canCreateSeed && args && args.possibleactions && args.possibleactions.indexOf('convertCreateSeed') < 0) {
                    canCreateSeed = false;
                }
                if (canCreateSeed) {
                    this.addTopButtonSecondary(
                        'ea-button-create-seed',
                        this.format_string_recursive(
                            _('Create ${seedIcon}'),
                            { seedIcon: _('Seed') }
                        ),
                        () => this.serverAction('convertCreateSeed')
                    );
                    const boxElem = this.playerBoardMgr.getPlayerIdCreateSeedBoxElem(this.player_id);
                    if (boxElem !== null) {
                        this.addClickable(boxElem, () => this.serverAction('convertCreateSeed'));
                    }
                }
            },

            onStateConvertSelectPayment(args) {
                debug('onStateConvertSelectPayment');
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
                this.paymentMgr.addSprout(args.args.sproutCards, args.args.sproutCount);
            },

            onStateConvertSelectUseSeed(args) {
                debug('onStateConvertSelectUseSeed');
                debug(args);
                const actions = [
                    {
                        id: 'germinate',
                        count: 1,
                        abilityId: this.ABILITY_GERMINATE,
                    },
                    {
                        id: 'soil',
                        count: 2,
                        abilityId: this.ABILITY_SOIL,
                    },
                    {
                        id: 'growth',
                        count: 2,
                        abilityId: this.ABILITY_GROWTH,
                    },
                    {
                        id: 'sprout',
                        count: 3,
                        abilityId: this.ABILITY_SPROUT,
                    },
                    {
                        id: 'compostFromDeck',
                        count: 3,
                        abilityId: this.ABILITY_COMPOST_FROM_DECK,
                    },
                    {
                        id: 'compostFromHand',
                        count: 4,
                        abilityId: this.ABILITY_COMPOST_FROM_HAND,
                    },
                ];
                for (const action of actions) {
                    this.addTopButtonPrimary(
                        'ea-button-' + action.id,
                        this.format_string_recursive(
                            '+${count} ${' + action.id + 'Icon}',
                            {
                                count: action.count,
                                germinateIcon: '',
                                soilIcon: '',
                                growthIcon: '',
                                sproutIcon: '',
                                compostFromDeckIcon: '',
                                compostFromHandIcon: '',
                            }
                        ),
                        () => {
                            if (action.abilityId == this.ABILITY_GERMINATE) {
                                const dialog = new bx.ModalDialog('ea-germinate-dialog', {
                                    title: _('Germinate'),
                                    contents: '',
                                    closeWhenClickAnywhere: true,
                                    onShow: () => {
                                        const containerElem = document.getElementById('popin_ea-germinate-dialog_contents');
                                        containerElem.innerHTML = '<div class="ea-germinate-text"></div><div class="ea-germinate-buttons"></div>';
                                        containerElem.firstChild.innerHTML = _('Choose an objective to draw the first matching card from the deck. This cannot be undone.');
                                        this.createGerminateButtons(containerElem.lastChild);
                                    },
                                });
                                dialog.show();
                            } else {
                                this.serverAction('convertSelectUseSeed', { abilityId: action.abilityId });
                            }
                        }
                    );
                }
            },

            onStateConvertSelectUseSeedGain(args) {
                debug('onStateConvertSelectUseSeedGain');
                debug(args);
                this.onAbilityGain('convertSelectUseSeedGain', args.args);
            },

            onStateConvertSelectCreateSeed(args) {
                debug('onStateConvertSelectCreateSeed');
                debug(args);

                let resetSelection = () => { };
                if (args.args.leafIds.length > 0) {
                    resetSelection = this.addTopButtonSelection(
                        this.format_string_recursive(
                            _('Convert 1 ${leafIcon} to 1 ${seedIcon}'),
                            {
                                leafIcon: '',
                                seedIcon: '',
                            }
                        ),
                        _('You must select a Leaf on your Player Board or a Leaf without a final position on the Fauna Board'),
                        {
                            ids: args.args.leafIds,
                            onElement: (tokenId) => this.leafTokenMgr.getLeafElementByTokenId(tokenId),
                            onClick: (tokenId) => {
                                let confirm = Promise.resolve();
                                if (this.isTrue(args.args.leafIdsOnFaunaBoard[tokenId])) {
                                    confirm = gameui.showConfirmDialog(_('You will convert a Leaf that could score on the Fauna board, are you sure?'));
                                }
                                confirm.then(() => this.serverAction('convertSelectCreateSeedFromLeaf', { tokenId: tokenId }));
                            },
                        }
                    );
                }

                const BUTTON_CONVERT_ID = 'button-create-seed-from-sprouts';
                const updateButton = () => {
                    const count = this.paymentMgr.sproutCount();
                    const buttonElem = document.getElementById(BUTTON_CONVERT_ID);
                    buttonElem.innerHTML = this.format_string_recursive(
                        _('Convert ${sproutCount} ${sproutIcon} to ${seedCount} ${seedIcon}'),
                        {
                            sproutCount: count,
                            sproutIcon: _('sprout(s)'),
                            seedCount: Math.floor(count / 4),
                            seedIcon: _('seed'),
                        },
                    );
                };
                this.addTopButtonPrimaryWithValid(
                    BUTTON_CONVERT_ID,
                    '',
                    _('You must select a multiple of 4 sprouts (4, 8, 12, ...)'),
                    () => {
                        const payedSproutList = this.paymentMgr.getPayedSproutList().join(',');
                        this.paymentMgr.pause();
                        this.serverAction('convertSelectCreateSeedFromSprouts', {
                            payedSproutList: payedSproutList,
                            payedGrowthList: '',
                            payedCompostFromHandCardIds: '',
                        })
                            .catch(() => this.paymentMgr.resume());
                    });

                this.addTopButtonSecondary(
                    'button-convert-reset',
                    _('Reset'),
                    () => {
                        this.paymentMgr.resetPayment();
                        resetSelection();
                    }
                );

                this.paymentMgr.startPayment(() => {
                    const count = this.paymentMgr.sproutCount();
                    gameui.setTopButtonValid(BUTTON_CONVERT_ID, count > 0 && (count % 4) == 0);
                    updateButton();
                });
                this.paymentMgr.addSprout(args.args.sproutCards, args.args.sproutCount);
            },

            createGerminateButtons(containerElem) {
                const actions = [
                    {
                        id: this.GERMINATE_SOIL_3_OR_LESS,
                        title: _('3- ${soilIcon}'),
                    },
                    {
                        id: this.GERMINATE_SOIL_4_OR_MORE,
                        title: _('4+ ${soilIcon}'),
                    },
                    {
                        id: this.GERMINATE_SCORE_3_OR_LESS,
                        title: _('3- ${scoringIcon}'),
                    },
                    {
                        id: this.GERMINATE_SCORE_4_OR_MORE,
                        title: _('4+ ${scoringIcon}'),
                    },
                    {
                        id: this.GERMINATE_SCORE_EVEN,
                        title: _('Even ${scoringIcon}'),
                    },
                    {
                        id: this.GERMINATE_SCORE_ODD,
                        title: _('Odd ${scoringIcon}'),
                    },
                    {
                        id: this.GERMINATE_SPROUT_SPACE_3_OR_LESS,
                        title: _('3- ${sproutIcon} Spaces'),
                    },
                    {
                        id: this.GERMINATE_SPROUT_SPACE_EXACTLY_6,
                        title: _('6 ${sproutIcon} Spaces'),
                    },
                    {
                        id: this.GERMINATE_HABITAT_SUNNY,
                        icon: 'habitat-sunny',
                    },
                    {
                        id: this.GERMINATE_HABITAT_WET,
                        icon: 'habitat-wet',
                    },
                    {
                        id: this.GERMINATE_HABITAT_ROCKY,
                        icon: 'habitat-rocky',
                    },
                    {
                        id: this.GERMINATE_HABITAT_COLD,
                        icon: 'habitat-cold',
                    },
                    {
                        id: this.GERMINATE_CARD_TYPE_TREE,
                        icon: 'card-type-tree',
                    },
                    {
                        id: this.GERMINATE_CARD_TYPE_HERB,
                        icon: 'card-type-herb',
                    },
                    {
                        id: this.GERMINATE_CARD_TYPE_MUSHROOM,
                        icon: 'card-type-mushroom',
                    },
                    {
                        id: this.GERMINATE_CARD_TYPE_BUSH,
                        icon: 'card-type-bush',
                    },
                    {
                        id: this.GERMINATE_CARD_TYPE_TERRAIN,
                        icon: 'card-type-terrain',
                    },
                    {
                        id: this.GERMINATE_CARD_TYPE_EVENT,
                        icon: 'card-type-event',
                    },
                    {
                        id: this.GERMINATE_HABITAT_1_OR_LESS,
                        title: _('1- Habitats'),
                    },
                    {
                        id: this.GERMINATE_HABITAT_2_OR_MORE,
                        title: _('2+ Habitats'),
                    },
                    {
                        id: this.GERMINATE_GROWTH_SCORE_4_OR_LESS,
                        title: _('${growthIcon} score 4- ${leafIcon}'),
                    },
                    {
                        id: this.GERMINATE_GROWTH_SCORE_5_OR_MORE,
                        title: _('${growthIcon} score 5+ ${leafIcon}'),
                    },
                    {
                        id: this.GERMINATE_GROWTH_CAPACITY_2_OR_LESS,
                        title: _('2- ${growthIcon} capacity'),
                    },
                    {
                        id: this.GERMINATE_GROWTH_CAPACITY_4_OR_MORE,
                        title: _('4+ ${growthIcon} capacity'),
                    },
                    {
                        id: this.GERMINATE_ABILITY_COLOR_RED,
                        color: this.AB_COLOR_RED,
                        text: _('Red'),
                    },
                    {
                        id: this.GERMINATE_ABILITY_COLOR_YELLOW,
                        color: this.AB_COLOR_YELLOW,
                        text: _('Yellow'),
                    },
                    {
                        id: this.GERMINATE_ABILITY_COLOR_BLUE,
                        color: this.AB_COLOR_BLUE,
                        text: _('Blue'),
                    },
                    {
                        id: this.GERMINATE_ABILITY_COLOR_MULTICOLOR,
                        title: _('Multicolor'),
                    },
                    {
                        id: this.GERMINATE_ABILITY_COLOR_GREEN,
                        color: this.AB_COLOR_GREEN,
                        text: _('Green'),
                    },
                    {
                        id: this.GERMINATE_ABILITY_COLOR_BROWN,
                        color: this.AB_COLOR_BROWN,
                        text: _('Brown'),
                    },
                    {
                        id: this.GERMINATE_ABILITY_COLOR_BLACK,
                        color: this.AB_COLOR_BLACK,
                        text: _('Black'),
                    },
                    {
                        id: this.GERMINATE_ABILITY_2,
                        title: _('2 abilities'),
                    },
                    {
                        id: this.GERMINATE_DIRECTIONAL_AID,
                        title: _('Directional aids'),
                    },
                    {
                        id: this.GERMINATE_CARD_NAME_IS_BOLD,
                        title: '<b>' + _('Bold') + '</b>',
                    },
                    {
                        id: this.GERMINATE_CARD_NAME_IS_ITALIC,
                        title: '<i>' + _('Italic') + '</i>',
                    },
                    {
                        id: this.GERMINATE_CARD_NAME_IS_UNDERLINE,
                        title: '<u>' + _('Underline') + '</u>',
                    },
                    {
                        id: this.GERMINATE_CARD_ABILITY_ICON_GROWTH,
                        title: _('Icon: ${growthIcon}'),
                    },
                    {
                        id: this.GERMINATE_CARD_ABILITY_ICON_SPROUT,
                        title: _('Icon: ${sproutIcon}'),
                    },
                    {
                        id: this.GERMINATE_CARD_ABILITY_ICON_SOIL,
                        title: _('Icon: ${soilIcon}'),
                    },
                    {
                        id: this.GERMINATE_CARD_ABILITY_ICON_COMPOST,
                        title: _('Icon: ${compostIcon}'),
                    },
                    {
                        id: this.GERMINATE_CARD_ABILITY_ICON_DRAW,
                        title: _('Icon: ${drawFromDeckIcon}'),
                    },
                    {
                        id: this.GERMINATE_CARD_ABILITY_ICON_COLON,
                        title: _('Icon: : (colon)'),
                    },
                ];

                for (const action of actions) {
                    const button = document.createElement('button');
                    button.classList.add('action-button', 'bgabutton', 'bgabutton_blue');
                    button.addEventListener('click', () => {
                        this.serverAction('convertSelectUseSeedGerminate', { germinateId: action.id });
                    });
                    if (action.title) {
                        button.innerHTML = this.format_string_recursive(
                            action.title,
                            {
                                soilIcon: '',
                                scoringIcon: '',
                                sproutIcon: '',
                                growthIcon: '',
                                leafIcon: '',
                                drawFromDeckIcon: '',
                                compostIcon: '',
                            }
                        );
                    } else if (action.icon) {
                        button.innerHTML = '<div class="ea-icon-' + action.icon + '"></div>';
                    } else if (action.color) {
                        button.innerHTML = '<div class="ea-ability-color ea-ability-color-' + action.color + '">' + action.text + '</div>'
                    } else {
                        debug('BUG! geminate does not have a text');
                    }

                    containerElem.appendChild(button);
                }
            },
        });
    });