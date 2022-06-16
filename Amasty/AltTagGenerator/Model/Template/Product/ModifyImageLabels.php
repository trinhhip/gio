<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Template\Product;

use Amasty\AltTagGenerator\Model\Template\Product\Filter\CustomAttributeResolver\IncrementResolver;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Media\Config as MediaConfig;

class ModifyImageLabels
{
    /**
     * @var ModifyAltTag
     */
    private $modifyAltTag;

    /**
     * @var IncrementResolver
     */
    private $incrementResolver;

    /**
     * @var MediaConfig
     */
    private $mediaConfig;

    public function __construct(
        MediaConfig $mediaConfig,
        ModifyAltTag $modifyAltTag,
        IncrementResolver $incrementResolver
    ) {
        $this->modifyAltTag = $modifyAltTag;
        $this->incrementResolver = $incrementResolver;
        $this->mediaConfig = $mediaConfig;
    }

    public function execute(ProductInterface $product): void
    {
        foreach ($this->mediaConfig->getMediaAttributeCodes() as $attributeCode) {
            $labelAttributeCode = sprintf('%s_label', $attributeCode);
            $altTag = $this->modifyAltTag->execute(
                $product,
                $product->getData($labelAttributeCode) ?: '',
                true
            );
            $product->setData($labelAttributeCode, $altTag);
        }

        $this->incrementResolver->clear();
    }
}
