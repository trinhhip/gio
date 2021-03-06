define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/totals'
], function (Component, quote, priceUtils, totals) {
        "use strict";
        return Component.extend({
            defaults: {
                isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                template: 'Omnyfy_Mcm/checkout/summary/transactionfee'
            },
            totals: quote.getTotals(),
            isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,
            isDisplayed: function() {
                var price = 0;
                if (this.totals()) {
                    price = totals.getSegment('mcm_transaction_fee_incl_tax').value;
                }
                return this.isFullMode() && price > 0;
            },
            getValue: function() {
                var price = 0;
                if (this.totals()) {
                    price = totals.getSegment('mcm_transaction_fee_incl_tax').value;
                }
                return this.getFormattedPrice(price);
            },
            getBaseValue: function() {
                var price = 0;
                if (this.totals()) {
                    price = this.totals().mcm_transaction_fee_incl_tax;
                }
                return priceUtils.formatPrice(price, quote.getBasePriceFormat());
            }
        });
    }
);
