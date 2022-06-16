<?php
namespace OmnyfyCustomzation\BuyerApproval\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class TypeAction
 * @package OmnyfyCustomzation\BuyerApproval\Model\Config\Source
 */
class TypeAction implements ArrayInterface
{
    const COMMAND = 'command';
    const API = 'api';
    const OTHER = 'other';
    const EDITCUSTOMER = 'edit_customer';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->toArray() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $options;
    }

    /**
     * @return array
     */
    protected function toArray()
    {
        return [
            self::COMMAND => __('Command'),
            self::API => __('Api'),
            self::OTHER => __('Other'),
            self::EDITCUSTOMER => __('Edit Customer')
        ];
    }
}
