<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Plugin\Catalog\Model\Product\Gallery\ReadHandler;

use Amasty\AltTagGenerator\Model\Template\Product\ModifyImageLabels;
use Amasty\AltTagGenerator\Model\Template\Product\ModifyMediaGallery;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Gallery\ReadHandler;

class ModifyImageLabel
{
    /**
     * @var ModifyMediaGallery
     */
    private $modifyMediaGallery;

    /**
     * @var ModifyImageLabels
     */
    private $modifyImageLabels;

    public function __construct(ModifyMediaGallery $modifyMediaGallery, ModifyImageLabels $modifyImageLabels)
    {
        $this->modifyMediaGallery = $modifyMediaGallery;
        $this->modifyImageLabels = $modifyImageLabels;
    }

    /**
     * @see \Magento\Catalog\Model\Product\Gallery\ReadHandler::execute
     * @see \Magento\Catalog\Model\ResourceModel\Product\Collection::addMediaGalleryData
     *
     * @param ReadHandler $subject
     * @param null $result
     * @param Product $product
     * @return void
     */
    public function afterAddMediaDataToProduct(ReadHandler $subject, $result, Product $product): void
    {
        $this->modifyMediaGallery->execute($product);
        $this->modifyImageLabels->execute($product);
    }
}
