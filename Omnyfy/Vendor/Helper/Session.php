<?php
/**
 * Project: Multi Vendor.
 * User: jing
 * Date: 27/8/18
 * Time: 2:51 PM
 */
namespace Omnyfy\Vendor\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Customer\Model\SessionFactory as CustomerSession;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Customer\Model\Context as CustomerContext;

class Session extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_customerSession;
    protected $httpContext;

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        HttpContext $httpContext
    ) {
        parent::__construct($context);
        $this->_customerSession = $customerSession;
        $this->httpContext = $httpContext;
    }

    public function getSessionLocationId()
    {
        return $this->_customerSession->create()->getLocationId();
    }

    public function getSessionVendorId()
    {
        return $this->_customerSession->create()->getVendorId();
    }

    public function getShipFromWarehouseFlag()
    {
        $flag = $this->_customerSession->create()->getShipFromWarehouseFlag();
        return empty($flag) ? false : true;
    }

    public function getCustomer() {
        return $this->_customerSession->create()->getCustomer();
    }

    public function isLoggedIn() {
        return (bool)$this->httpContext->getValue(CustomerContext::CONTEXT_AUTH);
    }
}
