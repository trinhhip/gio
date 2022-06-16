/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'ko',
        'Magento_Checkout/js/model/totals',
        'uiComponent',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Checkout/js/model/quote',
        'Omnyfy_Vendor/js/model/location',
        'Omnyfy_Vendor/js/model/vendor-source-stock'
    ],
    function (ko, totals, Component, stepNavigator, quote, location, source) {
        'use strict';
        var locations = ko.observable(location.getLocations());
        var sources = ko.observable(source.getSources());
        var shippingConfiguration = window.checkoutConfig.shippingConfiguration;
        var shippingOverallId = window.checkoutConfig.shippingOverallId;
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/summary/cart-items'
            },
            totals: totals.totals(),
            getItems: totals.getItems(),
            sources: ko.observable(source.getSources()),
            reloadSources: function() {
                self = this;
                return self.sources(source.getSources());
            },
            getSourceStockId: function(item_id) {
                var sourceStockId = null;
                _.each(quote.getItems(), function(item){
                    if (shippingConfiguration == 'overall_cart' && shippingOverallId) {
                        sourceStockId = shippingOverallId;
                    }
                    else {
                        if (item.item_id == item_id) {
                            sourceStockId = item.source_stock_id;
                        }
                    }
                });
                return sourceStockId;
            },
            getLocations: function() {
                return locations;
            },
            getLocationId: function(item_id) {
                var location_id = null;
                _.each(quote.getItems(), function(item){
                    if (shippingConfiguration == 'overall_cart' && shippingOverallId) {
                        location_id = shippingOverallId;
                    }
                    else {
                        if (item.item_id == item_id) {
                            location_id = item.location_id;
                        }
                    }
                });
                return location_id;
            },
            getItemsQty: function() {
                return parseFloat(this.totals.items_qty);
            },
            isItemsBlockExpanded: function () {
                return quote.isVirtual() || stepNavigator.isProcessed('shipping');
            }
        });
    }
);
