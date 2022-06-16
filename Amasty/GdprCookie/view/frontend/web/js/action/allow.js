/**
 * Action Allow All Cookies
 */

define([
    'jquery',
    'mage/url',
    'Amasty_GdprCookie/js/model/cookie-data-provider',
    'Amasty_GdprCookie/js/model/manageable-cookie',
    'Amasty_GdprCookie/js/action/ga-initialize'
], function ($, urlBuilder, cookieDataProvider, manageableCookie, gaInitialize) {
    'use strict';

    return function () {
        var url = urlBuilder.build('gdprcookie/cookie/allow');

        return $.ajax({
            showLoader: true,
            method: 'POST',
            url: url,
            success: function () {
                if (gaInitialize.deferrer.resolve) {
                    gaInitialize.deferrer.resolve();
                }

                cookieDataProvider.updateCookieData().done(function (cookieData) {
                    manageableCookie.updateGroups(cookieData);
                    manageableCookie.processManageableCookies();
                }).fail(function () {
                    manageableCookie.setForce(true);
                    manageableCookie.processManageableCookies();
                });
            }
        });
    };
});
