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



namespace Mirasvit\GoogleTagManager\Model\Config\Source;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject;
use Magento\Framework\Option\ArrayInterface;

class Brand implements ArrayInterface
{
    private $attributeRepository;

    private $searchCriteriaBuilder;

    private $convertDataObject;

    public function __construct(
        ProductAttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DataObject $convertDataObject
    ) {
        $this->attributeRepository   = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->convertDataObject     = $convertDataObject;
    }

    public function toOptionArray(): array
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $default           = ['value' => 0, 'label' => __(' ')];
        $productAttributes = $this->attributeRepository->getList($searchCriteria)->getItems();
        $options           = $this->convertDataObject->toOptionArray($productAttributes, 'attribute_code', 'frontend_label');

        array_unshift($options, $default);

        return $options;
    }
}
