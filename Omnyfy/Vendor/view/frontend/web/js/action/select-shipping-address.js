define([
    'Magento_Checkout/js/model/quote',
    'mage/url',
    'jquery',
    'underscore'
], function (quote, urlBuilder, $, _) {
    'use strict';

    return function (shippingAddress) {
        quote.shippingAddress(shippingAddress);
        var url = urlBuilder.build('shop/checkout/changeSourceStock');
        var addressChange = {
            city: shippingAddress.city,
            country: shippingAddress.countryId,
            postcode: shippingAddress.postcode,
            region: shippingAddress.region,
            street: shippingAddress.street != undefined ? shippingAddress.street[0] : ''
        }
        $.ajax({
            method: 'POST',
            url: url,
            data: {
                quote_id: quote.getQuoteId(),
                shipping_address: addressChange
            },
            dataType: "json"
        }).done(function (respone){
            if (requirejs('uiRegistry').get("name=checkout.steps.shipping-step.shippingAddress") != undefined) {
                var productChange = respone.product_change;
                if (productChange != undefined && Object.keys(productChange).length > 0) {
                    window.checkoutConfig.sourceData = respone.source_stock;
                    var items = window.checkoutConfig.quoteItemData;
                    _.each(items, function(item, index) {
                        if (item.source_stock_id != productChange[item.product_id]) {
                            window.checkoutConfig.quoteItemData[index].location_id = productChange[item.product_id]; 
                            window.checkoutConfig.quoteItemData[index].source_stock_id = productChange[item.product_id]; 
                        }
                    });
                    if (requirejs('uiRegistry').get("name=checkout.sidebar.summary.cart_items") != undefined) {
                        requirejs('uiRegistry').get("name=checkout.sidebar.summary.cart_items").reloadSources();
                    }
                }
            }
        });
    };
});
