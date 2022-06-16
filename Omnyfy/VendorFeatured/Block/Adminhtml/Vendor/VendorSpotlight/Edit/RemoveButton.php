<?php
namespace Omnyfy\VendorFeatured\Block\Adminhtml\Vendor\VendorSpotlight\Edit;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class RemoveButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getModelId()) {
            $data = [
                'label' => __('Remove All'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to do this? This action cannot be undone.'
                ) . '\', \'' . $this->getDeleteUrl() . '\')',
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * Get URL for delete button
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/removeall', ['vendor_id' => $this->getModelId()]);
    }
}