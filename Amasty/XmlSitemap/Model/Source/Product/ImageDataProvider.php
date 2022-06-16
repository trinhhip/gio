<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\Source\Product;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Backend\Media\ImageEntryConverter;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;
use Magento\Framework\Data\Collection;
use Magento\Framework\Escaper;

class ImageDataProvider
{
    const IMAGE_TYPE = 'product_page_image_medium_no_frame';

    /**
     * @var GalleryReadHandler
     */
    private $galleryReadHandler;

    /**
     * @var Image
     */
    private $imageHelper;

    /**
     * @var Escaper
     */
    private $escaper;

    public function __construct(
        GalleryReadHandler $galleryReadHandler,
        Image $imageHelper,
        Escaper $escaper
    ) {
        $this->galleryReadHandler = $galleryReadHandler;
        $this->imageHelper = $imageHelper;
        $this->escaper = $escaper;
    }

    public function getData(Product $product): array
    {
        $this->galleryReadHandler->execute($product);
        $images = $product->getMediaGalleryImages();

        if ($images instanceof Collection) {
            $image = $images->getFirstItem();
            if ($image->getFile()) {
                $imagesData = [
                    'loc' => $this->getImageUrl($product, $image->getFile()),
                    'title' => $image->getLabel()
                ];
            }
        }

        return $imagesData ?? [];
    }

    private function getImageUrl(Product $product, string $file): string
    {
        $image = $this->imageHelper->init(
            $product,
            self::IMAGE_TYPE,
            ['type' => ImageEntryConverter::MEDIA_TYPE_CODE]
        );
        $image->setImageFile($file);

        return $this->escaper->escapeUrl($image->getUrl());
    }
}
