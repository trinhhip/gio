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

use Magento\Framework\View\Element\Html\Select;

class TypeOption extends Select
{
    const TYPE_DIMENSION = 'dimension';
    const TYPE_METRIC    = 'metric';

    private function getOptionData(): array
    {
        return [
            self::TYPE_DIMENSION => (string)__('Dimension'),
            self::TYPE_METRIC    => (string)__('Metric'),
        ];
    }

    public function setInputName(string $value): TypeOption
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
                $this->addOption($optionId, addslashes($optionLabel));
            }
        }
        return parent::_toHtml();
    }
}
