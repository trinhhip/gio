define([
    'Magento_Ui/js/form/element/abstract',
    'jquery',
    'ko',
    'Amasty_AltTagGenerator/js/form/element/rules-form',
    'uiRegistry',
    'prototype'
], function (Abstract, $, ko, VarienRulesForm, uiRegistry) {
    'use strict';

    return Abstract.extend({
        default: {
            formContent: '',
            elementTmpl: 'Amasty_AltTagGenerator/form/element/rule-conditions-chooser',
            newFormChildUrl: '',
            conditionsFormId: 'rule_conditions_fieldset'
        },

        /**
         *
         * @return {string}
         */
        getFormContent: function () {
            return uiRegistry.get(this.provider).get(this.dataScope);
        },

        initForm: function () {
            window[this.conditionsFormId] = new VarienRulesForm(this.conditionsFormId, this.newFormChildUrl);
            this.processRuleFormChange();
        },

        processRuleFormChange: function () {
            var formValue = $('#' + this.conditionsFormId).serializeArray(),
                parsedFormValue = this.parseFormValue(formValue);

            this.value(parsedFormValue);
        },

        /**
         * Transform jQuery.serializeArray result to tree like object
         *
         * @param {object} formValue
         * @return {object}
         */
        parseFormValue: function (formValue) {
            var result = {};

            formValue.forEach(function (formPart) {
                var flatKey = formPart['name'],
                    partValue = formPart['value'],
                    keyParts = flatKey.split('[').map(function (rawKeyPart) {
                        return rawKeyPart === ']' ? '[]' : rawKeyPart.replace(/]$/g, '');
                    });

                this.setValue(result, keyParts.reverse(), partValue);
            }.bind(this));

            return result;
        },

        /**
         * Inserts a value into an object at the path specified in the first argument.
         *
         * @example
         *   keysPathParts = ['c', 'b', 'a']
         *   result = {a: {b: {c: value}}}
         *
         * @param {object} object
         * @param keysPathParts
         * @param value
         */
        setValue: function (object, keysPathParts, value) {
            if (keysPathParts.length > 0) {
                var key = keysPathParts.pop();

                if (keysPathParts.length === 0) {
                    if (object instanceof Array) {
                        object.push(value);
                    } else {
                        object[key] = value;
                    }
                } else {
                    var nextKey = keysPathParts.pop(),
                        newObject = nextKey === '[]' ? [] : {};

                    newObject = object[key] || newObject;
                    keysPathParts.push(nextKey);
                    object[key] = newObject;
                    this.setValue(newObject, keysPathParts, value);
                }
            }
        }
    });
});
