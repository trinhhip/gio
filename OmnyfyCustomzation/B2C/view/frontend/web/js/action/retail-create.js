/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/storage',
    'Magento_Ui/js/model/messageList',
    'Magento_Customer/js/customer-data',
    'mage/translate'
], function ($, storage, globalMessageList, customerData, $t) {
    'use strict';

    var callbacks = [],

        /**
         * @param {Object} retailData
         * @param {String} redirectUrl
         * @param {*} isGlobal
         * @param {Object} messageContainer
         */
        action = function (retailData, redirectUrl, isGlobal, messageContainer) {
            messageContainer = messageContainer || globalMessageList;

            return storage.post(
                'buyer/retail/createajax',
                JSON.stringify(retailData),
                isGlobal
            ).done(function (response) {
                if (response.success) {
                    console.log(response.message);
                    messageContainer.addErrorMessage({
                        'message': window.authenticationPopup.retailCreateSuccessMessage
                    });
                    callbacks.forEach(function (callback) {
                        callback(retailData, response);
                    });
                    customerData.invalidate(['customer']);

                    if (redirectUrl) {
                        window.location.href = redirectUrl;
                    } else if (response.redirectUrl) {
                        window.location.href = response.redirectUrl;
                    } else {
                        location.reload();
                    }
                } else {
                    messageContainer.addErrorMessage({
                        'message': response.message
                    });
                    callbacks.forEach(function (callback) {
                        callback(retailData, response);
                    });
                }
            }).fail(function () {
                messageContainer.addErrorMessage({
                    'message': $t('Could not authenticate. Please try again later')
                });
                callbacks.forEach(function (callback) {
                    callback(retailData);
                });
            });
        };

    /**
     * @param {Function} callback
     */
    action.registerLoginCallback = function (callback) {
        callbacks.push(callback);
    };

    return action;
});
