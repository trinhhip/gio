<?php


namespace OmnyfyCustomzation\ProductPdf\Block\Pdf;


use Magento\Catalog\Model\Product\Gallery\ReadHandler;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Model\Template\FilterFactory;
use Omnyfy\Vendor\Model\Resource\Vendor;
use Omnyfy\Vendor\Model\VendorFactory;
use PluginCompany\ProductPdf\Block\Pdf;
use PluginCompany\ProductPdf\Helper\ImageCompare;

class Content extends Pdf
{
    const NO_IMAGE = 'no_selection';
    protected $_template = 'OmnyfyCustomzation_ProductPdf::pdf/content.phtml';
    /**
     * @var Vendor
     */
    private $vendor;
    /**
     * @var VendorFactory
     */
    private $vendorFactory;
    /**
     * @var Configurable
     */
    private $configurable;

    public function __construct(
        Vendor $vendor,
        VendorFactory $vendorFactory,
        Configurable $configurable,
        Context $context,
        FilterFactory $templateFilterFactory,
        DirectoryList $directoryList,
        CollectionFactory $collectionFactory,
        ImageCompare $imageCompare,
        Data $pricingHelper,
        ReadHandler $galleryReadHandler,
        $data = []
    )
    {
        $this->vendor = $vendor;
        $this->vendorFactory = $vendorFactory;
        $this->configurable = $configurable;
        parent::__construct(
            $context,
            $templateFilterFactory,
            $directoryList,
            $collectionFactory,
            $imageCompare,
            $pricingHelper,
            $galleryReadHandler,
            $data
        );
    }

    public function getFullProductImageUrl($path)
    {
        if (!$path) {
            return false;
        }
        return parent::getFullProductImageUrl($path);
    }

    public function getVendorByProductId($productId)
    {
        $vendor = null;
        $vendorId = $this->vendor->getVendorIdByProductId($productId);
        if ($vendorId) {
            $vendor = $this->vendorFactory->create()->load($vendorId);
        }
        return $vendor;
    }

    public function getProductAttributeText($product, $attributeCode)
    {
        return $product->getResource()->getAttribute($attributeCode)->getFrontend()->getValue($product);
    }

    public function getProductOptions($product)
    {
        return $this->configurable->getConfigurableAttributesAsArray($product);
    }

    public function getVariations($option)
    {
        $optionLabel = [];
        foreach ($option['values'] as $value) {
            $optionLabel[] = __($value['label']);
        }
        return count($optionLabel) ? '(' . implode(', ', $optionLabel) . ')' : '';
    }

    public function getTechnicalImageAttributes()
    {
        return [
            'technical_drawing_1',
            'technical_drawing_2',
            'technical_drawing_3'
        ];
    }

    public function getTechnicalPhotos($product)
    {
        $productImages = $product->getMediaAttributeValues();
        $technicalAttributes = $this->getTechnicalImageAttributes();
        $technicalImages = [];
        foreach ($productImages as $imageCode => $image) {
            if (in_array($imageCode, $technicalAttributes) && $image && $image != self::NO_IMAGE) {
                $technicalImages[$imageCode] = $image;
            }
        }
        return $technicalImages;
    }
}
