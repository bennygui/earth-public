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
        return declare("bx.DragScroller", null, {
            constructor(idOrElement, enabled = true) {
                this.element = idOrElement;
                if (typeof this.element == "string") {
                    this.element = document.getElementById(idOrElement);
                }
                this.enabled = enabled;
                this.mustDrag = false;

                this.attachToElement();
            },

            enable() {
                this.enabled = true;
            },

            disable() {
                this.enabled = false;
            },

            attachToElement() {
                let startX = 0;
                let scrollLeft = 0;

                this.element.addEventListener('mousedown', (e) => {
                    this.mustDrag = true;
                    startX = e.pageX - this.element.offsetLeft;
                    scrollLeft = this.element.scrollLeft;
                });
                this.element.addEventListener('mouseleave', () => {
                    this.mustDrag = false;
                    this.element.classList.remove('bx-is-dragging');
                });
                this.element.addEventListener('mouseup', () => {
                    this.mustDrag = false;
                    requestAnimationFrame(() => {
                        this.element.classList.remove('bx-is-dragging');
                    })
                });
                this.element.addEventListener('mousemove', (e) => {
                    if (!this.mustDrag || !this.enabled) {
                        return;
                    }
                    e.preventDefault();
                    this.element.classList.add('bx-is-dragging');
                    const x = e.pageX - this.element.offsetLeft;
                    const walk = (x - startX) * 1.5;
                    this.element.scrollLeft = scrollLeft - walk;
                });
            },
        });
    });