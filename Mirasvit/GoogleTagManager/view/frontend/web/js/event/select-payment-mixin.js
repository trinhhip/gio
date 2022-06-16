define([
    'mage/utils/wrapper',
    'Mirasvit_GoogleTagManager/js/event/select-payment'
], function (wrapper, SelectPayment) {
    'use strict';
    
    return function (selectPaymentFunction) {
        return wrapper.wrap(selectPaymentFunction, function (originalSelectPaymentFunction, paymentMethod) {
            originalSelectPaymentFunction(paymentMethod);
    
            if (paymentMethod) {
                SelectPayment().setGaData('', '');
            }
        });
    };
});
