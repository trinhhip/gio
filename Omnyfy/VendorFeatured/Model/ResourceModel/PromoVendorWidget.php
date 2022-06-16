<?php
namespace Omnyfy\VendorFeatured\Model\ResourceModel;

class PromoVendorWidget extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('omnyfy_vendorfeatured_promo_widget', 'entity_id');
    }
}