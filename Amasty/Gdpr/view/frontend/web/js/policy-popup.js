/**
 * Consent popup logic
 */
define([
    'jquery',
    'mage/url',
    'mage/translate',
    'Magento_Ui/js/modal/modal-component'
], function ($, urlBuilder, $t, modal) {
    'use strict';

    return modal.extend({
        defaults: {
            textUrl: '',
            acceptUrl: '',
            popupDataUrl: '',
            htmlContent: '',
            notificationText: $t('We would like to inform you that our Privacy ' +
                'Policy has been amended. Please, read and accept the new terms. '),
            versionChanged: false,
            consentPolicy: {},
            options: {
                autoOpen: false,
                type: 'popup',
                title: $t('Privacy Policy'),
                modalClass: 'amgdpr-modal-container',
                buttons: [ {
                    text: $t('I have read and accept'),
                    class: 'action action-primary',
                    actions: [ {
                        'targetName': '${ $.name }',
                        'actionName': 'acceptPolicy'
                    } ]
                } ]
            }
        },

        initialize: function () {
            this._super().showPopupWithConsentPolicy();

            return this;
        },

        initObservable: function () {
            this._super()
                .observe([
                    'htmlContent',
                    'versionChanged'
                ]);

            return this;
        },

        showPopupWithConsentPolicy: function () {
            $.ajax({
                url: this.popupDataUrl,
                method: 'GET',
                success: function (data) {
                    if (data.show) {
                        this.showPopup(data);
                    }
                }.bind(this)
            });
        },

        showPopup: function (consentPolicy) {
            this.consentPolicy = consentPolicy;
            this.versionChanged(consentPolicy.versionChanged);

            $.ajax({
                url: this.textUrl,
                method: 'GET',
                success: function (data) {
                    this.htmlContent(data.content);
                    this.openModal();
                }.bind(this)
            });
        },

        acceptPolicy: function () {
            if (!this.acceptUrl || !this.consentPolicy.policyVersion) {
                return;
            }

            $('body').trigger('processStart');
            $.ajax({
                url: this.acceptUrl,
                method: 'POST',
                data: this.consentPolicy,
                complete: function () {
                    this.closeModal();
                    $('body').trigger('processStop');
                }.bind(this)
            });
        }
    });
});
