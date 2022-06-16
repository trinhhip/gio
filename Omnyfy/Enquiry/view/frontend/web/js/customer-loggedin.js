define([
    'jquery',
    'uiComponent',
    'ko',
    'jquery/jquery-storageapi',
    'Magento_Customer/js/customer-data'
], function ($, Component, ko, storage, customerData) {
    'use strict';

    return Component.extend({
        /** @inheritdoc */
        initialize: function () {
            this._super();

            var cacheKey = 'customer',
                that = this;

            this.isFirstPageAfterLogin = document.referrer.indexOf('/customer/account/login') !== -1 ? true : false;
            this.isLoaded = ko.observable(false);
            this.isLoggedIn = ko.observable(false);
            this.isNotLoggedIn = ko.observable(true);
            this.customer = customerData.get('customer');//ko.observable($.initNamespaceStorage('mage-cache-storage').localStorage.get(cacheKey));

            if (this.customer() !== undefined) {
                this.isLoggedIn(this.customer().fullname ? true : false);
                this.isNotLoggedIn(this.customer().fullname ? false : true);

                if (this.customer().fullname) {
                    this.setFormData();
                }

                if (this.customer.fullname && this.isFirstPageAfterLogin
                    || !this.customer.fullname && !this.isFirstPageAfterLogin) {
                    this.isLoaded(true);
                }
            }

            if (this.customer() == undefined && !this.isFirstPageAfterLogin) {
                this.isLoaded(true);
            }

            this.customer.subscribe(function (updatedValue) {
                that.isLoggedIn(updatedValue.fullname ? true : false);
                that.isNotLoggedIn(updatedValue.fullname ? false : true);
                that.isLoaded(true);
                that.setFormData();
            });

            setTimeout(function () {
                if (that.isLoaded() === false) {
                    that.isLoaded(true);
                }
            }, 15000);
        },

        setFormData: function() {
            var customerDataSession = customerData.get('customer')();
            $('#firstname').val(customerDataSession.firstname);
            $('#lastname').val(customerDataSession.lastname);
            $('#emailaddress').val(customerDataSession.email);
            $('#company').val(customerDataSession.company);
            $('#mobilenumber').val(customerDataSession.phone);
        }
    });
});
