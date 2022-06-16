/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'OmnyfyCustomzation_OrderNote/js/model/order-notes',
    'Magento_Checkout/js/model/step-navigator'
], function (Component,quote,orderNotes,stepNavigator) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'OmnyfyCustomzation_OrderNote/summary/item/details'
        },

        /**
         * @param {Object} quoteItem
         * @return {String}
         */
        getValue: function (quoteItem) {
            return quoteItem.name;
        },
        getItemNote: function (itemId) {
            return orderNotes.orderNotes()[itemId];
        },
        isHidden: function () {
            return stepNavigator.isProcessed('shipping');
        },
        isEnabled: function () {
            return this.settings.isEnabled;
        }
    });
});
