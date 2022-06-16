define([
    'jquery',
    'uiComponent',
    'underscore'
], function ($, Component, _) {
    'use strict';
    
    return Component.extend({
        gtmAttrRegex: /^data-gtm-(.*)/g,
        
        eventAttr:    'data-gtm-event',
        listAttr:     'data-gtm-item_list_id',
        listNameAttr: 'data-gtm-item_list_name',
        productAttr:  'data-gtm-item_id',
        
        eventSelector:  '',
        listSelector:  '',
        productSelector:  '',
        
        sentLists: [],
        
        initialize: function () {
            this.eventSelector   = '[' + this.eventAttr + ']';
            this.listSelector    =  '[' + this.listAttr + ']';
            this.productSelector = '[' + this.productAttr + ']';
            
            this._super();
        },
        
        getProductAttribute: function () {
            return this.productAttr;
        },
        
        getListAttribute: function () {
            return this.listAttr;
        },
        
        getListNameAttribute: function () {
            return this.listNameAttr;
        },
        
        getEventAttribute: function () {
            return this.eventAttr;
        },
        
        getEventSelector: function () {
            return this.eventSelector;
        },
        
        getListSelector: function () {
            return this.listSelector;
        },
        
        getProductSelector: function () {
            return this.productSelector;
        },
        
        getAttributeRegex: function () {
            return this.gtmAttrRegex;
        }
    });
});
