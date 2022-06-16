define([
    'jquery',
    'OmnyfyCustomzation_B2C/js/action/retail-create'
], function ($, retailCreate) {
    'use strict';
    var mixin = {
        requestToTradeUrl: window.authenticationPopup.requestToTradeUrl,
        countryOptions: window.authenticationPopup.countryOptions,

        defaults: {
            template: 'OmnyfyCustomzation_B2C/authentication-popup'
        },

        retailRegister: function (formUiElement, event) {
            var registerData = {},
                formElement = $(event.currentTarget),
                formDataArray = formElement.serializeArray();

            event.stopPropagation();
            formDataArray.forEach(function (entry) {
                registerData[entry.name] = entry.value;
            });

            if (formElement.validation() &&
                formElement.validation('isValid')
            ) {
                retailCreate(registerData)
            }

            return false;
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
