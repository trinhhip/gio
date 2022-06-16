/**
 * Cookie Data Provider Logic
 */
define([
    'jquery',
    'mage/url',
], function ($, urlBuilder) {
    'use strict';

    urlBuilder.setBaseUrl(window.BASE_URL);

    return {
        cookieData: [],
        updateResult: $.Deferred(),
        updateStatus: 'done',
        cookieFetchUrl: urlBuilder.build('gdprcookie/cookie/cookies'),

        getCookieData: function () {
            if (this.cookieData.length === 0) {
                return this.updateCookieData();
            }

            return $.Deferred().resolve(this.cookieData);
        },

        updateCookieData: function () {
            if (this.updateStatus === 'pending') {
                return this.updateResult;
            }

            this.updateStatus = 'pending';
            this.updateResult = $.Deferred();

            $.ajax({
                url: this.cookieFetchUrl,
                type: 'GET',
                cache: true,
                dataType: 'json',
                data: {
                    allowed: $.cookie('amcookie_allowed'),
                    restriction: $.cookie('amcookie_policy_restriction')
                },
                success: function (cookieData) {
                    this.updateStatus = 'done';

                    if (cookieData.cookiePolicy !== undefined) {
                        $.cookie('amcookie_policy_restriction', cookieData.cookiePolicy, {expires: 10});
                    }

                    if (cookieData.cookiePolicy === 'allowed') {
                        this.cookieData = cookieData;
                        this.updateResult.resolve(this.cookieData);
                    } else {
                        this.updateResult.reject();
                    }
                }.bind(this)
            });

            return this.updateResult;
        }
    }
});
