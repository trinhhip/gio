<?php
/**
 * Project: Approval
 * User: jing
 * Date: 2019-08-19
 * Time: 12:07
 */
namespace Omnyfy\Approval\Observer;

use Magento\Backend\App\Area\FrontNameResolver;

class ProductSaveBefore implements \Magento\Framework\Event\ObserverInterface
{
    protected $_state;

    protected $_session;

    protected $_helper;
    /**
     * @var \Omnyfy\Approval\Model\Resource\Product\CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var \Omnyfy\Approval\Model\ProductFactory
     */
    private $productFactory;

    public function __construct(
        \Magento\Framework\App\State $state,
        \Omnyfy\Approval\Helper\Data $helper,
        \Omnyfy\Approval\Model\Resource\Product\CollectionFactory $collectionFactory,
        \Omnyfy\Approval\Model\ProductFactory $productFactory
    ) {
        $this->_state = $state;
        $this->_helper = $helper;
        $this->collectionFactory = $collectionFactory;
        $this->productFactory = $productFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        //$eventName = $observer->getEvent()->getName();
        $product = $observer->getData('data_object');

        if ($product->getId()) {
            $collection = $this->collectionFactory->create()->addFieldToFilter('product_id',$product->getId())->getItems();
            foreach ($collection as $item) {
                $model = $this->productFactory->create();
                $model->load($item->getData('id'));
                $model->setData('sku', $product->getSku());
                $model->setData('product_name', $product->getName());
                $model->save();
            }
        }

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

        //check if it's already in approval table, if it is return without doing anything
        if ($product->getId()) {
            //search record by product_id and vendor_id
            $record = $this->_helper->getProductRecord($product->getId(), $vendorInfo['vendor_id']);
            if (!empty($record)) {
                return;
            }
        }

        $product->setData('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
        $product->setData('approval_status', \Omnyfy\Approval\Model\Source\Status::STATUS_IN_PUBLISHING);
    }

    protected function getBackendSession()
    {
        if (null == $this->_session) {
            $this->_session = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Backend\Model\Session::class);
        }
        return $this->_session;
    }
}
