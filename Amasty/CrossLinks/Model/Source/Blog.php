<?php

namespace Amasty\CrossLinks\Model\Source;

class Blog implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->moduleManager = $moduleManager;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        $arr = $this->toArray();
        foreach ($arr as $value => $label) {
            $optionArray[] = [
                'value' => $value,
                'label' => $label
            ];
        }
        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        if ($this->moduleManager->isEnabled('Amasty_Blog')) {
            $result = [0 => __('No'), 1 => __('Yes')];
        } else {
            $result = [0 => __('No (Module was not installed)')];
        }

        return $result;
    }
}
