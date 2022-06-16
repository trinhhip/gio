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
            $(document).ready(function() {
                _.each($(this.eventSelector), function (el) {
                    let $el = $(el);
                    
                    if (this.isVisible($el)) {
                        this.sendData($el);
                    }
                }.bind(this));
            }.bind(this));
            
            $(window).scroll(function() {
                this.sendData($(this.eventSelector), null);
            }.bind(this));
    
            $('body').on('contentUpdated', function () {
                this.sendData($(this.eventSelector), null);
            }.bind(this));
        },
        
        sendData: function ($el) {
            _.each($el, function (eventEl, i) {
                let $eventEl = $(eventEl);
        
                // set identifier or each promo
                if (!$eventEl.attr(this.promolistIdAttr)) {
                    $eventEl.attr(this.promolistIdAttr, i);
                }
        
                if (this.isVisible($eventEl)) {
                    this.setGaData($eventEl, null);
                }
            }.bind(this));
        },
        
        setGaData: function ($listEl, productIds) {
            let itemIdSelector   = '[' + this.itemIdAttr + ']',
                itemNameSelector = '[' + this.itemNameAttr+ ']';
    
            let $eventEl     = $listEl;
            let $itemIdEls   = $(itemIdSelector, $eventEl);
            let $itemNameEls = $(itemNameSelector, $eventEl);
            
            let listId = $eventEl.attr(this.promolistIdAttr);
    
            let $itemsEls;
            if ($itemIdEls.length) {
                $itemsEls = $itemIdEls;
            } else {
                $itemsEls = $itemNameEls;
            }
            
            if (typeof this.sentLists[listId] == 'undefined' || !this.sentLists[listId]) {
                this.sentLists[listId] = true;
                
                let items = [];
    
                _.each($itemsEls, function ($el) {
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
                    'ecommerce': {
                        'promoView':  {
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
                
                let ga4Data = {
                    0: 'event',
                    1: 'view_promotion',
                    2: eventItems
                };
                
                window.dataLayer.push({ecommerce: null});  // Clear the previous ecommerce object.
                gtag('event', 'view_promotion', eventItems);
            }
        }
    });
});
