<?php


namespace Omnyfy\Enquiry\Block\Adminhtml\Enquiries\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class CompleteButton extends GenericButton implements ButtonProviderInterface
{

    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getModelId()) {
            $data = [
                'label' => __('Complete'),
                'class' => 'complete',
                'on_click' => sprintf("location.href = '%s';", $this->getCompleteUrl()),
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * Get URL for complete button
     *
     * @return string
     */
    public function getCompleteUrl()
    {
        return $this->getUrl('*/*/complete', ['enquiries_id' => $this->getModelId()]);
    }
}
