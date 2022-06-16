define([
    'jquery',
    'Magento_Ui/js/grid/provider'
], function ($, provider) {
    'use strict';

    return provider.extend({
        reload: function (options) {
            var templateRuleCondition = $('[data-form-part="amasty_alt_conditions"]').serialize();

            if (typeof this.params.filters === 'undefined') {
                this.params.filters = {};
            }

            this.params.filters.template_rule_condition = templateRuleCondition;

            this._super({'refresh': true});
        }
    });
});
