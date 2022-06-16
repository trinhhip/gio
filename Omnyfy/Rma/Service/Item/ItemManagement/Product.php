<?php
namespace Omnyfy\Rma\Service\Item\ItemManagement;

/**
 *  We put here only methods directly connected with Item properties
 */
class Product extends \Mirasvit\Rma\Service\Item\ItemManagement\Product
{
    private $imageHelper;

    private $itemFactory;

    private $itemManagement;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface $itemManagement,
        \Mirasvit\Rma\Model\ItemFactory $itemFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory
    )
    {
        $this->itemManagement    = $itemManagement;
        $this->itemFactory       = $itemFactory;
        $this->productRepository = $productRepository;
        $this->imageHelper       = $imageHelper;
        $this->productFactory    = $productFactory;
        parent::__construct($itemManagement, $itemFactory, $productRepository, $imageHelper, $productFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function getImage($item, $imageId, $attributes = [])
    {
        try {
            $orderItem = $this->itemManagement->getOrderItem($item);
            $item->setProductOptions($orderItem->getProductOptions());
            $options = $item->getProductOptions();
            if (!empty($options['simple_sku'])) {
                $childItem = $this->itemFactory->create()->setSku($options['simple_sku']);
                $product   = $this->getProduct($childItem);
                $image     = $this->imageHelper->init($product, $imageId, $attributes);
                if ($image->getUrl() == $image->getDefaultPlaceholderUrl()) {//if child does not have img, use parent
                    $product = $this->getProduct($item);
                }
            } else {
                $product = $this->getProduct($item);
            }
            $image = $this->imageHelper->init($product, $imageId, $attributes);
            $image->setImageFile($product->getSmallImage());
            if ($image->getUrl() == $image->getDefaultPlaceholderUrl()) {
                $product = $this->productFactory->create();
                if (!empty($options['super_product_config'])) {//configurable product
                    $product->getResource()->load($product, $options['super_product_config']['product_id']);
                } elseif (!empty($options['info_buyRequest']) && isset($options['info_buyRequest']['product'])) {//others
                    $product->getResource()->load($product, $options['info_buyRequest']['product']);
                }
                $image = $this->imageHelper->init($product, $imageId, $attributes);
            }

            return $image;
        } catch (\Exception $e) {
            $product = $this->getProduct($item);
            return $image = $this->imageHelper->init($product, $imageId, $attributes);
        }

    }
}
