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
        // Inspired by https://github.com/bga-devs/tisaac-boilerplate
        return declare("bx.Numbers", null, {
            DELAY: 100,
            STEPS: 5,

            constructor(initialValues = 0, targetIdsOrElements = []) {
                this.targetIdsOrElements = targetIdsOrElements;
                this.currentValues = initialValues;
                this.targetValues = initialValues;
                this.onFinishStepValues = [];
                this.ensureNumbers();
                this.update();
            },

            addTarget(targetIdOrElement) {
                if (this.targetIdsOrElements instanceof Array) {
                    this.targetIdsOrElements.push(targetIdOrElement);
                } else {
                    this.targetIdsOrElements = [this.targetIdsOrElements, targetIdOrElement];
                }
                this.update();
            },

            registerOnFinishStepValues(callback) {
                this.onFinishStepValues.push(callback);
            },

            getValues() {
                return this.currentValues;
            },

            setValue(value) {
                this.setValues(value);
            },

            setValues(values) {
                this.currentValues = values;
                this.targetValues = values;
                this.ensureNumbers();
                this.update();
            },

            toValue(value, isInstantaneous = false) {
                this.toValues(value, isInstantaneous);
            },

            toValues(values, isInstantaneous = false) {
                if (isInstantaneous || gameui.isFastMode()) {
                    this.setValues(values);
                } else {
                    this.targetValues = values;
                    this.ensureNumbers();
                    this.stepValues(true);
                }
            },

            stepValues(firstCall = false) {
                if (this.currentAtTarget()) {
                    this.update();
                    if (!firstCall) {
                        for (const callback of this.onFinishStepValues) {
                            callback(this);
                        }
                    }
                    return;
                }
                if (this.currentValues instanceof Array) {
                    const newValues = [];
                    for (let i = 0; i < this.currentValues.length; ++i) {
                        newValues.push(this.stepOneValue(this.currentValues[i], this.targetValues[i]));
                    }
                    this.currentValues = newValues;
                } else {
                    this.currentValues = this.stepOneValue(this.currentValues, this.targetValues);
                }
                this.update();
                setTimeout(() => this.stepValues(), this.DELAY);
            },

            stepOneValue(current, target) {
                if (current === null) {
                    current = 0;
                }
                if (target === null) {
                    return null;
                }
                const step = Math.ceil(Math.abs(current - target) / this.STEPS);
                return (current + (current < target ? 1 : -1) * step);
            },

            update() {
                if (this.targetIdsOrElements instanceof Array) {
                    for (const target of this.targetIdsOrElements) {
                        this.updateOne(target);
                    }
                } else {
                    this.updateOne(this.targetIdsOrElements);
                }
            },

            updateOne(targetIdOrElement) {
                const elem = this.getElement(targetIdOrElement);
                elem.innerHTML = this.format();
            },

            getTargetElements() {
                if (this.targetIdsOrElements instanceof Array) {
                    return this.targetIdsOrElements.map((id) => this.getElement(id));
                } else {
                    return [this.getElement(this.targetIdsOrElements)];
                }
            },

            getTargetElement() {
                const elems = this.getTargetElements();
                if (elems.length == 0) {
                    return null;
                }
                return elems[0];
            },

            format() {
                if (this.currentValues instanceof Array) {
                    const formatted = [];
                    for (let i = 0; i < this.currentValues.length; ++i) {
                        formatted.push(this.formatOne(this.currentValues[i], this.targetValues[i]));
                    }
                    return this.formatMultiple(formatted);
                } else {
                    return this.formatOne(this.currentValues, this.targetValues);
                }
            },

            formatOne(currentValue, targetValue) {
                const span = document.createElement('span');
                if (currentValue != targetValue) {
                    span.classList.add('bx-counter-in-progress');
                }
                span.innerText = (currentValue === null ? '-' : currentValue);
                return span.outerHTML;
            },

            formatMultiple(formattedValues) {
                return formattedValues.join('/');
            },

            ensureNumbers() {
                if (this.currentValues instanceof Array) {
                    this.currentValues = this.currentValues.map((v) => this.ensureOneNumber(v));
                    this.targetValues = this.targetValues.map((v) => this.ensureOneNumber(v));
                } else {
                    this.currentValues = this.ensureOneNumber(this.currentValues);
                    this.targetValues = this.ensureOneNumber(this.targetValues);
                }
            },

            ensureOneNumber(value) {
                return (value === null ? null : parseInt(value));
            },

            currentAtTarget() {
                if (this.currentValues instanceof Array) {
                    return this.currentValues.every((v, i) => v == this.targetValues[i]);
                } else {
                    return (this.currentValues == this.targetValues);
                }
            },

            getElement(targetIdOrElement) {
                if (typeof targetIdOrElement == "string") {
                    return document.getElementById(targetIdOrElement);
                }
                return targetIdOrElement;
            },

        });
    });