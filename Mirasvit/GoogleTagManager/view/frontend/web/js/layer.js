define([
    'jquery',
    'uiComponent',
    'underscore',
    'Magento_Customer/js/customer-data'
], function ($, Component, _, customerData) {
    'use strict';
    
    return Component.extend({
        initialize: function () {
            this._super();
            
            const gtm = customerData.get('gtm');
            
            customerData.reload(['gtm'], false);
            
            gtm.subscribe(this.onUpdate);
        },
        
        onUpdate: function (data) {
            _.each(data.push, function (item) {
                if (item) {
                    window.dataLayer.push({ecommerce: null});
                    window.dataLayer.push(item);
                }
            })
        }
    });
});
