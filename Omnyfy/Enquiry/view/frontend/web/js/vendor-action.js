define(
    [
        'jquery',
        'mage/translate',
        'uiComponent',
        'underscore',
        'ko',
        'Magento_Customer/js/customer-data',
        'Magento_Ui/js/modal/confirm',
        'Magento_Ui/js/modal/modal',
        'mage/url',
    ],
    function ($, $t, Component, _, ko, customerData, confirm, modal, mageUrl) {
        'use strict';
        const ENQ_VALIDATE_URL = 'enquiry/enquiry_usage/validate'
        return Component.extend({
            defaults: {
                template: "Omnyfy_Enquiry/vendor_action",
                isShowPopup: ko.observable(''),
                vendorId: ko.observable(''),
                productId: ko.observable(''),
                urlLogin: ko.observable(''),
                firstname: ko.observable(''),
                lastname: ko.observable(''),
                email: ko.observable(''),
                phone: ko.observable(''),
                company: ko.observable(''),
            },

            isFormActive: ko.observable(false),

            initialize: function (config){
                this.isProductEnquiryActive(config.productId, config.vendorId);
                this._super();
            },

            showPopupEnquiry: function (){
                var self = this;
                if(self.isShowPopup){
                    self.showPopup();
                }
            },

            clickAction: function (element, event) {
                var self = this;
                event.preventDefault();
                if(!self.isCustomerLoggedIn()){
                    window.location.href = self.urlLogin;
                    return;
                }
                self.showPopup();
            },

            showPopup: function (){
                var self = this;
                var options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    title: $t('Make an enquiry'),
                    buttons: [{
                        text: $t('Submit'),
                        class: 'action primary',
                        click: function () {
                            if (self.validate()) {
                                $("#enquiry-form1").submit();
                                this.closeModal();
                            }
                        }
                    },{
                        text: $t('Cancel'),
                        class: '',
                        click: function () {
                            this.closeModal();
                        }
                    }]
                };
                var customerDataSession = customerData.get('customer')();
                self.firstname(customerDataSession.firstname);
                self.lastname(customerDataSession.lastname);
                self.email(customerDataSession.email);
                self.company(customerDataSession.company);
                self.phone(customerDataSession.phone);
                var formEl = $('#modal-vendor-action');
                modal(options, formEl);
                formEl.modal('openModal');
            },

            validate: function () {
                var form = '#enquiry-form1';
                return $(form).validation() && $(form).validation('isValid');
            },

            isCustomerLoggedIn: function (){
                var customer = customerData.get('customer')();
                return !!(customer.fullname && customer.firstname);
            },

            isProductEnquiryActive: function (productId, vendorId){
                var self = this;
                $.ajax({
                    url: mageUrl.build(ENQ_VALIDATE_URL),
                    data: {
                        vendorId: vendorId,
                        productId: productId
                    },
                    type: 'POST',
                }).done(function (response) {
                    if(!response.errors){
                        self.isFormActive(response.isEnqAvailable);
                    }
                }).fail(function (data) {
                    console.log(data);
                });
            }
        });
    }
);
