<?php
namespace Omnyfy\VendorAuth\Block\Adminhtml\Integration\Button;

class ConfirmSelectVendorButton extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        $this->_request = $context->getRequest();
        parent::__construct($context, $data);
    }

    /**
     * Block constructor adds buttons
     *
     */
    protected function _construct()
    {
        $this->addButton(
            'integration_vendor_button',
            $this->getButtonData()
        );
        parent::_construct();
    }

    /**
     * Return button attributes array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Create New Integration for Vendor'),
            'class' => 'primary',
            'sort_order' => 20
        ];
    }
}