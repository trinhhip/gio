<?php
namespace Omnyfy\Vendor\Helper;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Omnyfy\Vendor\Api\Data\VendorTypeInterface;
use Omnyfy\Vendor\Api\LocationRepositoryInterface;

class Product extends AbstractHelper
{
    /**
     * @var \Omnyfy\Vendor\Helper\Media
     */
    protected $_vendorMedia;
    /**
     * @var \Omnyfy\Vendor\Api\VendorRepositoryInterface
     */
    protected $_vendorRepository;
    /**
     * @var LocationRepositoryInterface
     */
    protected $_locationRepository;
    /**
     * @var \Omnyfy\Vendor\Api\VendorTypeRepositoryInterface
     */
    protected $_typeRepository;
    /**
     * @var \Omnyfy\Vendor\Api\VendorProductRepositoryInterface
     */
    protected $_productRepository;
    /**
     * @var Attribute
     */
    private $attribute;

    /**
     * Product constructor.
     * @param Context $context
     * @param \Omnyfy\Vendor\Api\VendorRepositoryInterface $vendorRepository
     * @param LocationRepositoryInterface $locationRepository
     * @param \Omnyfy\Vendor\Api\VendorTypeRepositoryInterface $typeRepository
     * @param \Omnyfy\Vendor\Api\VendorProductRepositoryInterface $productRepository
     * @param Media $media
     * @param Attribute $attribute
     */
    public function __construct(
        Context $context,
        \Omnyfy\Vendor\Api\VendorRepositoryInterface $vendorRepository,
        \Omnyfy\Vendor\Api\LocationRepositoryInterface $locationRepository,
        \Omnyfy\Vendor\Api\VendorTypeRepositoryInterface $typeRepository,
        \Omnyfy\Vendor\Api\VendorProductRepositoryInterface $productRepository,
        \Omnyfy\Vendor\Helper\Media $media,
         \Omnyfy\Vendor\Helper\Attribute $attribute
    ){
        $this->_vendorRepository = $vendorRepository;
        $this->_locationRepository = $locationRepository;
        $this->_productRepository = $productRepository;
        $this->_typeRepository = $typeRepository;
        $this->_vendorMedia = $media;
        parent::__construct($context);
        $this->attribute = $attribute;
    }
    /**
     * Get the vendor Id
     *
     * @param \Omnyfy\Vendor\Api\VendorProductRepositoryInterface $productRepository
     * @return bool|string
     */
    public function getVendorId($productId){
        return $this->_productRepository->getByProduct($productId);
    }
    /**
     * Get the vendor logo
     *
     * @param \Omnyfy\Vendor\Model\Vendor $vendor
     * @return bool|string
     */
    public function getVendorLogo($vendor){
        return $this->_vendorMedia->getVendorLogoUrl($vendor);
    }
    /**
     * Get the vendor banner
     *
     * @param \Omnyfy\Vendor\Model\Vendor $vendor
     * @return bool|string
     */
    public function getVendorBanner($vendor){
        return $this->_vendorMedia->getVendorBannerUrl($vendor);
    }
    /**
     * Gent the VendorInterface by id
     *
     * @param $vendorId
     * @return null|\Omnyfy\Vendor\Api\Data\VendorInterface
     */
    public function getVendor($vendorId){
        try {
            return $this->_vendorRepository->getById($vendorId);
        } Catch(\Exception $exception){
            return null;
        }
    }
    /**
     * Get the LocationInterface by id
     *
     * @param $locationId
     * @return null|\Omnyfy\Vendor\Api\Data\LocationInterface
     */
    public function getLocation($locationId){
        try {
            return $this->_locationRepository->getById($locationId);
        } catch (\Exception $exception){
            return null;
        }
    }

    /**
     * Get the VendorTypeInterface by vendor type id
     *
     * @param $typeId
     * @return null|VendorTypeInterface
     */
    public function getVendorType($typeId) {
        try {
            $vendorType = $this->_typeRepository->getById($typeId);
            return $vendorType;
        } catch(\Exception $exception){
            return null;
        }
    }
    /**
     * Check vendor template that is required to display
     *
     * @param $typeId
     * @return int|null|string
     * 0 - Display the Vendor details and link to the vendor page.
     * 1 - Display the Location details and link to the location page.
     */
    public function isVendorTemplate($typeId){
        if($type = $this->getVendorType($typeId))
            return $type->getSearchBy();
        return 0;
    }

    public function getAttributeShowOnEmail($vendor)
    {
        /* @var VendorTypeInterface $vendor */
        if (!$vendor) {
            return [];
        }
        $data = [];
        $attributeShowOnEmail = $this->attribute->getVendorAttributesAttributeShowOnEmail($vendor->getTypeId())->getItems();
        if(!empty($attributeShowOnEmail)) {
            foreach ($attributeShowOnEmail as $attribute) {
                if ($attribute->usesSource()) {
                    $value = $attribute->getSource()->getOptionText($vendor->getData($attribute->getAttributeCode()));
                } else {
                    $value = $vendor->getData($attribute->getAttributeCode());
                }
                if(!$value){
                    continue;
                }
                $data[] = [
                    'label' => $attribute->getFrontendLabel(),
                    'value' => $value
                ];
            }
        }
        return $data;

    }

    public function getProductSaleLabel($product, $isSaleLabelPercent, $productLabelConfig){
        $productLabel = '';
        $savePercent = null;
        $childProducts = [];
        if ($product->getTypeId() == 'configurable') {
            $childProducts = $product->getTypeInstance()->getUsedProducts($product);
        } elseif ($product->getTypeId() == 'grouped') {
            $childProducts = $product->getTypeInstance()->getAssociatedProducts($product);
        } else {
            $orgPrice = $product->getPrice();
            $specialPrice = $product->getSpecialPrice();
            $specialfromdate = $product->getSpecialFromDate();
            $specialtodate = $product->getSpecialToDate();
            if($specialPrice && $this->isSpecialDateValid($specialfromdate, $specialtodate) && $specialPrice < $orgPrice){
                $savePercent = 100-round(($specialPrice/$orgPrice)*100);
            }
        }
        if(!empty($childProducts)){
            foreach ($childProducts as $childProduct) {
                $childPrice = $childProduct->getPrice();
                $specialfromdate = $childProduct->getSpecialFromDate();
                $specialtodate = $childProduct->getSpecialToDate();
                if ($childProduct->getSpecialPrice() !== null && $this->isSpecialDateValid($specialfromdate, $specialtodate)) {
                    $childSpecialPrice = $childProduct->getSpecialPrice();
                    if($childSpecialPrice < $childPrice){
                        $allSavePercent[] = 100-round(($childSpecialPrice/$childPrice)*100);
                    }
                }
            }
            $savePercent = !empty($allSavePercent) ? max($allSavePercent) : null;
        }
        if($isSaleLabelPercent) {
            $productLabel .= $savePercent ? '<div class="product-label sale-label">'.'-' . $savePercent . '%'.'</div>' : '';
        }else{
            $productLabel .= $savePercent ? '<div class="product-label sale-label">'. $productLabelConfig .'</div>' : '';
        }
        return $productLabel;
    }

    public function isSpecialDateValid($specialfromdate, $specialtodate){
        $today = time();
        return (is_null($specialfromdate) && is_null($specialtodate)) || ($today >= strtotime($specialfromdate) && is_null($specialtodate)) || ($today <= strtotime($specialtodate) && is_null($specialfromdate)) || ($today >= strtotime($specialfromdate) && $today <= strtotime($specialtodate));
    }
}
