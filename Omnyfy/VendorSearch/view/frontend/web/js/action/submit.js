define(
    [
        'jquery',
        'mage/storage',
        'mage/apply/main'
    ],
    function ($, storage, mage) {
        'use strict';

        return function (submitUrl, data, vendorContainer, layerContainer, vendorCounterContainer) {
            /** change browser url */
            if (typeof window.history.pushState === 'function') {
                let urlPaths = submitUrl.split('?'),
                    baseUrl = urlPaths[0],
                    urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                    paramData = {},
                    parameters;

                for (let i = 0; i < urlParams.length; i++) {
                    parameters = urlParams[i].split('=');
                    paramData[parameters[0]] = parameters[1] !== undefined
                        ? window.decodeURIComponent(parameters[1].replace(/\+/g, '%20'))
                        : '';
                }
                paramData = $.param(paramData);
                let actionUrl = baseUrl + (paramData.length ? '?' + paramData : '');
                window.history.pushState({url: actionUrl}, '', actionUrl);
                $("body").trigger('processStart');
            }

            return storage.post(submitUrl, data).done(
                function (response) {
                    response = JSON.parse(response);
                    if (response.backUrl) {
                        window.location = response.backUrl;
                        return;
                    }
                    if (response.navigation) {
                        layerContainer.html(response.navigation);
                    }

                    if (response.vendors) {
                        vendorContainer.html(response.vendors);
                    }

                    if (response.vendorCounter) {
                        vendorCounterContainer.html(response.vendorCounter);
                    }

                    if (mage) {
                        mage.apply();
                    }
                }
            ).fail(
                function () {
                    window.location.reload();
                }
            ).always(function (){
                $('body').trigger('processStop');
            });

        };
    }
);
