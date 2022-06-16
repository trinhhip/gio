<?php

namespace Omnyfy\Mcm\Model\Config\Source;

use Magento\Authorization\Model\UserContextInterface;

class PayoutBasisType extends \Magento\Framework\DataObject implements \Magento\Framework\Option\ArrayInterface
{
    const WHOLESALE_VENDOR_VALUE = 1;

    const COMMISSION_VENDOR_VALUE = 0;

    /**
     * @param array $data
     */
    public function __construct(
        array $data = []
    )
    {
        parent::__construct($data);
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'label' => __("Wholesale Vendor"),
                'value' => $this::WHOLESALE_VENDOR_VALUE
            ],
            [
                'label' => __("Commission Vendor"),
                'value' => $this::COMMISSION_VENDOR_VALUE
            ]
        ];
        return $options;
    }
}
