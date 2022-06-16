<?php


namespace Omnyfy\Enquiry\Model\ResourceModel;

class EnquiryMessages extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('omnyfy_enquiry_enquiry_messages', 'enquiry_messages_id');
    }
}
