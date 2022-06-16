<?php
namespace Omnyfy\VendorAuth\Block\Adminhtml\Integration\Button;

class SelectVendorButton extends \Magento\Backend\Block\Widget\Container
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
            'omnyfy_vendorauth_select_vendor',
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
            'label' => __('Select Vendor for New Integration'),
            'on_click' => sprintf("location.href = '%s';", $this->getUrl('omnyfy_vendorauth/integration/selectVendor')),
            'class' => 'primary',
            'sort_order' => 20
        ];
    }
}