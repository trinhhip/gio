require([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'mage/translate'
], function($, confirmation, $t) {
    $(document).ready(function () {
        const STRIPE_CHECKOUT_SUCCESS_COOKIE = 'Stripe_Checkout_Success';
        let isStripeCheckoutSuccess = $.cookie(STRIPE_CHECKOUT_SUCCESS_COOKIE);
        let content = 'Your Subscription has been updated successfully';
        if(isStripeCheckoutSuccess){
            confirmation({
                title: 'Vendor subscription',
                content: '<div class="message message-success">' + content + '</div>',
                modalClass: 'confirm',
                buttons: [
                    {
                        text: $t('Close'),
                        class: 'action-secondary action-dismiss',

                        click: function (event) {
                            this.closeModal(event);
                        }
                    }
                ]
            });
            document.cookie = `${STRIPE_CHECKOUT_SUCCESS_COOKIE}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;`;
        }
    });
});