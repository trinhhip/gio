/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Checkout/js/model/sidebar',
        'Omnyfy_Vendor/js/model/vendor-source-stock'
    ],
    function($, Component, quote, stepNavigator, sidebarModel, source) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping-information'
            },

            isVisible: function() {
                return !quote.isVirtual() && stepNavigator.isProcessed('shipping');
            },

            getShippingMethodTitle: function() {
                var shippingMethod = quote.shippingMethod();
                var title ='';
                if (shippingMethod) {
                    for(var i in shippingMethod) {
                        if (title != '') {
                            title += ', ';
                        }
                        title += shippingMethod[i].carrier_title + " - " + shippingMethod[i].method_title;
                    }
                }
                return title;
            },
            getShippingMethods: function() {
                var shippingMethod = quote.shippingMethod(),
                    locationName = '',
                    title,
                    sourcestock;
                var result = [];
                if (this.isStorePickup()) {
                    title = shippingMethod['carrier_title'] + ' - ' + shippingMethod['method_title'];
                    sourcestock = source.getSourceById(shippingMethod.extension_attributes.source_stock_id);
                    if (quote.shippingAddress().firstname !== undefined) {
                        locationName = quote.shippingAddress().firstname + ' ' + quote.shippingAddress().lastname;
                        title += ' "' + locationName + '"';
                    }
                } else {
                    if (shippingMethod) {
                        for (var i in shippingMethod) {
                            title = shippingMethod[i].carrier_title + " - " + shippingMethod[i].method_title,
                                sourcestock = source.getSourceById(i);
                                if (sourcestock) {
                                    result.push({title: title, sourcestock: sourcestock});
                                }
                        }
                    }
                }

                return result;
            },
            isStorePickup: function () {
                var shippingMethod = quote.shippingMethod(),
                    isStorePickup = false;

                if (shippingMethod !== null) {
                    isStorePickup = shippingMethod['carrier_code'] === 'instore' &&
                        shippingMethod['method_code'] === 'pickup';
                }

                return isStorePickup;
            },
            back: function() {
                sidebarModel.hide();
                stepNavigator.navigateTo('shipping');
            },

            backToShippingMethod: function() {
                sidebarModel.hide();
                stepNavigator.navigateTo('shipping', 'opc-shipping_method');
            }
        });
    }
);
