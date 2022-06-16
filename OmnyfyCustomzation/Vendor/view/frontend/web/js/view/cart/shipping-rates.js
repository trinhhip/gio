define(function () {
    'use strict';

    var mixin = {
        defaults: {
            template: 'OmnyfyCustomzation_Vendor/cart/rates-template'
        },
    };

    return function (target) {
        return target.extend(mixin);
    };
});
