define([
    "uiRegistry"
], function (registry) {
    'use strict';
    return {
        reloadUIComponent: function (gridName, value) {
            if (gridName) {
                var params = [];
                var target = registry.get(gridName);

                if (target && typeof target === 'object') {
                    target.set('params.vendor_id', value);
                    console.log('Custom reload');
                    console.log(target);
                }
            }
        }
    };
});