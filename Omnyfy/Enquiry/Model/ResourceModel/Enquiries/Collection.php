<?php


namespace Omnyfy\Enquiry\Model\ResourceModel\Enquiries;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Omnyfy\Enquiry\Model\Enquiries',
            'Omnyfy\Enquiry\Model\ResourceModel\Enquiries'
        );
    }

    /**
     * @return $this|void
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            ['ve' => $this->getTable('omnyfy_vendor_vendor_entity')],
            'main_table.vendor_id = ve.entity_id',
            [
                'vendor_name'  => 've.name',
                'vendor_email' => 've.email',
                'full_name'    => 'CONCAT(main_table.customer_first_name," ", main_table.customer_last_name)',
                'product_name' => 'main_table.product_id'
            ]
        );

        $ObjectManager= \Magento\Framework\App\ObjectManager::getInstance();
        $context = $ObjectManager->get('Magento\Backend\Model\Session');

        $vendorInfo = $context->getVendorInfo();

        if (!empty($vendorInfo)) {
            $this->getSelect()->where('main_table.vendor_id='.$vendorInfo['vendor_id']);
        }

        $this->getSelect()->joinLeft(
            ['pe' => $this->getTable('catalog_product_entity')],
            'main_table.product_id = pe.entity_id',
            [
                'product_entity_id' => 'pe.entity_id',
                'product_sku' => 'pe.sku'
            ]
        );
        
    }
}
