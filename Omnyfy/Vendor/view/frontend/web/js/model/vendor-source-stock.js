define(
    ['ko'],
    function (ko) {
        'use strict';

        var sourceData = window.checkoutConfig.sourceData;
        var sources = ko.observable(sourceData);
        var shippingConfiguration = window.checkoutConfig.shippingConfiguration;
        var overallShippingIds = window.checkoutConfig.shippingOverallId;
        return {
            sources: sources,
            getSources: function() {
                var result = [];

                var sourceData = window.checkoutConfig.sourceData;

                for(var id in sourceData ) {
                    result.push(sourceData[id]);
                }
                return result;
            },
            getSourceById: function(sourceStockId) {
                for(var id in sourceData) {
                    if (id == sourceStockId) {
                        return sourceData[id];
                    }
                }
                return false;
            }
        };
    }
);
