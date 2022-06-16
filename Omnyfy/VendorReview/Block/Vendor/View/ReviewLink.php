<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Omnyfy\VendorReview\Block\Vendor\View;

class ReviewLink extends \Omnyfy\Core\Block\Element\Html\Link\PageSectionLink
{

    /**
     * @var \Omnyfy\Vendor\Block\Vendor\View
     */
    private $_vendorView;

    public function __construct(
        \Omnyfy\VendorReview\Helper\Vendor $vendorHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    )
    {
        $this->_vendorHelper = $vendorHelper;
        parent::__construct($context, $data);
    }

    /**
     * Check if vendor reviews are enabled in configuration and in vendor
     *
     * @return boolean
     */
    private function shouldDisplayReviews()
    {
        return $this->_vendorHelper->isVendorReviewEnabled();
    }


    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (false != $this->getTemplate()) {
            return parent::_toHtml();
        }

        if ($this->shouldDisplayReviews()) {
            return '<li><a ' . $this->getLinkAttributes() . ' >' . $this->escapeHtml($this->getLabel()) . '</a></li>';
        } else {
            return '';
        }
    }

}