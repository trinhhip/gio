<?php


namespace Omnyfy\Enquiry\Model\ResourceModel;

class Enquiries extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('omnyfy_enquiry_enquiries', 'enquiries_id');
    }
}
