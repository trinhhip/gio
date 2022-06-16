define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
], function (_, uiRegistry, select) {
    'use strict';
    return select.extend({
        // typeCalcFormula: 1,
        // typeFixed: 2,

        initialize: function () {
            var calcFormula = uiRegistry.get('index = calc_formula'),
                roundFactor = uiRegistry.get('index = round_factor'),
                price = uiRegistry.get('index = price'),
                type = this._super().initialValue;

            if (type == 1) {
                calcFormula.show();
                roundFactor.show();
                price.hide();
            } else {
                price.show();
                calcFormula.hide();
                roundFactor.hide();
            }
            return this;
        },

        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {

            var calcFormula = uiRegistry.get('index = calc_formula'),
                roundFactor = uiRegistry.get('index = round_factor'),
                price = uiRegistry.get('index = price');

            if (value == 1) {
                calcFormula.show();
                roundFactor.show()
                price.hide();
            } else {
                price.show();
                calcFormula.hide();
                roundFactor.hide();
            }
            return this._super();
        },
    });
});
