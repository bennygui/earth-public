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
        return declare("bx.LastRoundTrait", [
            bx.UtilTrait,
        ], {
            LAST_ROUND_ID: 'bx-display-last-round',

            constructor() {
                this.notificationsToRegister.push(['NTF_UPDATE_LAST_ROUND', 1]);
                this.lastRoundBannerText = null;
            },

            setup(gamedatas) {
                this.inherited(arguments);

                this.insertLastRoundElem();

                this.displayLastRound(gamedatas.isLastRound);
            },

            changeLastRoundBannerText(text) {
                this.lastRoundBannerText = text;
                const elem = document.getElementById(this.LAST_ROUND_ID);
                if (elem !== null) {
                    elem.innerText = text;
                }
            },

            insertLastRoundElem() {
                const container = document.getElementById('game_play_area');

                const elem = document.createElement('div');
                elem.id = this.LAST_ROUND_ID;
                elem.classList.add('bx-hidden');

                const text = document.createElement('div');
                text.innerText =
                    this.lastRoundBannerText === null
                        ? _('This is the last round!')
                        : this.lastRoundBannerText;
                elem.appendChild(text);

                container.insertBefore(elem, container.firstChild);
            },

            displayLastRound(doDisplay) {
                const elem = document.getElementById(this.LAST_ROUND_ID);
                if (this.isTrue(doDisplay)) {
                    elem.classList.remove('bx-hidden');
                } else {
                    elem.classList.add('bx-hidden');
                }
            },

            notif_UpdateLastRound(args) {
                this.displayLastRound(args.args.isLastRound);
            },
        });
    });