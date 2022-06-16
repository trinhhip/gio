define([
    'jquery'
], function ($) {
    'use strict';

    var configurableWidgetMixin = {
        _fillSelect: function (element) {
            var attributeId = element.id.replace(/[a-z]*/, ''),
                options = this._getAttributeOptions(attributeId),
                prevConfig,
                index = 1,
                allowedProducts,
                i,
                j,
                finalPrice = parseFloat(this.options.spConfig.prices.finalPrice.amount),
                optionFinalPrice,
                optionPriceDiff,
                optionPrices = this.options.spConfig.optionPrices,
                allowedOptions = [],
                indexKey,
                allowedProductMinPrice;

            this._clearSelect(element);
            element.options[0] = new Option('', '');
            element.options[0].innerHTML = this.options.spConfig.chooseText;
            prevConfig = false;

            if (element.prevSetting) {
                prevConfig = element.prevSetting.options[element.prevSetting.selectedIndex];
            }

            if (options) {
                for (indexKey in this.options.spConfig.index) {
                    /* eslint-disable max-depth */
                    if (this.options.spConfig.index.hasOwnProperty(indexKey)) {
                        allowedOptions = allowedOptions.concat(_.values(this.options.spConfig.index[indexKey]));
                    }
                }

                for (i = 0; i < options.length; i++) {
                    allowedProducts = [];
                    optionPriceDiff = 0;

                    /* eslint-disable max-depth */
                    if (prevConfig) {
                        for (j = 0; j < options[i].products.length; j++) {
                            // prevConfig.config can be undefined
                            if (prevConfig.config &&
                                prevConfig.config.allowedProducts &&
                                prevConfig.config.allowedProducts.indexOf(options[i].products[j]) > -1) {
                                allowedProducts.push(options[i].products[j]);
                            }
                        }
                    } else {
                        allowedProducts = options[i].products.slice(0);

                        if (typeof allowedProducts[0] !== 'undefined' &&
                            typeof optionPrices[allowedProducts[0]] !== 'undefined') {
                            allowedProductMinPrice = this._getAllowedProductWithMinPrice(allowedProducts);
                            optionFinalPrice = parseFloat(optionPrices[allowedProductMinPrice].finalPrice.amount);
                            optionPriceDiff = optionFinalPrice - finalPrice;
                        }
                    }

                    if (allowedProducts.length > 0 || _.include(allowedOptions, options[i].id)) {
                        options[i].allowedProducts = allowedProducts;
                        element.options[index] = new Option(this._getOptionLabel(options[i]), options[i].id);

                        if (typeof options[i].price !== 'undefined') {
                            element.options[index].setAttribute('price', options[i].price);
                        }

                        if (allowedProducts.length === 0) {
                            element.options[index].disabled = true;
                        }

                        element.options[index].config = options[i];
                        index++;
                    }

                    /* eslint-enable max-depth */
                    // Code added to select option
                    if (i == 0) {
                        this.options.values[attributeId] = options[i].id;
                    }
                }
                //Code added to check if configurations are set in url and resets them if needed
                if (window.location.href.indexOf('#') !== -1) {
                    this._parseQueryParams(window.location.href.substr(window.location.href.indexOf('#') + 1));
                }
            }

            //Hide the select field when there is only 1 option
            if (options.length === 1) {
                $(element).parents(".field.configurable").hide();
            }
        }
    };

    return function (targetWidget) {
        $.widget('mage.configurable', targetWidget, configurableWidgetMixin); // the widget alias should be like for the target widget
        return $.mage.configurable; //  the widget by parent alias should be returned
    };
});