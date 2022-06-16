<?php


namespace OmnyfyCustomzation\Navigation\Plugin\Catalog\Model;


class Config
{
    const LOW_PRICE = 'low_price';
    const HIGH_PRICE = 'high_price';

    public function afterGetAttributeUsedForSortByArray(\Magento\Catalog\Model\Config $catalogConfig, $results)
    {
        $results[self::LOW_PRICE] = __('Price: Low to High');
        $results[self::HIGH_PRICE] = __('Price: High to Low');
        return $results;
    }
}
