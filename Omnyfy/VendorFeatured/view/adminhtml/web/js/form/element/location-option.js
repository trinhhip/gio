define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/modal',
], function ($, _, uiRegistry, select, modal) {
    'use strict';
    return select.extend({
        initialize: function () {
            self = this;
            this._super();

            if(self.value()) {
                self.updateValueOptions(self.value());
            }

            return this;
        },
        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value)
        {
            if (value)
            {
                self.updateValueOptions(value);
            }
            return this._super();
        },
        updateValueOptions: function (value) {
            $.ajax({
                url: window.locationUrl,
                type: 'post',
                dataType: 'json',
                cache: false,
                showLoader: true,
                data: {vendorId: value,vendorFeaturedId: window.vendorFeaturedId}
            }).
            done(function (response) {
                if (!response.error) {
                    uiRegistry.get("omnyfy_vendorfeatured_vendor_featured_form.omnyfy_vendorfeatured_vendor_featured_form.General.source_code").options(response.options);
                    if (response.source) {
                        uiRegistry.get("omnyfy_vendorfeatured_vendor_featured_form.omnyfy_vendorfeatured_vendor_featured_form.General.source_code").value(response.source);
                    }
                    if (response.tags) {
                        uiRegistry.get("omnyfy_vendorfeatured_vendor_featured_form.omnyfy_vendorfeatured_vendor_featured_form.General.vendor_tags").value(response.tags);
                    }
                }
            });
        }
    });
});