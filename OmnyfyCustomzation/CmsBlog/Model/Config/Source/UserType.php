<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\UserType\CollectionFactory;

/**
 * Used in recent article widget
 *
 */
class UserType implements ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    protected $userTypeCollectionFactory;

    /**
     * @var array
     */
    protected $options;

    /**
     * Initialize dependencies.
     *
     * @param CollectionFactory $userTypeCollectionFactory
     * @param void
     */
    public function __construct(
        CollectionFactory $userTypeCollectionFactory
    )
    {
        $this->userTypeCollectionFactory = $userTypeCollectionFactory;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $array = [];
        foreach ($this->toOptionArray() as $item) {
            $array[$item['value']] = $item['label'];
        }
        return $array;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = [];
            $collection = $this->userTypeCollectionFactory->create();
            $collection
                //->addFieldToFilter('status', 1)
                ->setOrder('user_type');

            foreach ($collection as $item) {
                $this->options[] = [
                    'label' => $item->getUserType() .
                        ($item->getStatus() ? '' : ' (' . __('Disabled') . ')'),
                    'value' => $item->getId(),
                ];
            }
        }

        return $this->options;
    }

    /**
     * Generate spaces
     * @param int $n
     * @return string
     */
    protected function _getSpaces($n)
    {
        $s = '';
        for ($i = 0; $i < $n; $i++) {
            $s .= '--- ';
        }

        return $s;
    }

}
