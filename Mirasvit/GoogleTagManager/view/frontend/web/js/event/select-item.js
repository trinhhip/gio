define([
    'jquery',
    'underscore',
    'Mirasvit_GoogleTagManager/js/event/abstract'
], function ($, _, AbstractEvent) {
    'use strict';
    
    return AbstractEvent.extend({
        
        redirectUrl: '',
        
        listeners: function () {
            $(this.productSelector).on('click', "a", function (e) {
                e.preventDefault();
        
                let $currentEl = $(e.currentTarget);
                let $productEl = $(e.currentTarget).closest(this.productSelector);
                let $listEl    = $($productEl[0]).closest(this.listSelector);
                //let $eventEl   = $($productEl[0]).closest(eventSelector);
        
                this.redirectUrl = $currentEl.attr('href');
        
                let productIds = [];
        
                if ($productEl.length && $listEl.length) {
                    let productId = parseInt($productEl.attr(this.productAttr));
            
                    if (productId) {
                        productIds.push(productId);
                
                        this.setGaData($listEl, productIds);
                    }
                }
    
                window.location = this.redirectUrl;
            }.bind(this));
        },
        
        setGaData: function ($listEl, productIds) {
            let listId   = $listEl.attr(this.listAttr);
            let listName = $listEl.attr(this.listNameAttr);
    
            let productId = productIds[0];

            let ga3Data = {
                'event': 'productClick',
                'ecommerce': {
                    'click': {
                        'actionField': {'list': listName},      // Optional list property.
                        'products': [window.mstGtmProducts[productId]]
                    }
                }
            };
    
            window.dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
            window.dataLayer.push(ga3Data);
        
            window.dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
            gtag('event', 'select_item', {
                    item_list_id: listId,
                    item_list_name: listName,
                    items: [window.mstGtmProducts[productId]]
                }
            );
        }
    });
});
