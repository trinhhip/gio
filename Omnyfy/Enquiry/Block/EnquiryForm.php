<?php
/**
 * Created by PhpStorm.
 * User: Sanjaya-offline
 * Date: 5/24/2018
 * Time: 11:09 AM
 */

namespace Omnyfy\Enquiry\Block;


class EnquiryForm extends \Magento\Framework\View\Element\Template
{
    protected $_enquiryForm;
    protected $_catalogProduct;
    protected $_vendorResource;
    protected $_customerSession;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Omnyfy\Enquiry\Model\EnquiryForm $enquiryForm,
        \Magento\Catalog\Block\Product\View\AbstractView $catalogProductAbstractView,
        \Omnyfy\Vendor\Model\Resource\Vendor $vendorResource,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    )
    {
        $this->_enquiryForm = $enquiryForm;
        $this->_catalogProduct = $catalogProductAbstractView;
        $this->_vendorResource = $vendorResource;
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    public function isEnable($vendorId, $productId){
        return $this->_enquiryForm->isEnable($vendorId, $productId);
    }

    public function getProductId(){
        $product = $this->_catalogProduct->getProduct();
        return empty($product) ? 0 : $product->getId();
    }

    public function getVendorId($productId) {
        if (empty($productId)) {
            //TODO: load vendor Id from parameter
            $vendorId = $this->getRequest()->getParam('vendor_id');
            $vendorId = empty($vendorId) ? $this->getRequest()->getParam('id') : $vendorId;
            return $vendorId;
        }
        return $this->_vendorResource->getVendorIdByProductId($productId);
    }

    /**
     * This url will return a blank page
     * @return string
     */
    public function getFormSubmitUrl(){
        return $this->getUrl('enquiry/enquiry/save');
    }

    public function getFormSaveUrl() {
        return $this->getUrl('enquiry/index/enquiry', ['_secure' => true]);
    }

    public function getCustomerId(){
        $customerId = $this->_customerSession->getCustomer()->getId();
        if ($customerId)
            return $this->_customerSession->getCustomer()->getId();
        return 0;
    }

    public function getStoreId() {
        return $this->_storeManager->getStore()->getId();
    }
}
