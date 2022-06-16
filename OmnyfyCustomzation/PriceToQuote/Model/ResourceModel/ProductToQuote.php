<?php


namespace OmnyfyCustomzation\PriceToQuote\Model\ResourceModel;


use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ProductToQuote extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('vermillion_product_to_quote', 'entity_id');
    }
}
