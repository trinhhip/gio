<?php


namespace OmnyfyCustomzation\Catalog\Plugin\CatalogSearch\Block;


use Magento\CatalogSearch\Helper\Data;
use Magento\Search\Model\QueryFactory;

class Result
{
    /**
     * @var Data
     */
    public $catalogSearchData;
    /**
     * @var QueryFactory
     */
    public $queryFactory;

    public function __construct(
        Data $catalogSearchData,
        QueryFactory $queryFactory
    )
    {
        $this->catalogSearchData = $catalogSearchData;
        $this->queryFactory = $queryFactory;
    }

    public function aroundGetNoResultText(\Magento\CatalogSearch\Block\Result $subject, \Closure $proceed)
    {
        if ($this->catalogSearchData->isMinQueryLength()) {
            return __(
                'Please type your desired product, designer or brand name.',
                $this->queryFactory->get()->getMinQueryLength()
            );
        }
        return __('Discoveries await. Please try again!');
    }
}