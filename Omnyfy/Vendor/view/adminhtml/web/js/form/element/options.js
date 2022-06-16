define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/modal'
], function (_, uiRegistry, select, modal) {
    'use strict';
    return select.extend({
        initialize: function () {
            this._super();
            var vendorId;
            vendorId = this.getInitialValue();
            require(['jquery', 'reloadGrid'], function ($, reloadGrid) {
                reloadGrid.reloadUIComponent("index = assign_sources_grid", vendorId);
            });

            return this;
        },
        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            console.log('Select Update');
            console.log(value);
            require(['jquery', 'reloadGrid'], function ($, reloadGrid) {
                reloadGrid.reloadUIComponent("index = assign_sources_grid", value);
            });
            return this._super();
        },
    });
});