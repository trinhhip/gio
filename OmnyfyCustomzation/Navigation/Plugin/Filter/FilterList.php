<?php


namespace OmnyfyCustomzation\Navigation\Plugin\Filter;


use Magento\Catalog\Model\Layer;
use Magento\Framework\ObjectManagerInterface;
use OmnyfyCustomzation\Navigation\Model\Layer\Dimension;

class FilterList
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        ObjectManagerInterface $objectManager
    )
    {
        $this->objectManager = $objectManager;
    }

    public function afterGetFilters(
        Layer\FilterList $subject,
        $result,
        Layer $layer
    )
    {
        $result[] = $this->createDimensionFilter($layer);
        return $result;
    }

    public function createDimensionFilter(Layer $layer)
    {
        return $this->objectManager->create(
            Dimension::class,
            ['data' => [], 'layer' => $layer]
        );
    }
}