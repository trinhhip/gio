define([
    'jquery',
    'underscore',
    'Mirasvit_GoogleTagManager/js/event/abstract',
    'Magento_Checkout/js/model/quote'
], function ($, _, AbstractEvent, quote) {
    'use strict';
    
    return AbstractEvent.extend({
        
        redirectUrl: '',
        
        listeners: function () {},
        
        setGaData: function (listEl, productIds) {
            let ga3Data = {
                'event': 'checkoutOption',
                'ecommerce': {
                    'checkout_option': {
                        'actionField': {'step': 'payment', 'option': quote.getPaymentMethod()().title}
                    }
                }
            };
    
            window.dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
            window.dataLayer.push(ga3Data);
        
            window.dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
            
            if (typeof gtag != 'undefined') {
                gtag('event', 'add_payment_info', {
                        currency:     quote.getTotals()().quote_currency_code,
                        value:        quote.getTotals()().grand_total,
                        payment_type: quote.getPaymentMethod()().title,
                        items:        []
                    }
                );
            } else {
                window.dataLayer.push({event: "add_payment_info", ecommerce: {
                    currency:     quote.getTotals()().quote_currency_code,
                    value:        quote.getTotals()().grand_total,
                    payment_type: quote.getPaymentMethod()().title,
                    items:        []
                }});
            }
        }
    });
});
