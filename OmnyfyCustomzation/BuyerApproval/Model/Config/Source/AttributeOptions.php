<?php
namespace OmnyfyCustomzation\BuyerApproval\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class AttributeOptions
 * @package OmnyfyCustomzation\BuyerApproval\Model\Config\Source
 */
class AttributeOptions extends AbstractSource
{
    const PENDING = 'pending';
    const APPROVED = 'approved';
    const NOTAPPROVE = 'notapproved';
    const NEW_STATUS = 'new';
    const RETAIL_TO_TRADE = 'retail_upgrade';
    const UNREGISTERED = 'unregistered';

    /**
     * @return array
     */
    public function getAllOptions()
    {
        $options = [];

        foreach ($this->toArray() as $key => $label) {
            $options[] = [
                'value' => $key,
                'label' => $label
            ];
        }

        return $options;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            self::UNREGISTERED => __('Unregistered'),
            self::PENDING => __('Pending'),
            self::RETAIL_TO_TRADE => __('Retail To Trade'),
            self::APPROVED => __('Approved'),
            self::NOTAPPROVE => __('Not Approved'),
        ];
    }
}
