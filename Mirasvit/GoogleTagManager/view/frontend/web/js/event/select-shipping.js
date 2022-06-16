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
                        'actionField': {'step': 'shipping', 'option': quote.shippingMethod().carrier_title}
                    }
                }
            };
    
            window.dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
            window.dataLayer.push(ga3Data);
    
            window.dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
            
            if (typeof gtag != 'undefined') {
                gtag('event', 'add_shipping_info', {
                        currency:      quote.getTotals()().quote_currency_code,
                        value:         quote.shippingMethod().amount,
                        shipping_tier: quote.shippingMethod().carrier_title,
                        items:         []
                    }
                );
            } else {
                window.dataLayer.push({event: "add_shipping_info", ecommerce: {
                    currency:      quote.getTotals()().quote_currency_code,
                    value:         quote.shippingMethod().amount,
                    shipping_tier: quote.shippingMethod().carrier_title,
                    items:         []
                }});
            }
        }
    });
});
