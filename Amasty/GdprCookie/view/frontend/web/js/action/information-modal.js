define([
    'jquery',
    'uiRegistry',
    'Amasty_GdprCookie/js/action/create-modal',
    'mage/translate'
], function ($, registry, createModal) {
    'use strict';

    return function (name, data, cssClass) {
        var modalName = this.name + name + data.groupId,
            modal = registry.get(modalName),
            button = [ {
                'text': $.mage.__('Done'),
                'class': 'amgdprcookie-done',
                'actions': [ {
                    'targetName': '${ $.name }',
                    'actionName': 'closeModal'
                } ]
            } ];

        if (modal) {
            modal.openModal();

            return;
        }

        createModal.call(
            this,
            data.cookies,
            data.name,
            cssClass + ' -table',
            button,
            'Amasty_GdprCookie/cookie-table',
            modalName,
            data.description
        );
    };
});
