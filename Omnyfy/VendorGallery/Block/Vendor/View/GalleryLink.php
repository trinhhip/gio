<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Omnyfy\VendorGallery\Block\Vendor\View;

class GalleryLink extends \Omnyfy\Core\Block\Element\Html\Link\PageSectionLink {

    /**
     * @var \Omnyfy\VendorGallery\Block\Vendor\View
     */
    private $_vendorView;

    public function __construct(
        \Omnyfy\VendorGallery\Block\Vendor\View $vendorView,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->_vendorView = $vendorView;
        parent::__construct($context, $data);
    }

    public function shouldDisplayGallery() {
        return (boolean)count($this->_vendorView->getAlbumCollection());
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

        if ($this->shouldDisplayGallery()) {
            return '<li><a ' . $this->getLinkAttributes() . ' >' . $this->escapeHtml($this->getLabel()) . '</a></li>';
        } else {
            return '';
        }
    }
}