define([
    'ko',
    'underscore',
    'mageUtils',
    'uiClass',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/model/quote',
    'Omnyfy_Vendor/js/view/shipping'
], function (ko, _, utils, Class, shippingService, quote, omnyfyShipping) {
    'use strict';

    return Class.extend({
        easy_ship: 'easyship_easyship',
        element: null,
        carrierValue: null,

        initialize: function (element) {
            this.element = element;
        },

        observeShippingMethods: function () {
            if (this.getShippingMethods().length) {
                quote.shippingMethod.subscribe(this.toggleVisibilityForRate, this);
                omnyfyShipping["defaults"].currentSelectedShippingData.subscribe(this.toggleVisibilityForRate, this);
                /* hide element if no shipping method is selected*/
                this.toggleVisibilityForRate();
            } else {
                this.relationInitCheck();
            }

            return this;
        },

        toggleVisibility: function(rates) {
            _.some(rates, function(rate) {
                return this.toggleVisibilityForRate(rate);
            }, this);
        },

        toggleVisibilityForRate: function (rate) {
            var visible = false;
            var shippingMethodCodes = this.getShippingMethodCode(rate);
            if (shippingMethodCodes.length){
                shippingMethodCodes.forEach((shippingMethodCode) => {
                    if (shippingMethodCode) {
                        if (!visible){
                            visible = _.contains(this.getShippingMethods(), shippingMethodCode);
                            this.element.hidedByRate = !visible;
                        }
                    } else {
                        this.element.hidedByRate = true;
                    }
                })
            }
            if (!this.element.hidedByDepend) {
                this.element.visible(visible);
            }
            this.relationInitCheck();
            return visible;
        },

        relationInitCheck: function() {
            if (_.isFunction(this.element.initCheck) && !this.element.isRelationsInit) {
                this.element.initCheck();
            } else if (!_.isUndefined(this.element.isRelationsInit) && this.element.isRelationsInit) {
                this.element.checkDependencies();
            }
        },

        getShippingMethods: function() {
            return this.element.shipping_methods;
        },

        getShippingMethodCode: function (rate) {
            if (!quote.shippingMethod()) {
                return false;
            }

            if (!rate) {
                rate = quote.shippingMethod();
            }
            let shippingMethodCodes = [];
            /* START Omnyfy Vendor shipping rate support */


            Object.values(rate).forEach((value) => {
                if (value.carrier_code && value.method_code) {
                    this.carrierValue = value.carrier_code + '_' + value.method_code;
                    this.carrierValue = this.carrierValue.includes(this.easy_ship) ? this.easy_ship : this.carrierValue;
                    shippingMethodCodes.push(this.carrierValue);
                }
            });
            return shippingMethodCodes;

            /* END Omnyfy Vendor shipping rate support */
        }
    });
});
