<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-gtm
 * @version   1.0.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\GoogleTagManager\Service;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Session;
use Mirasvit\GoogleTagManager\Block\Adminhtml\Config\Form\Field\CustomDataRenderer\TypeOption;
use Mirasvit\GoogleTagManager\Model\Config;
use Mirasvit\GoogleTagManager\Model\Config\Source\ResolutionIdentifier;
use Mirasvit\GoogleTagManager\Model\DataProvider;
use Mirasvit\GoogleTagManager\Model\Config\Source\ProductIdentifier;

class DataService
{
    private $config;

    private $groupRepository;

    private $productRepository;

    private $session;

    public function __construct(
        Config $config,
        GroupRepositoryInterface $groupRepository,
        ProductRepository $productRepository,
        Session $session
    ) {
        $this->config            = $config;
        $this->groupRepository   = $groupRepository;
        $this->productRepository = $productRepository;
        $this->session           = $session;
    }

    public function formatPrice(float $v): float
    {
        return round($v, 2);
    }

    public function getProductData(Product $product, string $currency): array
    {
        $category = $product->getCategory();

        $categoryName = $category ? $category->getName() : '';

        $data = [
            DataProvider::KEY_PRODUCT_ID => $product->getId(),
            'item_name'                  => $product->getName(),
            'item_id'                    => $this->getProductId($product),
            'price'                      => $this->formatPrice((float)$product->getFinalPrice()),
            'currency'                   => $currency,
            'item_brand'                 => $this->getProductBrand($product),
            'item_category'              => $categoryName,
            'item_variant'               => $this->getProductVariants($product),
            'quantity'                   => (float)$product->getQuantity(),
        ];

        $data = $this->addCustomData($data, $product);
        $data = $this->addCustomerGroup($data, $product);

        return $data;
    }

    private function getProductId(Product $product): string
    {
        $result = $this->config->getMappingProductIdentifier($product->getStore()) == ProductIdentifier::PRODUCT_IDENTIFIER_ID ?
            (string)$product->getId() :
            $product->getSku();

        if ($this->config->getMappingProductIdentifier() == ResolutionIdentifier::PARENT_IDENTIFIER) {
            $result = $this->config->getMappingProductIdentifier($product->getStore()) == ProductIdentifier::PRODUCT_IDENTIFIER_ID ?
                (string)$product->getParentId() :
                $product->getParentSku();
        }

        return $result;
    }

    private function getProductBrand(Product $product): string
    {
        $attrCode = $this->config->getMappingBrandIdentifier($product->getStore());

        return $attrCode == 0 ? '' : $product->getData($attrCode);
    }

    private function getProductVariants(Product $product): string
    {
        $isTrack = $this->config->isMappingTrackVariants($product->getStore());

        $parentId = $product->getTypeInstance()->getParentIdsByChild($product->getId());

        return $isTrack && count($parentId) ? '' : $product->getSku();
    }

    private function addCustomData(array $data, Product $product): array
    {
        $customData = $this->config->getMappingCustomData($product->getStore());

        usort($customData, function ($a, $b) {
            return $a['custom_data_index'] <=> $b['custom_data_index'];
        });

        $product = $this->productRepository->getById($product->getId());

        foreach ($customData as $v) {
            $value = $product->getData($v['custom_data_attr']);

            if ($product->getCustomAttribute($v['custom_data_attr'])) {
                $value = (string)$product->getAttributeText($v['custom_data_attr']);
            }

            $data[$v['custom_data_code']] = $v['custom_data_type'] == TypeOption::TYPE_DIMENSION ? $value : (int)$value;
        }

        return $data;
    }

    private function addCustomerGroup(array $data, Product $product): array
    {
        $isTrack = (bool)$this->config->getMappingTrackCustomerGroup($product->getStore());

        if ($isTrack) {
            try {
                $data['customer_group'] = (string)$this->groupRepository->getById($this->session->getCustomerGroupId())->getCode();
            } catch (\Exception $e) {
            }
        }

        return $data;
    }
}
