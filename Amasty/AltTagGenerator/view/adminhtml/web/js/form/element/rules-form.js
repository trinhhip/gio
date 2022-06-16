define([
    'jquery',
    'Magento_Rule/rules',
    'prototype'
], function (jQuery, Rules) {
    'use strict';

    return Class.create(Rules, {
        /**
         * @param {Function} $super
         * @param {Element} container
         * @param {Event} event
         * @return {void}
         */
        removeRuleEntry: function ($super, container, event) {
            $super(container, event);
            this.getCurrentForm().trigger('change');
        },

        /**
         * @param {Function} $super
         * @param {Element} container
         * @param {Event} event
         * @return {void}
         */
        showParamInputField: function ($super, container, event) {
            var result = $super(container, event);

            if (result !== false) {
                this.getCurrentForm().trigger('change');
            }
        },

        /**
         * @return {jQuery}
         */
        getCurrentForm: function () {
            return jQuery(this.parent);
        }
    });
});
