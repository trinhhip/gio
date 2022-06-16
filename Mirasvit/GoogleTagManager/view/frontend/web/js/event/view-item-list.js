define([
    'jquery',
    'underscore',
    'Mirasvit_GoogleTagManager/js/event/abstract'
], function ($, _, AbstractEvent) {
    'use strict';
    
    return AbstractEvent.extend({
        
        isLoading: false,
    
        initEventSelector: function () {
            this.eventSelector = '[' + this.eventAttr + '="view_item_list"]';
        },
    
        listeners: function () {
            $(document).on('mst-gtm-init-products', function() {
                _.each($(this.eventSelector), function (el) {
                    let $el = $(el);
            
                    if (this.isVisible($el)) {
                        this.sendData($el);
                    }
                }.bind(this));
            }.bind(this));
    
            //$(window).scroll(function() {
            //    if (!this.isLoading) {
            //        this.isLoading = true;
            //
            //        _.each($(this.eventSelector), function (el) {
            //            let $el = $(el);
            //
            //            if (this.isVisible($el)) {
            //                this.sendData($el);
            //            }
            //        }.bind(this));
            //
            //        this.isLoading = false;
            //    }
            //}.bind(this));
        },
        
        sendData: function ($el) {
            let $listEl     = $(this.listSelector, $el);
            let $productEls = $(this.productSelector, $el);
            
            if (!$listEl.length && $el[0].hasAttribute(this.listAttr)) {
                $listEl = $el;
            }
    
            let productIds = [];
            
            _.each($productEls, function (productEl) {
                let productId = parseInt($(productEl).attr(this.productAttr));
                
                if (productId) {
                    productIds.push(productId);
                }
            }.bind(this));
            
            if (productIds.length) {
                this.setGaData($listEl, productIds);
            }
        },
    
        setGaData: function ($listEl, productIds) {
            let listId   = $listEl.attr(this.listAttr);
            let listName = $listEl.attr(this.listNameAttr);
            
            if (typeof this.sentLists[listId] == 'undefined' || !this.sentLists[listId]) {
                this.sentLists[listId] = true;
    
                let currency = '';
    
                let items = [];
                for (var i in productIds) {
                    items.push(window.mstGtmProducts[productIds[i]]);
        
                    if (typeof window.mstGtmProducts[productIds[i]].currency != 'undefined') {
                        currency = window.mstGtmProducts[productIds[i]].currency;
                    }
                }
    
                let ga3Data = {
                    'ecommerce': {
                        'currencyCode': currency,      // Optional list property.
                        'impressions':  [items]
                    }
                };
    
                window.dataLayer.push({ecommerce: null});  // Clear the previous ecommerce object.
                window.dataLayer.push(ga3Data);
        
                window.dataLayer.push({ecommerce: null});  // Clear the previous ecommerce object.
                window.dataLayer.push('event', 'view_item_list', {
                        item_list_id:   listId,
                        item_list_name: listName,
                        items:          [items]
                    }
                );
            }
        }
    });
});
