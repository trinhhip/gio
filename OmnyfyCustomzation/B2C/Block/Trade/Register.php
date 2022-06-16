<?php


namespace OmnyfyCustomzation\B2C\Block\Trade;


use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\View\Element\Template\Context;

class Register extends \Magento\Customer\Block\Form\Register
{
    public function __construct(
        Context $context,
        Data $directoryHelper,
        EncoderInterface $jsonEncoder,
        Config $configCacheType,
        RegionCollectionFactory $regionCollectionFactory,
        CollectionFactory $countryCollectionFactory,
        Manager $moduleManager,
        Session $customerSession,
        Url $customerUrl,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $moduleManager,
            $customerSession,
            $customerUrl,
            $data
        );
    }

    public function getPostActionToTrade()
    {
        $postAction = $this->getCustomerEmail() ? 'customer/account/loginpost' : 'buyer/trade/totrade';
        return $this->_urlBuilder->getUrl($postAction);
    }

    public function getPostActionCreate()
    {
        $postAction = $this->isLoggedIn() ? 'buyer/trade/updateTrade' : 'buyer/trade/createpost';
        return $this->_urlBuilder->getUrl($postAction);
    }

    public function getCustomerEmail()
    {
        return $this->getRequest()->getParam('email');
    }

    public function getCustomer()
    {
        return $this->isLoggedIn() ? $this->_customerSession->getCustomer() : null;
    }

    public function isLoggedIn()
    {
        return $this->_customerSession->isLoggedIn();
    }
}