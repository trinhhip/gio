<?php
namespace Omnyfy\Vendor\Plugin\Product\Checker;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\CatalogInventory\Model\Configuration;

class AddToCompareAvailability
{
    /**
     * @var Configuration
     */
    private $stockConfiguration;

    /**
     * @param Configuration $stockConfiguration
     */
    public function __construct(Configuration $stockConfiguration)
    {
        $this->stockConfiguration = $stockConfiguration;
    }

    public function afterIsAvailableForCompare(\Magento\Catalog\ViewModel\Product\Checker\AddToCompareAvailability $subject, $result, ProductInterface $product)
    {
        if ((int)$product->getStatus() !== Status::STATUS_DISABLED) {
            return $product->isSaleable() || $this->stockConfiguration->isShowOutOfStock();
        }

        return $result;
    }
}