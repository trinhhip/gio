<?php
/**
 * Project: Approval
 * User: jing
 * Date: 2019-08-19
 * Time: 15:29
 */
namespace Omnyfy\Approval\Model\Source;

class Status extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const STATUS_SUBMITTED_TO_REVIEW = 0;
    const STATUS_REVIEW_PASSED = 1;
    const STATUS_REVIEW_FAILED = 2;
    const STATUS_IN_PUBLISHING = 3;

    public function toValuesArray()
    {
        return [
            self::STATUS_SUBMITTED_TO_REVIEW => __('Submitted to review'),
            self::STATUS_REVIEW_PASSED => __('Review passed'),
            self::STATUS_REVIEW_FAILED => __('Review failed'),
            self::STATUS_IN_PUBLISHING => __('In publishing')
        ];
    }

    public function toOptionArray()
    {
        $result = [];
        foreach($this->toValuesArray() as $key => $value) {
            $result[] = [
                'value' => $key,
                'label' => $value
            ];
        }
        return $result;
    }

    public function getAllOptions()
    {
        return $this->toOptionArray();
    }
}
