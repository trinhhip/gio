/**
 * Add callbacks to the Fieldset component for show/hide inner fieldset
 */

define([
    'Magento_Ui/js/form/components/fieldset'
], function (Fieldset) {
    'use strict';

    return Fieldset.extend({
        /**
         * Show Fieldset
         *
         * @returns {void}
         */
        show: function () {
            this.visible(true);
        },

        /**
         * Hide Fieldset
         *
         * @returns {void}
         */
        hide: function () {
            this.visible(false);
        }
    });
});
