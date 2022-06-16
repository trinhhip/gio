define([
    'Magento_Ui/js/form/element/date'
], function(Date) {
    'use strict';

    return Date.extend({
        defaults: {
            options: {
                showsDate: true,
                showsTime: false,
                changeYear: false
            },

            elementTmpl: 'ui/form/element/date'
        }
    });
});