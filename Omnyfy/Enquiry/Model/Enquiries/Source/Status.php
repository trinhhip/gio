<?php
/**
 * Created by PhpStorm.
 * User: Sanjaya-offline
 * Date: 5/4/2018
 * Time: 4:15 PM
 */

namespace Omnyfy\Enquiry\Model\Enquiries\Source;


class Status implements \Magento\Framework\Data\OptionSourceInterface
{

    const NEW_MESSAGE = 1;
    const OPEN_MESSAGE = 2;
    const COMPLETE_MESSAGE = 3;
    const ARCHIVE_MESSAGE = 4;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->getOptionArray();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }

    public static function getOptionArray()
    {
        return [1 => __('New'), 2 => __('Open'), 3=> __("Completed"), 4=>__("Achieved")];
    }
}