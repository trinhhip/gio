define([
    'jquery',
    'mage/utils/wrapper',
    'Amasty_GdprCookie/js/action/cookie-decliner'
], function ($, wrapper, cookieDecliner) {
    'use strict';

    return function (customerData) {
        customerData.init = wrapper.wrapSuper(customerData.init, function () {
            return cookieDecliner.call(this, this._super);
        });

        return customerData;
    };
});
