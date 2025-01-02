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
    g_gamethemeurl + "modules/BX/js/Numbers.js",
],
    function (dojo, declare) {
        return declare("ea.ScoreMgr", null, {
            SCORE_TIME_PER_PLAYER: 4000,

            setup(gamedatas) {
                this.scorepad = null;
                this.activateScorepad(gamedatas.scorepad, gamedatas.gameHasEnded, true);
            },

            getPlayerEcosystemProgressAndScore(playerId) {
                const pad = this.getPlayerPad(playerId);
                if (pad == null) {
                    return '';
                }
                return this.getProgressAndScore(pad.progressPlayerEcosystem, pad.scorePlayerEcosystem);
            },

            getPublicEcosystem1ProgressAndScore(playerId) {
                const pad = this.getPlayerPad(playerId);
                if (pad == null) {
                    return '';
                }
                return this.getProgressAndScore(pad.progressPublicEcosystem1, pad.scorePublicEcosystem1);
            },

            getPublicEcosystem2ProgressAndScore(playerId) {
                const pad = this.getPlayerPad(playerId);
                if (pad == null) {
                    return '';
                }
                return this.getProgressAndScore(pad.progressPublicEcosystem2, pad.scorePublicEcosystem2);
            },

            getTerrainScore(cardId) {
                if (this.scorepad == null) {
                    return null;
                }
                for (const pad of this.scorepad) {
                    if (cardId in pad.scoreTerrainPerCardId) {
                        return pad.scoreTerrainPerCardId[cardId];
                    }
                }
                return null;
            },

            getPlayerPad(playerId) {
                if (this.scorepad == null) {
                    return null;
                }
                for (const pad of this.scorepad) {
                    if (pad.playerId == playerId) {
                        return pad;
                    }
                }
                return null;
            },

            hasScores() {
                if (this.scorepad == null || this.scorepad.length == 0) {
                    return false;
                }
                return true;
            },

            getProgressAndScore(progress, score) {
                return progress + ' &#x21e8; ' + score + ' <div class="ea-icon-leaf"></div>';
            },

            getScorepadContainer() {
                return document.getElementById('ea-scorepad-container');
            },

            getScorepadTable() {
                return document.getElementById('ea-scorepad-table');
            },

            drawCardScoreLine(cardId) {
                this.clearScoreLines();
                if (!this.scorepad) {
                    return;
                }
                for (const pad of this.scorepad) {
                    if (pad.scoreExtraPerCardId && cardId in pad.scoreExtraPerCardId) {
                        this.drawScoreLineForCardIds(pad.scoreExtraPerCardId[cardId].split('-'));
                    }
                }
            },

            activateScorepad(scorepad, gameHasEnded, isInstantaneous = false) {
                this.scorepad = scorepad;
                const scorepadContainer = this.getScorepadContainer();
                if (scorepad.length == 0 || !gameui.isTrue(gameHasEnded)) {
                    scorepadContainer.classList.add('bx-hidden');
                    return Promise.resolve();
                }

                // Display and fill scorepad table
                scorepadContainer.classList.remove('bx-hidden');
                const scorepadTable = this.getScorepadTable();
                const scorepadHead = scorepadTable.querySelector('thead tr');
                for (const pad of scorepad) {
                    const span = gameui.createPlayerColorNameElement(pad.playerId);
                    if (pad.playerId == gameui.GAIA_PLAYER_ID) {
                        span.innerText = _('Gaia');
                    }
                    const td = document.createElement('td');
                    td.appendChild(span);
                    scorepadHead.appendChild(td);
                }

                // Fill scorepad with scores
                for (let idx = 0; idx < scorepad[0].scoresInOrder.length; ++idx) {
                    const scorepadRow = scorepadTable.querySelector('tbody tr:nth-child(' + (idx + 1) + ')');
                    if (scorepad.every((pad) => pad.scoresInOrder[idx] === null)) {
                        scorepadRow.classList.add('bx-hidden');
                    }
                    for (const pad of scorepad) {
                        const td = document.createElement('td');
                        scorepadRow.appendChild(td);
                        if (!('elements' in pad)) {
                            pad.elements = {};
                        }
                        pad.elements[idx] = td;
                    }
                }
                const waitTime = (this.SCORE_TIME_PER_PLAYER / scorepad[0].scoresInOrder.length);
                const movements = [];
                for (let idx = 0; idx < scorepad[0].scoresInOrder.length; ++idx) {
                    for (const pad of scorepad) {
                        if (pad.scoresInOrder[idx] === null) {
                            continue;
                        }
                        const movementIdx = movements.length - 1;
                        const updateScore = () => gameui.wait(waitTime, isInstantaneous).then(() => {
                            const counter = new bx.Numbers();
                            counter.addTarget(pad.elements[idx]);
                            counter.toValue(pad.scoresInOrder[idx], isInstantaneous);
                        });
                        if (movementIdx < 0) {
                            movements.push(Promise.resolve().then(() => updateScore()));
                        } else {
                            movements.push(movements[movementIdx].then(() => updateScore()));
                        }
                    }
                }
                return Promise.all(movements).then(() => {
                    // Display scores on terrain cards
                    const cardIdWithExtraScore = new Set();
                    for (const pad of scorepad) {
                        const playerColor = (
                            (pad.playerId in gameui.gamedatas.players)
                                ? gameui.gamedatas.players[pad.playerId].player_color
                                : null
                        );
                        for (const cardId in pad.scoreTerrainPerCardId) {
                            gameui.cardMgr.addScoreToCardId(cardId, pad.scoreTerrainPerCardId[cardId], playerColor);
                        }
                        if (pad.scorePlayerEcosystem !== null) {
                            const cardElem = document.querySelector('#ea-area-player-' + pad.playerId + ' .ea-player-board .ea-player-board-card-2 .ea-card');
                            if (cardElem !== null) {
                                gameui.cardMgr.addScoreToCardId(cardElem.dataset.cardId, pad.scorePlayerEcosystem, playerColor);
                            }
                        }
                        if (pad.scorePublicEcosystem1 !== null) {
                            const cardElem = document.querySelector('#ea-fauna-board-ecosystem-card-0 .ea-card');
                            if (cardElem !== null) {
                                gameui.cardMgr.addScoreToCardId(cardElem.dataset.cardId, pad.scorePublicEcosystem1, playerColor);
                            }
                        }
                        if (pad.scorePublicEcosystem2 !== null) {
                            const cardElem = document.querySelector('#ea-fauna-board-ecosystem-card-1 .ea-card');
                            if (cardElem !== null) {
                                gameui.cardMgr.addScoreToCardId(cardElem.dataset.cardId, pad.scorePublicEcosystem2, playerColor);
                            }
                        }
                        if (pad.scoreExtraPerCardId) {
                            for (const cardId in pad.scoreExtraPerCardId) {
                                cardIdWithExtraScore.add(cardId);
                            }
                        }
                    }
                    let scoreLineCurrentCardId = null;
                    for (const cardId of cardIdWithExtraScore) {
                        const cardElem = gameui.cardMgr.getCardElementById(cardId);
                        const scoreLineElement = cardElem.querySelector('.ea-card-score-line');
                        scoreLineElement.classList.remove('bx-hidden');
                        scoreLineElement.addEventListener('click', () => {
                            if (scoreLineCurrentCardId == cardId) {
                                scoreLineCurrentCardId = null;
                                this.clearScoreLines();
                            } else {
                                scoreLineCurrentCardId = cardId;
                                this.drawCardScoreLine(cardId);
                            }
                        });
                    }
                });
            },

            clearScoreLines() {
                for (const pathElem of document.querySelectorAll('.ea-area-player-tableau .ea-scoring-lines path')) {
                    pathElem.setAttribute('d', '');
                }
            },

            drawScoreLineForCardIds(cardIdList) {
                if (cardIdList.length == 0) {
                    return;
                }
                if (cardIdList.length == 1 && cardIdList[0].length == 0) {
                    return;
                }
                const firstCardId = cardIdList[0];
                const firstCardElem = gameui.cardMgr.getCardElementById(firstCardId);

                const tableauElem = firstCardElem.closest('.ea-area-player-tableau');
                const svgElem = tableauElem.querySelector('.ea-scoring-lines');
                svgElem.setAttribute('width', tableauElem.offsetWidth);
                svgElem.setAttribute('height', tableauElem.offsetHeight);
                svgElem.setAttribute('viewBox', '0 0 ' + tableauElem.offsetWidth + ' ' + tableauElem.offsetHeight);

                let pathCommand = 'M ' + this.getCardIdLineCenter(firstCardId);
                if (cardIdList.length == 1) {
                    pathCommand += 'm -20,0 l 40,0';
                } else {
                    for (const cardId of cardIdList) {
                        pathCommand += ' L ' + this.getCardIdLineCenter(cardId);
                    }
                }
                const pathElem = svgElem.querySelector('path');
                pathElem.setAttribute('d', pathCommand);
            },

            getCardIdLineCenter(cardId) {
                const cardContainerElem = gameui.cardMgr.getCardElementById(cardId);
                const cardElem = gameui.cardMgr.getCardSelectionElementById(cardId);
                return (
                    (cardContainerElem.offsetLeft + cardElem.offsetWidth / 2)
                    + ','
                    + (cardContainerElem.offsetTop + cardElem.offsetHeight / 2)
                );
            },
        });
    });