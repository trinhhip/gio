/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 define(
    [
        'underscore',
        'Magento_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/cart/cache',
        'Magento_Customer/js/customer-data'
    ],
    function (_, resourceUrlManager, quote, storage, totalsService, errorProcessor, cartCache, customerData) {
        'use strict';

        return {
            /**
             * Get shipping rates for specified address.
             */
            estimateTotals: function (address) {
                var serviceUrl, payload;
                totalsService.isLoading(true);
                serviceUrl = resourceUrlManager.getUrlForTotalsEstimationForNewAddress(quote),
                    payload = {
                        addressInformation: {
                            address: _.pick(address, cartCache.requiredFields)
                        }
                    };

                var rates = quote.shippingMethodGroup();
                var methodCode = '{';
                var carrierCode = '{';
                var count = 0;
                for (var id in rates) {
                    let strMethod = '"'+id+'":"'+rates[id]['method_code']+'"';
                    let strCarrier= '"'+id+'":"'+rates[id]['carrier_code']+'"';
                    methodCode += strMethod + ',';
                    carrierCode += strCarrier + ',';
                    count++;
                }

                methodCode = methodCode.slice(0, -1) + '}';
                carrierCode = carrierCode.slice(0, -1) + '}';

                if (count > 0) {
                    payload.addressInformation['shipping_method_code'] = methodCode;
                    payload.addressInformation['shipping_carrier_code'] = carrierCode;
                }

                storage.post(
                    serviceUrl, JSON.stringify(payload), false
                ).done(
                    function (result) {
                        quote.setTotals(result);
                    }
                ).fail(
                    function (response) {
                        errorProcessor.process(response);
                    }
                ).always(
                    function () {
                        totalsService.isLoading(false);
                    }
                );
            }
        };
    }
);