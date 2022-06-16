<?php
namespace Omnyfy\ProductImport\Model\ProductService;

abstract class AbstractBaseProductService
{
    /**
     * @var \Omnyfy\ProductImport\Helper\ProductImage
     */
    protected $imageHelper;

    /**
     * Constructor
     *
     * @param \Omnyfy\ProductImport\Helper\ProductImage $imageHelper
     */
    public function __construct(
        \Omnyfy\ProductImport\Helper\ProductImage $imageHelper
    ){
        $this->imageHelper = $imageHelper;
    }

    abstract function getProduct($productRequestItem);

    public function setProductImages($product, $mediaGallery)
    {
        $global = $product->global();

        foreach($mediaGallery as $gallery) {
            if (isset($gallery['import_mode']) && $gallery['import_mode'] == 'async') {
                # asynchronous product image import
                $this->imageHelper->addImage($product->getSku(), $gallery);

            }else{
                # synchronous product image import
                $image = $product->addImage($gallery['file']);

                if (isset($gallery['label']) && isset($gallery['position']) && isset($gallery['disabled'])) {
                    $global->setImageGalleryInformation($image, $gallery['label'], $gallery['position'], !$gallery['disabled']);
                }

                if (isset($gallery['types'])) {
                    foreach ($gallery['types'] as $key => $imageType) {
                        $global->setImageRole($image, $imageType);
                    }
                }
            }
        }
        return $product;
    }
}