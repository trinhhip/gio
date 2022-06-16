define([
    'jquery',
    'underscore',
    'Mirasvit_GoogleTagManager/js/event/abstract'
], function ($, _, AbstractEvent) {
    'use strict';
    
    return AbstractEvent.extend({
        creativeNameAttr: 'data-gtm-creative_name',
        creativeSlotAttr: 'data-gtm-creative_slot',
        
        promolistIdAttr: 'data-gtml-promo-list-id',
        
        itemIdAttr:   'data-gtm-item_id',
        itemNameAttr: 'data-gtm-item_name',
        
        initEventSelector: function () {
            this.eventSelector = '[' + this.eventAttr + '="view_promotion"]';
        },
        
        listeners: function () {
            let itemIdSelector   = '[' + this.itemIdAttr + ']',
                itemNameSelector = '[' + this.itemNameAttr+ ']';
            
            $(document).ready(function() {
                $('body').on('click', this.eventSelector + ' ' + itemIdSelector, function (e) {
                    let $currentEl = $(e.currentTarget);
                    
                    this.sendData($currentEl);
                    
                }.bind(this));
                
                $('body').on('click', this.eventSelector + ' ' + itemNameSelector, function (e) {
                    let $currentEl = $(e.currentTarget);
                    
                    this.sendData($currentEl);
                    
                }.bind(this));
            }.bind(this));
        },
        
        sendData: function ($el) {
            this.setGaData($el, null);
        },
        
        setGaData: function ($listEl, productIds) {
            let $itemEl  = $listEl;
            let $eventEl = $itemEl.closest(this.eventSelector);
            
            let items = [];
            
            _.each($itemEl, function ($el) {
                let item = [];
                
                _.each($el.attributes, function (attr) {
                    if (attr.name.match(this.gtmAttrRegex)) {
                        let attrName = attr.name.replace(this.gtmAttrRegex, '$1');
                        
                        item[attrName] = attr.value;
                    }
                }.bind(this));
                
                items.push(item);
            }.bind(this));
            
            let ga3Data = {
                'event':     'promotionClick',
                'ecommerce': {
                    'promoClick':  {
                        promotions: items
                    }
                }
            };
            
            window.dataLayer.push({ecommerce: null});  // Clear the previous ecommerce object.
            window.dataLayer.push(ga3Data);
            
            let eventItems = {items: items};
            
            _.each($eventEl[0].attributes, function (attr) {
                if (attr.name.match(this.gtmAttrRegex)) {
                    let attrName = attr.name.replace(this.gtmAttrRegex, '$1');
                    
                    eventItems[attrName] = attr.value;
                }
            }.bind(this));
            
            window.dataLayer.push({ecommerce: null});  // Clear the previous ecommerce object.
            gtag('event', 'select_promotion',eventItems);
        }
    });
});
