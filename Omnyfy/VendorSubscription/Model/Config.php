<?php
/**
 * Project: Vendor Subscription
 * User: jing
 * Date: 2/10/19
 * Time: 5:38 pm
 */
namespace Omnyfy\VendorSubscription\Model;

class Config
{
    const CANCEL_SUBSCRIPTION_ADMIN_TEMPLATE = 'omnyfy_subscription/general/cancel_template_admin';

    const CANCEL_SUBSCRIPTION_VENDOR_TEMPLATE = 'omnyfy_subscription/general/cancel_template_vendor';

    const INVOICE_VENDOR_TEMPLATE = 'omnyfy_subscription/general/invoice_template_vendor';

    const INVOICE_ADMIN_TEMPLATE = 'omnyfy_subscription/general/invoice_template_admin';

    const UPDATE_SUBSCRIPTION_VENDOR_TEMPLATE = 'omnyfy_subscription/general/subscription_update_template_vendor';

    const UPDATE_SUBSCRIPTION_ADMIN_TEMPLATE = 'omnyfy_subscription/general/subscription_update_template_admin';

    const INVOICE_FAILED_VENDOR_TEMPLATE = 'omnyfy_subscription/general/invoice_failed_vendor';

    const INVOICE_FAILED_ADMIN_TEMPLATE = 'omnyfy_subscription/general/invoice_failed_mo';

    const SUBSCRIPTION_EXPIRY_VENDOR_TEMPLATE = 'omnyfy_subscription/general/subscription_expiry_vendor';

    const SUBSCRIPTION_EXPIRY_ADMIN_TEMPLATE = 'omnyfy_subscription/general/subscription_expiry_mo';

    const XML_PATH_ADMIN_EMAIL = 'trans_email/ident_support/email';

    const XML_PATH_ADMIN_NAME = 'trans_email/ident_support/name';
}
