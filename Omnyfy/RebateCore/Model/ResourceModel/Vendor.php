<?php

namespace Omnyfy\RebateCore\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class Vendor
 * @package Omnyfy\RebateCore\Model\ResourceModel
 */
class Vendor extends AbstractDb
{
    /**
     * Vendor constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context
    )
    {
        parent::__construct($context);
    }

    /**
     *
     */
    protected function _construct()
    {
        $this->_init('omnyfy_vendor_vendor_entity', 'entity_id');
    }

    /**
     * @return string
     */
    public function getIdFieldName()
    {
        return 'entity_id';
    }

}

