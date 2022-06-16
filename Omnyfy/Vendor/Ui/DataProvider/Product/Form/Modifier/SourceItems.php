<?php

namespace Omnyfy\Vendor\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\InventoryCatalogApi\Model\IsSingleSourceModeInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku;

class SourceItems extends \Magento\InventoryCatalogAdminUi\Ui\DataProvider\Product\Form\Modifier\SourceItems
{
    /**
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isSourceItemManagementAllowedForProductType;

    /**
     * @var IsSingleSourceModeInterface
     */
    private $isSingleSourceMode;

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var GetSourceItemsDataBySku
     */
    private $getSourceItemsDataBySku;

    /**
     * @param IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     * @param LocatorInterface $locator
     * @param GetSourceItemsDataBySku $getSourceItemsDataBySku
     */
    public function __construct(
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType,
        IsSingleSourceModeInterface $isSingleSourceMode,
        LocatorInterface $locator,
        GetSourceItemsDataBySku $getSourceItemsDataBySku
    ) {
        parent::__construct($isSourceItemManagementAllowedForProductType, $isSingleSourceMode, $locator, $getSourceItemsDataBySku);
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
        $this->isSingleSourceMode = $isSingleSourceMode;
        $this->locator = $locator;
        $this->getSourceItemsDataBySku = $getSourceItemsDataBySku;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        $product = $this->locator->getProduct();

        $meta['sources'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'visible' => $this->isSourceItemManagementAllowedForProductType->execute($product->getTypeId()),
                    ],
                ],
            ]
        ];

        return $meta;
    }
}
