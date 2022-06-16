<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Template\Product;

use Amasty\AltTagGenerator\Model\Template\Product\Filter\CustomAttributeResolver\IncrementResolver;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;

class ModifyMediaGallery
{
    /**
     * @var ModifyAltTag
     */
    private $modifyAltTag;

    /**
     * @var IncrementResolver
     */
    private $incrementResolver;

    public function __construct(ModifyAltTag $modifyAltTag, IncrementResolver $incrementResolver)
    {
        $this->modifyAltTag = $modifyAltTag;
        $this->incrementResolver = $incrementResolver;
    }

    /**
     * @param ProductInterface|Product $product
     * @return void
     */
    public function execute(ProductInterface $product): void
    {
        $mediaGallery = $product->getMediaGallery();
        foreach ($mediaGallery['images'] as &$image) {
            $image['label'] = $this->modifyAltTag->execute($product, $image['label'] ?? '');
        }
        $product->setMediaGallery($mediaGallery);

        $this->incrementResolver->clear();
    }
}
