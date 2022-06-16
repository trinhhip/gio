define([
    'mage/utils/wrapper',
    'Mirasvit_GoogleTagManager/js/event/select-shipping'
], function (wrapper, SelectShipping) {
    'use strict';
    
    var selectedShipping = '';
    
    return function (selectShippingFunction) {
        return wrapper.wrap(selectShippingFunction, function (originalSelectShippingFunction, shippingMethod) {
            originalSelectShippingFunction(shippingMethod);
    
            if (shippingMethod && selectedShipping != shippingMethod.carrier_code) {
                SelectShipping().setGaData('', '');
    
                selectedShipping = shippingMethod.carrier_code;
            }
        });
    };
});
