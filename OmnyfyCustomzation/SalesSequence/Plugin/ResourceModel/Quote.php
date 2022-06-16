<?php
/**
 * Copyright © 0 All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OmnyfyCustomzation\SalesSequence\Plugin\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use OmnyfyCustomzation\SalesSequence\Helper\Data;

class Quote
{
    /**
     * @var ResourceConnection
     */
    protected $connection;
    /**
     * @var Data
     */
    protected $helperData;

    public function __construct(
        ResourceConnection $resource,
        Data $helperData
    )
    {
        $this->connection = $resource->getConnection('sales');
        $this->helperData = $helperData;
    }

    public function afterGetReservedOrderId(
        \Magento\Quote\Model\ResourceModel\Quote $subject,
        $result,
        $quote
    )
    {
        $lastSequence = $this->connection->lastInsertId('sequence_order_' . $quote->getStoreId());
        return $this->helperData->getIncrementId($quote->getShippingAddress(), $lastSequence);
    }
}

