<?php
namespace Omnyfy\VendorFeatured\Block\Adminhtml\Vendor\SpotlightAssign\Edit;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class AssignButton extends GenericButton implements ButtonProviderInterface
{

    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'assign']],
                'form-role' => 'save',
            ],
            'sort_order' => 90,
        ];
    }
}