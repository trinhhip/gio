<?php
namespace OmnyfyCustomzation\BuyerApproval\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class TypeNotApprove
 * @package OmnyfyCustomzation\BuyerApproval\Model\Config\Source
 */
class TypeNotApprove implements ArrayInterface
{
    const SHOW_ERROR = 'show_error';
    const REDIRECT_PAGE = 'redirect_page';

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
            self::SHOW_ERROR => __('Show Error'),
            self::REDIRECT_PAGE => __('Redirect Page')
        ];
    }
}
