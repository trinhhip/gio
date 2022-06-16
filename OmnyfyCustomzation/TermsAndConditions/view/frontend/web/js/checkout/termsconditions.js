define(
    [
        'ko',
        'jquery',
        'uiComponent'
    ],
    function (ko, $, Component) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'OmnyfyCustomzation_TermsAndConditions/checkout/termsconditions'
            },

            initObservable: function () {
                this._super().observe({
                    Accept: ko.observable(false)
                });

                this.Accept.subscribe(function (value) {
                    var terms = $('.checkout-agreements-block');
                    if (value) {
                        terms.addClass('checked');
                    } else {
                        terms.removeClass('checked');
                    }
                });

                return this;
            }
        });
    });
