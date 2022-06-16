/**
 * Action Save Cookie
 */

define([
    'jquery',
    'underscore',
    'mage/url',
    'Amasty_GdprCookie/js/model/cookie-data-provider',
    'Amasty_GdprCookie/js/model/cookie',
    'Amasty_GdprCookie/js/model/manageable-cookie',
    'Amasty_GdprCookie/js/action/ga-initialize'
], function (
    $,
    _,
    urlBuilder,
    cookieDataProvider,
    cookieModel,
    manageableCookie,
    gaInitialize
) {
    'use strict';

    var options = {
        selectors: {
            formContainer: '[data-amcookie-js="form-cookie"]',
            toggleFieldSelector: '[data-amcookie-js="field"]'
        },
        googleAnalyticsCookieName: '_ga'
    };

    return function (element, formData) {
        var url = urlBuilder.build('gdprcookie/cookie/savegroups'),
            disabledFields = $(options.selectors.toggleFieldSelector + ':disabled'),
            form = $(element).closest(options.selectors.formContainer);

        if (_.isUndefined(formData)) {
            disabledFields.removeAttr('disabled');
            formData = form.serialize();
        }

        return $.ajax({
            showLoader: true,
            method: 'POST',
            url: url,
            data: formData,
            success: function () {
                disabledFields.attr('disabled', true);
                cookieModel.triggerSave();
                cookieDataProvider.updateCookieData().done(function (cookieData) {
                    manageableCookie.updateGroups(cookieData);
                    manageableCookie.processManageableCookies();
                }).fail(function () {
                    manageableCookie.setForce(true);
                    manageableCookie.processManageableCookies();
                });

                if (cookieModel.isCookieAllowed(options.googleAnalyticsCookieName) && gaInitialize.deferrer.resolve) {
                    gaInitialize.deferrer.resolve();
                }
            }
        });
    };
});
