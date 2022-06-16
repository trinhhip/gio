<?php


namespace OmnyfyCustomzation\Cart\Block\Cart;


use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Url\Helper\Data;

class RelatedProducts extends \Magento\Catalog\Block\Product\ListProduct
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    public $productCollectionFactory;
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    public $cart;
    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    public $productStatus;
    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    public $productVisibility;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Checkout\Model\Cart $cart,
        Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        Data $urlHelper,
        array $data = []
    )
    {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->cart = $cart;
        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
    }

    public function getLoadedProductCollection()
    {
        $collection = null;
        $relatedIds = $this->getRelatedIds();
        if ($relatedIds){
            $collection = $this->productCollectionFactory->create();
            $collection->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()]);
            $collection->setVisibility($this->productVisibility->getVisibleInSiteIds());
            $collection->addFieldToSelect('lead_time');
            $collection->addFieldToSelect('name');
            $collection->addFieldToFilter('entity_id', ['in', $relatedIds]);
        }

        return $collection;
    }

    public function getRelatedIds()
    {
        $relatedProductIds = [];
        $items = $this->cart->getQuote()->getAllItems();
        foreach ($items as $item) {
            $product = $item->getProduct();
            $relatedProducts = $product->getRelatedProducts();
            foreach ($relatedProducts as $relatedProduct) {
                $relatedProductIds[] = $relatedProduct->getId();
            }
        }
        return $relatedProductIds;
    }
}
