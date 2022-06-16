define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/form',
    'amxmlsitemap_helpers'
], function (_, registry, Form, helpers) {
    'use strict';

    return Form.extend({

        /** @inheritdoc */
        initialize: function () {
            this._super();

            helpers.prepareProductFieldData(this.source.data);

            return this;
        },

        /** @inheritdoc */
        initObservable: function () {
            registry.get('index = entities', function (item) {
                this.dynamicRowsUnique = item;
            }.bind(this));

            return this._super();
        },

        /**
         * Submits form
         *
         * @param {String} redirect
         * @returns {void}
         */
        submit: function (redirect) {
            if (this.source.data.additional) {
                helpers.updateRecordData(this.source.data.additional.entities, this.dynamicRowsUnique.elems());
            }

            helpers.prepareProductFieldData(this.source.data);

            this._super(redirect);
        }
    });
});
