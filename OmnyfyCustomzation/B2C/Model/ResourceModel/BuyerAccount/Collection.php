<?php


namespace OmnyfyCustomzation\B2C\Model\ResourceModel\BuyerAccount;


use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use OmnyfyCustomzation\B2C\Model\BuyerAccount as BuyerAccountModel;
use OmnyfyCustomzation\B2C\Model\ResourceModel\BuyerAccount as BuyerAccountResource;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';
    protected $_eventPrefix = 'buyer_account';
    protected $_eventObject = 'buyer_account';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(BuyerAccountModel::class, BuyerAccountResource::class);
    }
}
