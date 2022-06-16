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

namespace Mirasvit\GoogleTagManager\Block\Adminhtml\Config\Form\Field\CustomDataRenderer;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class AttributeOption extends Select
{
    private $attributeRepository;

    private $searchCriteriaBuilder;

    private $convertDataObject;

    public function __construct(
        ProductAttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DataObject $convertDataObject,
        Context $context,
        array $data = []
    ) {
        $this->attributeRepository   = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->convertDataObject     = $convertDataObject;

        parent::__construct($context, $data);
    }

    private function getOptionData(): array
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $productAttributes = $this->attributeRepository->getList($searchCriteria)->getItems();
        $options           = $this->convertDataObject->toOptionHash($productAttributes, 'attribute_code', 'frontend_label');

        return $options;
    }

    public function setInputName(string $value): AttributeOption
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            foreach ($this->getOptionData() as $optionId => $optionLabel) {
                if ($optionLabel !== null) {
                    $this->addOption($optionId, addslashes($optionLabel));
                }
            }
        }

        return parent::_toHtml();
    }
}
