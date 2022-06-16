define([
    'jquery'
], function ($) {
    'use strict';
    return {
        /**
         * @param {Object} data
         */
        getInputData: function () {
            var noteItemsArr = [];
            $('textarea[name^="note-item-ids"]').each(function() {
                var dataId = $(this).attr("data_note_item_id");
                noteItemsArr[dataId] = $(this).val();
            });
            var noteItems = Object.assign({}, noteItemsArr);

            return noteItems;
        }
    };
});
