<?php


namespace OmnyfyCustomzation\B2C\Block\Retail;


use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductRepository;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use OmnyfyCustomzation\B2C\Helper\Data as HelperData;

class Price extends AbstractProduct
{
    const PARAM_NAME_URL_ENCODED = 'uenc';
    /**
     * @var UrlHelper
     */
    public $urlHelper;
    /**
     * @var HelperData
     */
    public $helperData;
    /**
     * @var Configurable
     */
    public $configurable;
    /**
     * @var Visibility
     */
    public $productVisibility;
    /**
     * @var ProductRepository
     */
    public $productRepository;

    public function __construct(
        Context $context,
        UrlHelper $urlHelper,
        HelperData $helperData,
        Configurable $configurable,
        Visibility $productVisibility,
        ProductRepository $productRepository,
        array $data = []
    )
    {
        $this->urlHelper = $urlHelper;
        $this->helperData = $helperData;
        $this->configurable = $configurable;
        $this->productVisibility = $productVisibility;
        $this->productRepository = $productRepository;
        parent::__construct($context, $data);
    }

    public function getParamNameUrlEncode()
    {
        return self::PARAM_NAME_URL_ENCODED;
    }

    public function getAddToCartPostParams($product)
    {
        $visibleInSiteIds = $this->productVisibility->getVisibleInSiteIds();
        if (!$this->isVisibility($product, $visibleInSiteIds)) {
            $parentIds = $this->configurable->getParentIdsByChild($product->getId());
            foreach ($parentIds as $parentId) {
                $product = $this->productRepository->getById($parentId);
                if ($this->isVisibility($product, $visibleInSiteIds)) {
                    break;
                }
            }
        }
        $url = $this->getAddToCartUrl($product, ['_escape' => false]);
        return [
            'action' => $url,
            'data' => [
                'product' => (int)$product->getEntityId(),
                'uenc' => $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }

    public function getRetailPrice($product)
    {
        return $this->helperData->getRetailPrice($product);
    }

    protected function isVisibility($product, $visibleInSiteIds)
    {
        return in_array($product->getVisibility(), $visibleInSiteIds);
    }

    public function getAddToCartUrl($product, $additional = [])
    {
        switch ($product->getTypeId()) {
            case 'configurable':
                $additional['_query']['options'] = 'cart';
                $url = $this->getProductUrl($product, $additional);
                break;
            default:
                $url = $this->_cartHelper->getAddUrl($product, $additional);
                break;
        }
        return $url;
    }
}
