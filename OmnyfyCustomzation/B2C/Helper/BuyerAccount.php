<?php


namespace OmnyfyCustomzation\B2C\Helper;


use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;

class BuyerAccount extends AbstractHelper
{
    /**
     * @var ResourceConnection
     */
    public $resource;

    public function __construct(
        ResourceConnection $resource,
        Context $context
    )
    {
        $this->resource = $resource;
        parent::__construct($context);
    }

    public function requestToTrade($email, $status)
    {
        $connection = $this->resource->getConnection();
        $buyerTable = $connection->getTableName('b2c_customer_approval');
        $sql = $connection->select()->from(
            ['b' => $buyerTable],
            ['email']
        )->where('b.email =?', $email);
        $result = $connection->fetchAll($sql);
        if (count($result)) {
            $connection->update($buyerTable, ['status' => $status], ['email = ?' => $email]);
        } else {
            $connection->insert($buyerTable, ['email' => $email, 'status' => $status]);
        }
    }
}
