define([
    'jquery',
    'uiComponent',
    'underscore',
    'Mirasvit_GoogleTagManager/js/event/select-item',
    'Mirasvit_GoogleTagManager/js/event/view-item-list',
    'Mirasvit_GoogleTagManager/js/event/select-promotion',
    'Mirasvit_GoogleTagManager/js/event/view-promotion'
], function ($, Component, _, SelectItem, ItemList, SelectPromotion, ViewPromotion) {
    'use strict';
    
    return Component.extend({
        initialize: function () {
            this._super();
    
            SelectItem();
            ItemList();
            SelectPromotion();
            ViewPromotion();
        }
    });
});
