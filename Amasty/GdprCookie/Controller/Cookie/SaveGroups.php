<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Controller\Cookie;

use Magento\Framework\Controller\Result\RawFactory;
use Amasty\GdprCookie\Api\CookieManagementInterface;
use Amasty\GdprCookie\Model\Consent\AllowedGroupFormatter;
use Amasty\GdprCookie\Model\CookieManager;
use Amasty\GdprCookie\Model\CookieConsentLogger;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

class SaveGroups implements \Magento\Framework\App\ActionInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CookieManager
     */
    private $cookieManager;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var RawFactory
     */
    private $rawFactory;

    /**
     * @var CookieConsentLogger
     */
    private $consentLogger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var AllowedGroupFormatter
     */
    private $allowedStatusFormatter;

    /**
     * @var CookieManagementInterface
     */
    private $cookieManagement;

    public function __construct(
        RequestInterface $request,
        Session $session,
        RawFactory $rawFactory,
        StoreManagerInterface $storeManager,
        CookieManager $cookieManager,
        ManagerInterface $messageManager,
        CookieConsentLogger $consentLogger,
        AllowedGroupFormatter $allowedStatusFormatter,
        CookieManagementInterface $cookieManagement
    ) {
        $this->request = $request;
        $this->session = $session;
        $this->rawFactory = $rawFactory;
        $this->storeManager = $storeManager;
        $this->cookieManager = $cookieManager;
        $this->consentLogger = $consentLogger;
        $this->messageManager = $messageManager;
        $this->allowedStatusFormatter = $allowedStatusFormatter;
        $this->cookieManagement = $cookieManagement;
    }

    public function execute()
    {
        $response = $this->rawFactory->create();
        $storeId = (int)$this->storeManager->getStore()->getId();
        $allowedCookieGroupIds = (array)$this->request->getParam('groups');

        if (!$allowedCookieGroupIds) {
            $rejectedCookieNames = array_map(function ($cookie) {
                return $cookie->getName();
            }, $this->cookieManagement->getCookies($storeId));
            $this->cookieManager->deleteCookies($rejectedCookieNames);
            $this->cookieManager->updateAllowedCookies(CookieManager::ALLOWED_NONE);

            if ($customerId = $this->session->getCustomerId()) {
                $this->consentLogger->logCookieConsent(
                    $customerId,
                    __('None cookies allowed')
                );
            }

            return $response;
        }

        if ($customerId = $this->session->getCustomerId()) {
            $consentStatus = $this->allowedStatusFormatter->format($storeId, $allowedCookieGroupIds);

            $this->consentLogger->logCookieConsent(
                $customerId,
                $consentStatus
            );
        }

        $rejectedCookieNames = array_map(function ($cookie) {
            return $cookie->getName();
        }, $this->cookieManagement->getNotAssignedCookiesToGroups($storeId, $allowedCookieGroupIds));
        $this->messageManager->addSuccessMessage(__('You saved your cookie settings!'));
        $this->cookieManager->deleteCookies($rejectedCookieNames);
        $this->cookieManager->updateAllowedCookies(implode(',', $allowedCookieGroupIds));

        return $response;
    }
}
