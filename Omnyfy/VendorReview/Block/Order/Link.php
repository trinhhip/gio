<?php

namespace Omnyfy\VendorReview\Block\Order;

/**
 * Sales order link
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 100.0.2
 */
class Link extends \Magento\Sales\Block\Order\Link
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    protected $helper;

    protected $helperVendor;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Magento\Framework\Registry $registry,
        \Omnyfy\VendorReview\Helper\Data $helper,
        \Omnyfy\VendorReview\Helper\Vendor $helperVendor,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $registry, $data);
        $this->helper = $helper;
        $this->helperVendor = $helperVendor;
    }

    public function getOrderStatus() {
        return $this->_registry->registry('current_order')->getStatus();
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    protected function _toHtml()
    {

        if($this->getKey() == 'review') {
            if(!$this->helperVendor->isVendorReviewEnabled()) {
                return '';
            }
            if(!$this->helper->isDisplayVendorReviewOnOrder() && !$this->helper->isDisplayProductReviewOnOrder()) {
                return '';
            }
            if(!$this->helper->isDisplayVendorReviewOnOrder() && $this->getOrderStatus() != 'complete') {
                return '';
            }

            $label = $this->helper->getTitleHeaderTab() ? $this->helper->getTitleHeaderTab() : __('Leave Feedback');

            
            $highlight = '';

            if ($this->getIsHighlighted()) {
                $highlight = ' current';
            }

            if ($this->isCurrent()) {
                $html = '<li class="nav item current">';
                $html .= '<strong>'
                    . $this->escapeHtml(__($label))
                    . '</strong>';
                $html .= '</li>';
            } else {
                $html = '<li class="nav item' . $highlight . '"><a href="' . $this->escapeHtml($this->getHref()) . '"';
                $html .= $label
                    ? ' title="' . $this->escapeHtml(__($label)) . '"'
                    : '';
                $html .= $this->getAttributesHtml() . '>';

                if ($this->getIsHighlighted()) {
                    $html .= '<strong>';
                }

                $html .= $this->escapeHtml(__($label));

                if ($this->getIsHighlighted()) {
                    $html .= '</strong>';
                }

                $html .= '</a></li>';
            }

            return $html;

        }
        return parent::_toHtml();
    }
}
