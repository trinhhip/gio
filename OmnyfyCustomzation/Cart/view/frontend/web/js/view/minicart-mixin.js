define([
    'jquery'
], function ($) {
    'use strict';


    var sidebarInitialized = false,
        addToCartCalls = 0,
        miniCart;

    miniCart = $('[data-block=\'minicart\']');

    return function (Component) {
        return Component.extend({

            /**
             * @override
             */
            initialize: function () {
                var self = this;

                miniCart.on('dropdowndialogopen', function () {
                    $('body').addClass('minicart-open');
                    $("html").css("overflow", "hidden");
                });

                miniCart.on('dropdowndialogclose', function () {
                    $('body').removeClass('minicart-open');
                    $("html").css("overflow", "auto");
                });

                return this._super();
            }
        });
    }
});
