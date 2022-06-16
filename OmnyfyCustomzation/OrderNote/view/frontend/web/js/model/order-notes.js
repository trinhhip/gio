define([
    'ko',
    'Magento_Checkout/js/model/quote'
], function (ko,quote) {
    'use strict';
    var orderNotes = ko.observable(getOrderNotesFromQuote());

    function getOrderNotesFromQuote() {
        var itemsData = quote.getItems();
        var quoteItemNotes = [];
        itemsData.forEach(function(item) {
            quoteItemNotes[item.item_id] = item.order_note;
        });
        var noteItems = Object.assign({}, quoteItemNotes);

        return noteItems;
    }
    return {
        orderNotes: orderNotes,
        /**
         * @param {Object} data
         */
        setOrderNotes: function (data) {
            orderNotes(data);
        }
    };
});
