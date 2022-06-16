define([
    'underscore',
    'Magento_Ui/js/dynamic-rows/dynamic-rows',
    'amxmlsitemap_helpers'
], function (_, dynamicRowsComponent, helpers) {
    'use strict';

    return dynamicRowsComponent.extend({
        defaults: {
            availableEntities: {},
            listens: {
                '${ $.provider }:availableEntities': 'onAvailableEntitiesChanged'
            },
            exports: {
                availableEntities: '${ $.provider }:availableEntities'
            },
            imports: {
                availableEntities: '${ $.provider }:availableEntities'
            }
        },

        /**
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe('availableEntities')
                .observe('addButton');

            this.availableEntities.subscribe(this.onAvailableEntitiesChanged.bind(this));

            return this;
        },

        /**
         * @param {Array} entities
         * @returns {void}
         */
        onAvailableEntitiesChanged: function (entities) {
            var hasAvailableItems = _.find(entities, function (entity) {
                return entity.allowed === true;
            });

            this.addButton(!_.isUndefined(hasAvailableItems));
        },

        /**
         * @param {String|Number} index
         * @param {Number} recordId
         * @returns {void}
         */
        processingDeleteRecord: function (index, recordId) {
            var entities,
                entityCode;

            helpers.updateRecordData(this.recordData(), this.elems());

            entities = this.availableEntities();
            entityCode = this.recordData()[recordId]['entity_code'];
            entities[entityCode].allowed = true;

            this._super(index, recordId);
            this.availableEntities(entities);
            this.changed(true);
        }
    });
});
