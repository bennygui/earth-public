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
        return declare("bx.UtilTrait", null, {
            toPascalCase(str) {
                return this.toCamelCase(' ' + str);
            },
            toCamelCase(str) {
                return str.toLowerCase().replace(/[^a-zA-Z0-9]+(.)/g, (match, chr) => {
                    return chr.toUpperCase();
                });
            },
            toDashCase(str) {
                return str.replace(/\.?([A-Z]+)/g, (match, chr) => {
                    return "-" + chr.toLowerCase()
                }).replace(/^-/, '');
            },
            areObjectsEqual(a, b) {
                if (a === b) {
                    return true;
                }

                if (typeof a != 'object' || typeof b != 'object' || a === null || b === null) {
                    return false;
                }

                const keysA = Object.keys(a);
                const keysB = Object.keys(b);

                if (keysA.length != keysB.length) {
                    return false;
                }

                for (const key of keysA) {
                    if (!keysB.includes(key)) {
                        return false;
                    }

                    if (typeof a[key] === 'function' || typeof b[key] === 'function') {
                        if (a[key].toString() != b[key].toString()) {
                            return false;
                        }
                    } else {
                        if (!this.areObjectsEqual(a[key], b[key])) {
                            return false;
                        }
                    }
                }
                return true;
            },

            deepClone(o) {
                return JSON.parse(JSON.stringify(o));
            },

            areArraysEqual(a, b) {
                if (a.length != b.length) {
                    return false;
                }
                return a.every((v, i) => v == b[i]);
            },

            isTrue(v) {
                if (v === undefined || v === null || v === false) {
                    return false;
                }
                if (v === true || v === 'true' || (!isNaN(v) && parseInt(v) != 0)) {
                    return true;
                }
                return false;
            },
            isFalse(v) {
                return !this.isTrue(v);
            },

            rotateValueToFront(array, value) {
                const index = array.indexOf(value);
                if (index <= 0) {
                    return array.slice();
                }
                const front = array.slice(0, index);
                const back = array.slice(index);
                return back.concat(front);
            },
        });
    });