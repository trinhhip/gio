<?php
/**
 * Project: Vendor Subscription
 * User: jing
 * Date: 2019-07-01
 * Time: 17:42
 */
namespace Omnyfy\VendorSubscription\Model\Resource\Subscription;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $appState;

    protected $_session;

    protected function _construct()
    {
        $this->_init('Omnyfy\VendorSubscription\Model\Subscription', 'Omnyfy\VendorSubscription\Model\Resource\Subscription');
    }

    protected function _renderFiltersBefore()
    {
        parent::_renderFiltersBefore();

        if (\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE != $this->getAppState()->getAreaCode()) {
            return;
        }

        $vendorInfo = $this->getBackendSession()->getVendorInfo();

        if (empty($vendorInfo)) {
            return;
        }

        $this->addVendorFilter($vendorInfo['vendor_id']);
    }

    protected function getAppState()
    {
        if (null == $this->appState) {
            $this->appState = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\App\State::class);
        }
        return $this->appState;
    }

    protected function getBackendSession()
    {
        if (null == $this->_session) {
            $this->_session = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Backend\Model\Session::class);
        }
        return $this->_session;
    }

    public function addVendorFilter($vendorId)
    {
        if (!$this->getFlag('has_vendor_filter')) {
            $this->addFieldToFilter('vendor_id', $vendorId);

            $this->setFlag('has_vendor_filter', 1);
        }

        return $this;
    }
}
 