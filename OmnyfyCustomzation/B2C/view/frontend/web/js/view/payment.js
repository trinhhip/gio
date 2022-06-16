define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/step-navigator',
], function ($, quote, stepNavigator) {
    'use strict';
    var mixin = {
        allowCountries: window.checkoutConfig.b2c_allows_countries,

        navigate: function () {
            var shippingAddress = quote.shippingAddress(),
                countryId = shippingAddress.countryId;

            if (this.allowCountries.indexOf(countryId) === -1) {
                stepNavigator.setHash('shipping')
            } else {
                this._super();
            }
        }
    };
    return function (target) {
        return target.extend(mixin);
    };
});
