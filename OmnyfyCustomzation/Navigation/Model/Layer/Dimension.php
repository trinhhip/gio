<?php


namespace OmnyfyCustomzation\Navigation\Model\Layer;


use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;

class Dimension extends AbstractFilter
{
    const MAX_DIMENSION = 999999;
    /**
     * @var CollectionFactory
     */

    public $productCollection;
    public $dimension = [
        'length' => 'omnyfy_dimensions_length',
        'width' => 'omnyfy_dimensions_width',
        'height' => 'omnyfy_dimensions_height',
    ];

    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        DataBuilder $itemDataBuilder,
        CollectionFactory $productCollection,
        array $data = []
    )
    {
        $this->productCollection = $productCollection;
        parent::__construct($filterItemFactory, $storeManager, $layer, $itemDataBuilder, $data);
    }

    /**
     * @param RequestInterface $request
     *
     * @return $this
     */
    public function apply(RequestInterface $request)
    {
        if ($request->getParam('height') || $request->getParam('width') || $request->getParam('length')) {
            $filterIds = $this->getFilterIds($request);
            $productCollection = $this->getLayer()->getProductCollection();
            $productCollection->addIdFilter($filterIds);
        }
        return $this;
    }

    public function getFilterIds($request)
    {
        $collection = $this->productCollection->create();
        foreach ($this->dimension as $key => $attrCode) {
            if ($request->getParam($key)) {
                $collection = $this->prepareCollection($collection, $request->getParam($key), $attrCode);
                $this->getLayer()->getState()->addFilter(
                    $this->_createItem($key, $request->getParam($key))
                );
            }
        }
        return $collection->getAllIds();
    }

    public function prepareCollection($collection, $dimensionRange, $attributeCode)
    {
        $dimensionFiler = $this->getFilterRange($dimensionRange);
        $collection->addFieldToSelect($attributeCode);
        $collection->addFieldToFilter($attributeCode, ['from' => $dimensionFiler[0], 'to' => $dimensionFiler[1]]);
        return $collection;
    }

    public function getFilterRange($range)
    {
        $filterOptions = explode('-', $range);
        $filterOptions[0] = isset($filterOptions[0]) ? $filterOptions[0] : 0;
        $filterOptions[1] = isset($filterOptions[1]) ? $filterOptions[1] : self::MAX_DIMENSION;
        $filterOptions[1] = $filterOptions[1] < $filterOptions[0] ? $filterOptions[0] : $filterOptions[1];
        return $filterOptions;
    }
}
