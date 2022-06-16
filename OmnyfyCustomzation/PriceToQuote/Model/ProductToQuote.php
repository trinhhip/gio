<?php


namespace OmnyfyCustomzation\PriceToQuote\Model;


use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class ProductToQuote extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'product_to_quote';

    protected $_cacheTag = 'product_to_quote';

    protected $_eventPrefix = 'product_to_quote';

    protected function _construct()
    {
        $this->_init(\OmnyfyCustomzation\PriceToQuote\Model\ResourceModel\ProductToQuote::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        return [];
    }
}
