define([
    'jquery',
    'uiComponent',
    'underscore',
    'Mirasvit_GoogleTagManager/js/event/event-selectors'
], function ($, Component, _, EventSelectors) {
    'use strict';
    
    return Component.extend({
        isLoading: false,
        isInitEvents: false,
        
        initialize: function (itemInfoUrl) {
            this.itemInfoUrl = itemInfoUrl;
            
            this._super();
    
            setInterval(this.updateProducts.bind(this), 200);
        },
        
        getProductData: function (id) {
            return window.mstGtmProducts[id];
        },
        
        updateProducts: function () {
            if (this.isLoading) {
                return;
            }
            
            let $productEls = $(EventSelectors().getProductSelector());
    
            let missedIds  = [];
    
            if ($productEls.length) {
                _.each($productEls, function (productEl) {
                    let $productEl = $(productEl);
                    
                    let productId = parseInt($productEl.attr(EventSelectors().getProductAttribute()));
            
                    if (!productId || typeof window.mstGtmProducts[productId] == 'undefined') {
                        missedIds.push($productEl.attr(EventSelectors().getProductAttribute()));
                    }
                }.bind(this));
            }
    
            if (missedIds.length) {
                this.isLoading = true;
                
                $.ajax({
                    url:      this.itemInfoUrl,
                    type:     'POST',
                    dataType: 'json',
                    data:     {product_ids: missedIds},
            
                    success: function (response) {
                        if (typeof response.data != 'undefined') {
                            for (var i in response.data) {
                                window.mstGtmProducts[response.data[i]['product_id']] = response.data[i];
                            }
                        }
                        this.isLoading = false;
    
                        // instead of $(document).ready()
                        $(document).trigger('mst-gtm-init-products');
                    }.bind(this)
                });
            }
        }
    });
});
