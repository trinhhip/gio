<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */

declare(strict_types=1);

namespace Amasty\Gdpr\Plugin\ConsentValidation\Checkout\Api;

use Amasty\Gdpr\Model\Consent\RegistryConstants;
use Amasty\Gdpr\Model\Consent\Validator;
use Amasty\Gdpr\Model\ConsentLogger;
use Magento\Framework\Exception\LocalizedException;

class GuestPaymentInformationManagementInterfacePlugin
{
    /**
     * @var Validator
     */
    private $validator;

    public function __construct(
        Validator $validator
    ) {
        $this->validator = $validator;
    }

    public function aroundSavePaymentInformationAndPlaceOrder(
        \Magento\Checkout\Model\GuestPaymentInformationManagement $subject,
        callable $proceed,
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        $additionalData = $paymentMethod->getAdditionalData();
        $consentData = [];
        if (isset($additionalData[RegistryConstants::CONSENTS])) {
            $consentData = json_decode($additionalData[RegistryConstants::CONSENTS], true);
        }

        if (!$this->validator->validate(ConsentLogger::FROM_CHECKOUT, $consentData)) {
            throw new LocalizedException(__('Policy Confirmation Required'));
        }

        return $proceed($cartId, $email, $paymentMethod, $billingAddress);
    }
}
