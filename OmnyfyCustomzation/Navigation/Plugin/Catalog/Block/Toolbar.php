<?php


namespace OmnyfyCustomzation\Navigation\Plugin\Catalog\Block;


use OmnyfyCustomzation\Navigation\Plugin\Catalog\Model\Config;

class Toolbar
{
    public function aroundSetCollection(\Magento\Catalog\Block\Product\ProductList\Toolbar $subject,
                                        \Closure $proceed, $collection)
    {
        $currentOrder = $subject->getCurrentOrder();
        $result = $proceed($collection);
        switch ($currentOrder) {
            case Config::LOW_PRICE:
                $subject->getCollection()->setOrder('price', 'ASC');
                break;
            case Config::HIGH_PRICE:
                $subject->getCollection()->setOrder('price', 'DESC');
                break;
        }
        return $result;
    }
}
