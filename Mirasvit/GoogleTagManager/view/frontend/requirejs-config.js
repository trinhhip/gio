var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/select-payment-method': {
                'Mirasvit_GoogleTagManager/js/event/select-payment-mixin': true
            },
            'Magento_Checkout/js/action/select-shipping-method': {
                'Mirasvit_GoogleTagManager/js/event/select-shipping-mixin': true
            }
        }
    }
};
