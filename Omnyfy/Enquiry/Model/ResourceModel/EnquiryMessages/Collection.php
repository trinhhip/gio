<?php


namespace Omnyfy\Enquiry\Model\ResourceModel\EnquiryMessages;

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
            'Omnyfy\Enquiry\Model\EnquiryMessages',
            'Omnyfy\Enquiry\Model\ResourceModel\EnquiryMessages'
        );
    }
}
