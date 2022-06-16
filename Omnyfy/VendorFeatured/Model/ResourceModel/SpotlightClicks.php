<?php
namespace Omnyfy\VendorFeatured\Model\ResourceModel;

class SpotlightClicks extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('omnyfy_spotlight_clicks', 'click_id');
    }
}