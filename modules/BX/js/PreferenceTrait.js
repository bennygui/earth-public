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
        return declare("bx.PreferenceTrait", null, {
            constructor() {
                // Format: ['prefId', defaultValue, {value: 'description', ...}]
                this.localPreferenceToRegister = [
                ];
            },

            onLoadingComplete() {
                this.inherited(arguments);
                this.onPreferenceReady();
            },

            setLocalPreference(prefId, value) {
                window.localStorage.setItem(prefId, value);
                this.onLocalPreferenceChanged(prefId, value);
            },

            getLocalPreference(prefId) {
                const value = window.localStorage.getItem(prefId);
                if (value === null) {
                    return this.getLocalPreferenceDefaultValue(prefId);
                }
                if (value === "true") {
                    return true;
                } else if (value === "false") {
                    return false;
                }
                return value;
            },

            getLocalPreferenceDefaultValue(prefId) {
                for (const regPref of this.localPreferenceToRegister) {
                    if (regPref[0] == prefId) {
                        return regPref[1];
                    }
                }
                return null;
            },

            onPreferenceReady() {
                for (const regPref of this.localPreferenceToRegister) {
                    const prefId = regPref[0];
                    this.onLocalPreferenceChanged(prefId, this.getLocalPreference(prefId));
                }
            },

            onLocalPreferenceChanged(prefId, value) {
            },
        });
    });