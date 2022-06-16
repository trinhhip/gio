<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\User\Model\ResourceModel\User\CollectionFactory;

/**
 * Authors list
 *
 */
class Author implements ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    protected $authorCollectionFactory;

    /**
     * @var array
     */
    protected $options;

    /**
     * Initialize dependencies.
     *
     * @param CollectionFactory $authorCollectionFactory
     * @param void
     */
    public function __construct(
        CollectionFactory $authorCollectionFactory
    )
    {
        $this->authorCollectionFactory = $authorCollectionFactory;
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
            $this->options = [['label' => __('Please select'), 'value' => 0]];
            $collection = $this->authorCollectionFactory->create();

            foreach ($collection as $item) {
                $this->options[] = [
                    'label' => $item->getName(),
                    'value' => $item->getId(),
                ];
            }
        }

        return $this->options;
    }


}
