<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

declare(strict_types=1);

namespace Amasty\Orderattr\Model\Attribute\InputType\FrontendCaster;

use Amasty\Orderattr\Api\Data\CheckoutAttributeInterface;
use Magento\Store\Model\StoreManagerInterface;

class File implements SpecificationProcessorInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * @param string[] $element
     * @param CheckoutAttributeInterface $attribute
     */
    public function processSpecificationByAttribute(array &$element, CheckoutAttributeInterface $attribute): void
    {
        $validateRules = $attribute->getValidateRules();
        if (!empty($validateRules['max_file_size'])) {
            $element['maxFileSize'] = (int)$validateRules['max_file_size'] * 1024 * 1024;
        }
        if (!empty($validateRules['file_extensions'])) {
            $element['allowedExtensions'] = str_replace(',', ' ', $validateRules['file_extensions']);
        }
        $element['uploaderConfig']['url'] = $this->storeManager->getStore()->getUrl('orderattr/file/upload');
    }
}
