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
        return declare("bx.DiceTrait", null, {
            createDiceElement(id, faceCreateFct) {
                const dice = document.createElement('div');
                dice.classList.add('bx-dice');
                dice.id = id;

                const diceFaces = document.createElement('div');
                diceFaces.classList.add('bx-dice-faces');

                dice.appendChild(diceFaces);

                for (let i = 0; i < 6; ++i) {
                    const face = document.createElement('div');
                    face.classList.add('bx-dice-face');
                    face.classList.add('bx-dice-face-' + i);
                    diceFaces.appendChild(face);
                    face.appendChild(faceCreateFct(i));
                }

                return dice;
            },

            createAndAnimateDiceFromSide(id, parentElement, finalFace, faceCreateFct, isInstantaneous = false) {
                const duration = 1000;
                const diceContainer = this.createDiceElement(id, faceCreateFct);
                parentElement.appendChild(diceContainer);
                const dice = diceContainer.querySelector('.bx-dice-faces');
                const setFinalTransform = () => {
                    delete diceContainer.style.zIndex;
                    switch (parseInt(finalFace)) {
                        default:
                            debug('BUG! createAndAnimateDiceFromSide: invalid finalFace' + finalFace);
                        case 0:
                            dice.style.transform = 'none';
                            break;
                        case 1:
                            dice.style.transform = 'rotateX(180deg)';
                            break;
                        case 2:
                            dice.style.transform = 'rotateY(-90deg)';
                            break;
                        case 3:
                            dice.style.transform = 'rotateY(90deg)';
                            break;
                        case 4:
                            dice.style.transform = 'rotateX(-90deg)';
                            break;
                        case 5:
                            dice.style.transform = 'rotateX(90deg)';
                            break;
                    }
                };
                if (isInstantaneous || this.isFastMode()) {
                    setFinalTransform();
                    return Promise.resolve();
                }
                return new Promise((resolve, reject) => {
                    diceContainer.style.zIndex = 5000;
                    dice.style.opacity = 0;

                    const rect = dice.getBoundingClientRect();
                    dice.style.top = (-1 * rect.top) + 'px';
                    dice.style.left = (-1 * rect.left) + 'px';
                    let rotX = 0;
                    let rotY = 0;
                    switch (parseInt(finalFace)) {
                        default:
                            debug('BUG! createAndAnimateDiceFromSide: invalid finalFace' + finalFace);
                        case 0:
                            rotX = 360 + 45 + 180;
                            rotY = -360 - 45;
                            break;
                        case 1:
                            rotX = 360 + 45 + 180;
                            rotY = 360 + 45;
                            break;
                        case 2:
                            rotX = 360 + 180;
                            rotY = -360 - 45 - 90;
                            break;
                        case 3:
                            rotX = -360 - 180;
                            rotY = -360 - 45 - 90;
                            break;
                        case 4:
                            rotX = 360 + 45 + 90;
                            rotY = -360 - 45;
                            break;
                        case 5:
                            rotX = 360 + 45 + 90;
                            rotY = 360 + 45;
                            break;
                    }
                    dice.style.transform = 'rotateX(' + rotX + 'deg) rotateY(' + rotY + 'deg)';
                    setTimeout(() => {
                        dice.style.transition = 'transform ' + duration + 'ms, top ' + duration + 'ms, left ' + duration + 'ms';
                        setTimeout(() => {
                            dice.style.opacity = null;
                            dice.style.top = '0px';
                            dice.style.left = '0px';
                            setFinalTransform();
                            setTimeout(() => {
                                resolve();
                            }, duration);
                        }, 1);
                    }, 1);
                });
            },
        });
    });