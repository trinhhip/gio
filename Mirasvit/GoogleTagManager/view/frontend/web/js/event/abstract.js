define([
    'jquery',
    'uiComponent',
    'underscore',
    'Mirasvit_GoogleTagManager/js/event/event-selectors'
], function ($, Component, _, EventSelectors) {
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
            this.gtmAttrRegex    = EventSelectors().getAttributeRegex();
            this.eventAttr       = EventSelectors().getEventAttribute();
            this.listAttr        = EventSelectors().getListAttribute();
            this.listNameAttr    = EventSelectors().getListNameAttribute();
            this.productAttr     = EventSelectors().getProductAttribute();
            this.listSelector    = EventSelectors().getListSelector();
            this.productSelector = EventSelectors().getProductSelector();
            
            this._super();
            
            this.initEventSelector();
            
            window.dataLayer = window.dataLayer || [];
            
            this.listeners();
        },
    
        initEventSelector: function () {
            this.eventSelector = EventSelectors().getEventSelector();
        },
        
        listeners: function () {
        
        },
        
        sendData: function ($el) {
        
        },
        
        setGaData: function ($listEl, productIds) {
        
        },
        
        isVisible: function ($el) {
            if (!$el.is(":visible")) {
                return false;
            }
            
            const $win = $(window);
            
            const elementTop = $el.offset().top;
            const elementBottom = elementTop + $el.outerHeight();
            
            const viewportTop = $win.scrollTop();
            const viewportBottom = viewportTop + $win.height();
            
            return elementBottom > viewportTop && elementTop < viewportBottom;
        },
        
        getProductAttribute: function () {
            return this.productAttr;
        },
        
        getProductSelector: function () {
            return this.productSelector;
        }
    });
});
