<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Observer\Customer;

use Amasty\GdprCookie\Model\Consent\AllowedGroupFormatter;
use Amasty\GdprCookie\Model\CookieConsentLogger;
use Amasty\GdprCookie\Model\CookieManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;

class Login implements ObserverInterface
{
    /**
     * @var CookieManager
     */
    private $cookieManager;

    /**
     * @var CookieConsentLogger
     */
    private $consentLogger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AllowedGroupFormatter
     */
    private $allowedStatusFormatter;

    public function __construct(
        CookieManager $cookieManager,
        CookieConsentLogger $consentLogger,
        StoreManagerInterface $storeManager,
        AllowedGroupFormatter $allowedStatusFormatter
    ) {
        $this->cookieManager = $cookieManager;
        $this->consentLogger = $consentLogger;
        $this->storeManager = $storeManager;
        $this->allowedStatusFormatter = $allowedStatusFormatter;
    }

    /**
     * @param Observer $observer
     *
     * @return $this|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $allowedCookieGroups = $this->cookieManager->getAllowCookies();

        if ($allowedCookieGroups === null) {
            return;
        }

        if (!in_array($allowedCookieGroups, [CookieManager::ALLOWED_ALL, CookieManager::ALLOWED_NONE])) {
            $storeId = (int)$this->storeManager->getStore()->getId();
            $status = $this->allowedStatusFormatter->format($storeId, explode(',', $allowedCookieGroups));
        } elseif ($allowedCookieGroups === CookieManager::ALLOWED_NONE) {
            $status = __('None cookies allowed');
        } else {
            $status = __('All Allowed');
        }

        $customerId = $observer->getData('customer')->getData('entity_id');
        $this->consentLogger->logCookieConsent($customerId, $status);
    }
}
