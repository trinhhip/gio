<?php


namespace OmnyfyCustomzation\PriceToQuote\Model\ResourceModel\ProductToQuote;


use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';
    protected $_eventPrefix = 'product_to_quote';
    protected $_eventObject = 'product_to_quote';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\OmnyfyCustomzation\PriceToQuote\Model\ProductToQuote::class,
            \OmnyfyCustomzation\PriceToQuote\Model\ResourceModel\ProductToQuote::class);
    }
}
