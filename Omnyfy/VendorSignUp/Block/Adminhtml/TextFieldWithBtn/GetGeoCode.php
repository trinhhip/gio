<?php

namespace Omnyfy\VendorSignUp\Block\Adminhtml\TextFieldWithBtn;
use Magento\Backend\Block\Template;
use Magento\Backend\Model\UrlInterface;


class GetGeoCode extends Template
{
    protected $_template = 'Omnyfy_VendorSignUp::signup/geocodebtn.phtml';
    private UrlInterface $url;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        UrlInterface $url,
        array $data = []
    ) {
        $this->url = $url;
        parent::__construct($context, $data);
    }

    public function fetchGeoCodeUrl(){
        return $this->url->getUrl('omnyfy_vendorsignup/signup/fetchgeocode');
    }

    public function getVendorId(){
        return $this->getRequest()->getParam('id');
    }
}