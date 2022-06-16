/**
 * Copyright © Omnyfy, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
 define([
    'jquery',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'underscore',
    'escaper',
    'jquery/jquery-storageapi'
], function ($, Component, customerData, _, escaper) {
    'use strict';

    return Component.extend({
        defaults: {
            cookieMessages: [],
            messages: [],
            allowedTags: ['div', 'span', 'b', 'strong', 'i', 'em', 'u', 'a']
        },

        /**
         * Extends Component object by storage observable messages.
         */
        initialize: function () {
            this._super();

            this.cookieMessages = _.unique($.cookieStorage.get('mage-messages'), 'text');
            this.messages = customerData.get('messages').extend({
                disposableCustomerData: 'messages'
            });

            // wrap this.isAddedToCart() inside setTimeout, so that this function runs after customer-data.js initialized
            setTimeout(() => {
                this.isAddedToCart();
            });
            

            // Force to clean obsolete messages
            if (!_.isEmpty(this.messages().messages)) {
                customerData.set('messages', {});
            }

            $.mage.cookies.set('mage-messages', '', {
                samesite: 'strict',
                domain: ''
            });
        },

        /**
         * Prepare the given message to be rendered as HTML
         *
         * @param {String} message
         * @return {String}
         */
        prepareMessageForHtml: function (message) {
            return escaper.escapeHtml(message, this.allowedTags);
        },

        isAddedToCart: function () {

            // Check if a product has been added to a cart
            const cartCookieMessages = this.cookieMessages.filter((msg) => {
                return !!msg.text.match(/you added.*to your/i)
            });

            if (cartCookieMessages.length > 0) {
                customerData.set('added-to-cart-flag', true);
            }else{
                customerData.set('added-to-cart-flag', false);
            }
        }
    });
});
