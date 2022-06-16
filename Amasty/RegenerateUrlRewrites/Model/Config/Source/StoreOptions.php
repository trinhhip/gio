<?php

declare(strict_types=1);

namespace Amasty\RegenerateUrlRewrites\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\StoreManagerInterface;

class StoreOptions implements OptionSourceInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $optionArray = [];
        $optionArray[] = ['value' => '', 'label' => __('All store view')];
        /** @var \Magento\Store\Api\Data\StoreInterface $store */
        foreach ($this->storeManager->getStores(false) as $store) {
            $optionArray[] = ['value' => $store->getId(), 'label' => $store->getName()];
        }

        return $optionArray;
    }
}
