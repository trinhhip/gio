define([
    'jquery',
    'underscore',
    'Mirasvit_GoogleTagManager/js/event/abstract'
], function ($, _, AbstractEvent) {
    'use strict';
    
    return AbstractEvent.extend({
        listeners: function () {
            if (typeof $.mage.addToWishlist == 'undefined') {
                return;
            }
            
            let wishlistWidget = $.mage.addToWishlist();
    
            let wishlistSelector = null;
            if (wishlistWidget) {
                wishlistSelector = wishlistWidget.options.actionElement;
            }
            
            if (wishlistSelector) {
                $('body').on('click', wishlistSelector, function (e) {
                    let params    = $(e.currentTarget).data('post');
                    let productId = parseInt(params.data.product);
        
                    let productIds = [];
                    let missedIds = [];
        
                    if (productId) {
                        productIds.push(productId);
            
                        this.setGaData(null, productIds);
                    }
                }.bind(this));
            }
        },
        
        setGaData: function (listEl, productIds) {
            let productId = productIds[0];
            
            //let ga3Data = {
            //    'event': 'productClick',
            //    'ecommerce': {
            //        'click': {
            //            'actionField': {'list': listName},      // Optional list property.
            //            'products': [ProductStorage().getProductData(productId)]
            //        }
            //    }
            //};
            //
            //window.dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
            //window.dataLayer.push(ga3Data);
            
            window.dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
            gtag('event', 'add_to_wishlist', {
                    currency: window.mstGtmProducts[productId].currency,
                    value: window.mstGtmProducts[productId].price,
                    items: [window.mstGtmProducts[productId]]
                }
            );
        }
    });
});
