/**
 * Copyright © Omnyfy, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * html pattern to use:
 *
 * <!-- ko ifnot: isLoaded -->
 *      <div><?= __('Loading...')?></div>
 * <!-- /ko -->
 *
 * <!-- ko if: isLoggedIn  -->
 *     <div style="display: none;" data-bind="visible: isLoggedIn">
 *      content for logged in user
 *      </div>
 * <!-- /ko -->
 *
 * <!-- ko if: isNotLoggedIn  -->
 *     <div style="display: none;" data-bind="visible: isNotLoggedIn">
 *          content for not loggedin user
 *     </div>
 * <!-- /ko -->
 */

define([
    'jquery',
    'uiComponent',
    'ko',
    'jquery/jquery-storageapi'
], function ($, Component, ko, storage) {
    'use strict';

    return Component.extend({
        /** @inheritdoc */
        initialize: function () {
            this._super();


            var cacheKey = 'customer',
                that = this;

            this.isLoggedIn = ko.observable(false);
            this.isNotLoggedIn = ko.observable(true);
            this.customer = ko.observable($.initNamespaceStorage('mage-cache-storage').localStorage.get(cacheKey));

            if(this.customer() == undefined) {
                setTimeout(function() {
                    that.customer($.initNamespaceStorage('mage-cache-storage').localStorage.get(cacheKey));

                    if (that.customer() !== undefined) {
                        that.isLoggedIn(that.customer().fullname ? true : false);
                        that.isNotLoggedIn(that.customer().fullname ? false : true);
                    }
                }, 4000);
            }

            if (this.customer() !== undefined) {
                this.isLoggedIn(this.customer().fullname ? true : false);
                this.isNotLoggedIn(this.customer().fullname ? false : true);
            }

            this.customer.subscribe(function (updatedValue) {
                that.isLoggedIn(updatedValue.fullname ? true : false);
                that.isNotLoggedIn(updatedValue.fullname ? false : true);
            });

            this.isLoaded = true;
        }
    });
});
