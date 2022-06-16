<?php
namespace Omnyfy\Easyship\Block\Adminhtml\Config;
use Magento\Framework\Data\Form\Element\AbstractElement;


class SyncShippingCategory extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var string
     */
    protected $_template = 'Omnyfy_Easyship::system/config/sync_shipping_category.phtml';

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Remove scope label
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for sync button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('omnyfy_easyship/system_config/syncshippingcategory');
    }

    /**
     * Generate sync button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'synccategory_button',
                'label' => __('Sync Shipping Category'),
            ]
        );

        return $button->toHtml();
    }
}
