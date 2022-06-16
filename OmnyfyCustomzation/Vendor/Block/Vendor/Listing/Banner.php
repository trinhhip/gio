<?php

namespace OmnyfyCustomzation\Vendor\Block\Vendor\Listing;

use OmnyfyCustomzation\Vendor\Helper\Data;

class Banner extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Data
     */
    public $helperData;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Data $helperData,
        array $data = []
    )
    {
        $this->helperData = $helperData;
        parent::__construct($context);
    }

    public function getBanner()
    {
        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $banner = $this->helperData->getBannerImage();
        if (!$banner) {
            return false;
        }
        return $mediaUrl . 'porto/sticky_logo/' . $banner;
    }

}
