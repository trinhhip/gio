define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function ($, message) {
    'use strict';

    return function (deferred) {
        return {
            modalConfig: {
                closed: function () {
                    return deferred.resolve('continue-regeneration');
                },
                buttons: [{
                    text: $.mage.__('Yes'),
                    class: 'secondary',
                    click: function () {
                        this.closeModal();
                        return deferred.resolve('cancel-regeneration');
                    }
                }, {
                    text: $.mage.__('No'),
                    class: 'primary',
                    click: function () {
                        this.closeModal();
                        return deferred.resolve('continue-regeneration');
                    }
                }],
                content: $.mage.__('This action will lead to the termination of the regeneration process. ') +
                    $.mage.__('Do you really want to stop the process of regeneration?')
            },

            showCancelAlert: function () {
                message(this.modalConfig);
            }
        }
    }
});
