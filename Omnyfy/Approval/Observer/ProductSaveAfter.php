<?php
/**
 * Project: Approval
 * User: jing
 * Date: 2019-08-19
 * Time: 12:41
 */

/**
 * Project: Approval
 * User: jing
 * Date: 2019-08-19
 * Time: 12:07
 */

namespace Omnyfy\Approval\Observer;

use Magento\Backend\App\Area\FrontNameResolver;

class ProductSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    protected $_state;

    protected $_session;

    protected $_helper;

    protected $_emailHelper;

    protected $storeManager;

    public function __construct(
        \Magento\Framework\App\State $state,
        \Omnyfy\Approval\Helper\Data $helper,
        \Omnyfy\Core\Helper\Email $_emailHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->_state = $state;
        $this->_helper = $helper;
        $this->_emailHelper = $_emailHelper;
        $this->storeManager = $storeManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //$eventName = $observer->getEvent()->getName();
        $product = $observer->getData('data_object');

        //if it's not in admin area, do nothing
        if (FrontNameResolver::AREA_CODE != $this->_state->getAreaCode()) {
            return;
        }

        //if it's admin, do nothing
        $vendorInfo = $this->getBackendSession()->getVendorInfo();
        if (empty($vendorInfo)) {
            return;
        }

        if (!$this->_helper->isModuleOutputEnabled()) {
            return;
        }

        //check config
        if (!$this->_helper->isEnabled()) {
            return;
        }

        //search record by product_id and vendor_id
        $record = $this->_helper->getProductRecord($product->getId(), $vendorInfo['vendor_id']);
        if (!empty($record)) {
            //Do nothing if already approved
            return;
        }

        // SAVE product_id, vendor_id into pending approve table,
        // vendor update product will reset
        $this->_helper->saveProductRecord(
            $product->getId(),
            $product->getSku(),
            $vendorInfo['vendor_id'],
            \Omnyfy\Approval\Model\Source\Status::STATUS_IN_PUBLISHING,
            $product->getName(),
            $vendorInfo['vendor_name']
        );

        //TODO: UPDATE product approval status
        $product->setData('approval_status', \Omnyfy\Approval\Model\Source\Status::STATUS_IN_PUBLISHING);
        $product->getResource()->saveAttribute($product, 'approval_status');
    }

    protected function getBackendSession()
    {
        if (null == $this->_session) {
            $this->_session = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Backend\Model\Session::class);
        }
        return $this->_session;
    }
}

 
