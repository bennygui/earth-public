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
        return declare("ea.LeafTokenMgr", null, {
            setup(gamedatas) {
                const elemCreationElem = gameui.getElementCreationElement();
                for (const tokenId in gamedatas.leafs) {
                    const leaf = gamedatas.leafs[tokenId];
                    const leafElem = this.createLeafElement(leaf, true);
                    elemCreationElem.appendChild(leafElem);
                }
            },

            createLeafElement(leaf, setId = false) {
                const element = document.createElement('div');
                element.classList.add('ea-token-leaf');
                if (leaf.playerId == gameui.GAIA_PLAYER_ID) {
                    element.classList.add(gameui.gamedatas.gaiaColorName)
                } else {
                    element.classList.add(gameui.gamedatas.players[leaf.playerId].player_color_name)
                }
                element.dataset.playerId = leaf.playerId;
                element.dataset.tokenId = leaf.tokenId;
                if (setId) {
                    element.id = 'ea-leaf-token-id-' + leaf.tokenId;
                }
                return element;
            },

            getLeafElementByTokenId(tokenId) {
                return document.getElementById('ea-leaf-token-id-' + tokenId);
            },
        });
    });