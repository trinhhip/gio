<?php


namespace OmnyfyCustomzation\B2C\Plugin\Customer;


use Closure;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CusCollectFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use OmnyfyCustomzation\B2C\Helper\Data as B2CHelper;
use OmnyfyCustomzation\BuyerApproval\Helper\Data as HelperData;
use OmnyfyCustomzation\BuyerApproval\Model\Config\Source\AttributeOptions;
use OmnyfyCustomzation\BuyerApproval\Model\Config\Source\TypeNotApprove;

/**
 * Class CustomerAuthenticated
 *
 * @package OmnyfyCustomzation\BuyerApproval\Plugin
 */
class CustomerAuthenticated
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var ResponseInterface
     */
    protected $_response;

    /**
     * @var CusCollectFactory
     */
    protected $_cusCollectFactory;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var RedirectInterface
     */
    protected $_redirect;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var B2CHelper
     */
    protected $b2cHelper;

    public function __construct(
        HelperData $helperData,
        ManagerInterface $messageManager,
        ResponseFactory $response,
        CusCollectFactory $cusCollectFactory,
        Session $customerSession,
        RedirectInterface $redirect,
        StoreManagerInterface $storeManager,
        B2CHelper $b2cHelper
    )
    {
        $this->helperData = $helperData;
        $this->messageManager = $messageManager;
        $this->_response = $response;
        $this->_cusCollectFactory = $cusCollectFactory;
        $this->_customerSession = $customerSession;
        $this->_redirect = $redirect;
        $this->storeManager = $storeManager;
        $this->b2cHelper = $b2cHelper;
    }

    public function aroundAuthenticate(
        AccountManagement $subject,
        Closure $proceed,
        $username,
        $password
    )
    {
        $result = $proceed($username, $password);

        if (!$this->helperData->isEnabled()) {
            return $result;
        }

        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customerFilter = $this->_cusCollectFactory->create()
            ->addFieldToFilter('email', $username)
            ->addFieldToFilter('website_id', $websiteId)
            ->getFirstItem();

        // check old customer and set approved
        $getIsApproved = null;

        if ($customerId = $customerFilter->getId()) {
            // check new customer logedin
            $getIsApproved = $this->helperData->getIsApproved($customerId);
        }

        if ($customerId && $getIsApproved !== AttributeOptions::APPROVED &&
            !empty($getIsApproved) && $customerFilter->getGroupId() != $this->b2cHelper->getDefaultCustomerGroup()) {
            // case redirect
            $urlRedirect = $this->helperData->getUrl($this->helperData->getCmsRedirectPage(), ['_secure' => true]);
            if ($this->helperData->getTypeNotApprove() === TypeNotApprove::SHOW_ERROR
                || empty($this->helperData->getTypeNotApprove())) {
                // case show error
                $urlRedirect = $this->helperData->getUrl('customer/account/login', ['_secure' => true]);
                $this->messageManager->addErrorMessage(__($this->helperData->getErrorMessage()));
            }

            // force logout customer
            $this->_customerSession->logout()
                ->setBeforeAuthUrl($this->_redirect->getRefererUrl())
                ->setLastCustomerId($customerId);

            // processCookieLogout
            $this->helperData->processCookieLogout();

            // force redirect
            return $this->_response->create()->setRedirect($urlRedirect)->sendResponse();
        }

        return $result;
    }
}
