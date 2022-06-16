<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Country\CollectionFactory;

/**
 * Used in recent article widget
 *
 */
class CountryList implements ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    protected $countryCollectionFactory;

    /**
     * @var array
     */
    protected $options;

    /**
     * Initialize dependencies.
     *
     * @param CollectionFactory $countryCollectionFactory
     * @param void
     */
    public function __construct(
        CollectionFactory $countryCollectionFactory
    )
    {
        $this->countryCollectionFactory = $countryCollectionFactory;
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
            $collection = $this->countryCollectionFactory->create()->addFieldToFilter('status', 1);
            $collection->getSelect()->order('country_name', 'ASC');

            foreach ($collection as $item) {
                $this->options[] = [
                    'label' => $item->getCountryName(),
                    'value' => $item->getId(),
                ];
            }
        }

        return $this->options;
    }
}
