<?php
namespace Omnyfy\Stripe\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Omnyfy\Stripe\Model\ResourceModel\VendorConnectAccount;
use Omnyfy\Vendor\Model\Resource\Vendor\Collection as VendorCollection;
use Omnyfy\Vendor\Model\VendorFactory;
use StripeIntegration\Payments\Helper\Generic;
use Magento\Backend\Model\Session as BackendSession;

class Data extends AbstractHelper
{
    /**
     * @var \StripeIntegration\Payments\Helper\Generic
     */
    protected $paymentsHelper;

    /**
     * CookieManager
     *
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var Gateway
     */
    private $gatewayHelper;

    /**
     * @var VendorFactory
     */
    private $vendorFactory;

    /**
     * @var VendorConnectAccount
     */
    private $vendorConnectAccount;

    /**
     * @var BackendSession
     */
    private $backendSession;

    /**
     * @var VendorCollection
     */
    private $vendorCollection;

    /**
     * @param Context $context
     * @param Generic $paymentsHelper
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param SessionManagerInterface $sessionManager
     * @param Gateway $gatewayHelper
     * @param VendorFactory $vendorFactory
     * @param VendorConnectAccount $vendorConnectAccount
     * @param BackendSession $backendSession
     * @param VendorCollection $vendorCollection
     */
    public function __construct(
        Context $context,
        Generic $paymentsHelper,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        SessionManagerInterface $sessionManager,
        Gateway $gatewayHelper,
        VendorFactory $vendorFactory,
        VendorConnectAccount $vendorConnectAccount,
        BackendSession $backendSession,
        VendorCollection $vendorCollection
    ) {
        parent::__construct($context);
        $this->paymentsHelper = $paymentsHelper;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionManager = $sessionManager;
        $this->gatewayHelper = $gatewayHelper;
        $this->vendorFactory = $vendorFactory;
        $this->vendorConnectAccount = $vendorConnectAccount;
        $this->backendSession = $backendSession;
        $this->vendorCollection = $vendorCollection;
    }

    /**
     * @param array $object
     * @param string $vendorId
     * @param string $accountRef
     * @return array
     */
    public function processDataForAccountEvent($object, $vendorId, $accountRef)
    {
        $eventData = [
            'vendor_id' => $vendorId,
            'status' => 'pending',
            'account_ref' => $accountRef
        ];
        if (empty($object['payouts_enabled']) || empty($object['capabilities']['transfers'])) {
            return $eventData;
        }
        if ($object['payouts_enabled'] == true && $object['capabilities']['transfers'] == 'active') {
            $eventData['status'] = 'approved';
        }
        return $eventData;
    }

    /**
     * @param float $amount
     * @param string $currency
     * @return false|float
     */
    public function convertStripeAmountToMagentoAmount($amount, $currency)
    {
        $cents = 100;
        if ($this->paymentsHelper->isZeroDecimal($currency))
            $cents = 1;

        return round($amount / $cents);
    }

    /**
     * Get form key cookie
     *
     * @param string $cookieName
     * @return string
     */
    public function getCookie($cookieName)
    {
        return $this->cookieManager->getCookie($cookieName);
    }

    /**
     * @param string $cookieName
     * @param string $value
     * @param int $duration
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     * @return void
     */
    public function setCookie($cookieName, $value, $duration = 86400)
    {
        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration($duration)
            ->setPath($this->sessionManager->getCookiePath())
            ->setDomain($this->sessionManager->getCookieDomain());

        $this->cookieManager->setPublicCookie(
            $cookieName,
            $value,
            $metadata
        );
    }

    /**
     * @param string $cookieName
     * @param int $duration
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     * @return void
     */
    public function deleteCookie($cookieName, $duration = 86400)
    {
        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration($duration)
            ->setPath($this->sessionManager->getCookiePath())
            ->setDomain($this->sessionManager->getCookieDomain());

        $this->cookieManager->deleteCookie(
            $cookieName,
            $metadata
        );
    }

    /**
     * @param string $stripeCode
     * @throws \Exception
     */
    public function handleStripeAccountAuthCode($stripeCode)
    {
        $connectedAccountId = $this->gatewayHelper->getStripeAccountId($stripeCode);
        $vendorInfo = $this->backendSession->getVendorInfo();

        if (empty($vendorInfo) || empty($vendorInfo['vendor_id'])) {
            $vendorId = $this->_getRequest()->getParam('vendorid');
        } else {
            $vendorId = $vendorInfo['vendor_id'];
        }
        $vendorModel = null;
        $stripeUser = $this->gatewayHelper->getUserById($connectedAccountId);
        if (!$vendorId && !empty($stripeUser) && !empty($stripeUser['email'])) {
            $vendorModel = $this->vendorCollection->addFilter('email', $stripeUser['email'])->getFirstItem();
            $vendorId = !$vendorModel ? 0 : $vendorModel->getId();
        }

        if (($connectedAccountId != null) && $vendorId) {
            if (empty($vendorModel)) {
                $vendorModel = $this->vendorFactory->create()->load($vendorId);
            }
            $vendorModel->setData('stripe_account_code', $connectedAccountId);
            $vendorModel->save();
            $bankAccount = $this->gatewayHelper->getBankAccountByUserId($connectedAccountId);
            $bankAccountId = $bankAccount['id'];
            $accountInfo = [
                'stripe_account_id' => $connectedAccountId,
                'bank_account_id' => $bankAccountId
            ];
            $eventData = $this->processDataForAccountEvent(
                $stripeUser,
                $vendorId,
                $connectedAccountId
            );
            $this->_eventManager->dispatch('omnyfy_vendorsignup_kyc_status_update',
                $eventData
            );
            if ($eventData['status'] == 'approved') {
                $this->vendorConnectAccount->updateVendorPayout($vendorId, $accountInfo);
            }

        } else {
            throw new \Exception('Stripe connect only use for vendor users');
        }
    }
}