<?php


namespace Omnyfy\Enquiry\Model;

use Omnyfy\Enquiry\Api\Data\EnquiriesInterface;

class Enquiries extends \Magento\Framework\Model\AbstractModel implements EnquiriesInterface
{
    protected $_eventPrefix = 'omnyfy_enquiry_enquiries';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Omnyfy\Enquiry\Model\ResourceModel\Enquiries');
    }

    /**
     * Get enquiries_id
     * @return string
     */
    public function getEnquiriesId()
    {
        return $this->getData(self::ENQUIRIES_ID);
    }

    /**
     * Set enquiries_id
     * @param string $enquiriesId
     * @return \Omnyfy\Enquiry\Api\Data\EnquiriesInterface
     */
    public function setEnquiriesId($enquiriesId)
    {
        return $this->setData(self::ENQUIRIES_ID, $enquiriesId);
    }

    /**
     * Get vendor_id
     * @return string
     */
    public function getVendorId()
    {
        return $this->getData(self::VENDOR_ID);
    }

    /**
     * Set vendor_id
     * @param string $vendorId
     * @return \Omnyfy\Enquiry\Api\Data\EnquiriesInterface
     */
    public function setVendorId($vendorId)
    {
        return $this->setData(self::VENDOR_ID, $vendorId);
    }

    /**
     * Get product_id
     * @return string
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * Set product_id
     * @param string $productId
     * @return \Omnyfy\Enquiry\Api\Data\EnquiriesInterface
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * Get customer_id
     * @return string
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * Set customer_id
     * @param string $customerId
     * @return \Omnyfy\Enquiry\Api\Data\EnquiriesInterface
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Get customer_first_name
     * @return string
     */
    public function getCustomerFirstName()
    {
        return $this->getData(self::CUSTOMER_FIRST_NAME);
    }

    /**
     * Set customer_first_name
     * @param string $customerFirstName
     * @return \Omnyfy\Enquiry\Api\Data\EnquiriesInterface
     */
    public function setCustomerFirstName($customerFirstName)
    {
        return $this->setData(self::CUSTOMER_FIRST_NAME, $customerFirstName);
    }

    /**
     * Get customer_last_name
     * @return string
     */
    public function getCustomerLastName()
    {
        return $this->getData(self::CUSTOMER_LAST_NAME);
    }

    /**
     * Set customer_last_name
     * @param string $customerLastName
     * @return \Omnyfy\Enquiry\Api\Data\EnquiriesInterface
     */
    public function setCustomerLastName($customerLastName)
    {
        return $this->setData(self::CUSTOMER_LAST_NAME, $customerLastName);
    }

    /**
     * Get customer_email
     * @return string
     */
    public function getCustomerEmail()
    {
        return $this->getData(self::CUSTOMER_EMAIL);
    }

    /**
     * Set customer_email
     * @param string $customerEmail
     * @return \Omnyfy\Enquiry\Api\Data\EnquiriesInterface
     */
    public function setCustomerEmail($customerEmail)
    {
        return $this->setData(self::CUSTOMER_EMAIL, $customerEmail);
    }

    /**
     * Get customer_mobile
     * @return string
     */
    public function getCustomerMobile()
    {
        return $this->getData(self::CUSTOMER_MOBILE);
    }

    /**
     * Set customer_mobile
     * @param string $customerMobile
     * @return \Omnyfy\Enquiry\Api\Data\EnquiriesInterface
     */
    public function setCustomerMobile($customerMobile)
    {
        return $this->setData(self::CUSTOMER_MOBILE, $customerMobile);
    }

    /**
     * Get customer_company
     * @return string
     */
    public function getCustomerCompany()
    {
        return $this->getData(self::CUSTOMER_COMPANY);
    }

    /**
     * Set customer_company
     * @param string $customerCompany
     * @return \Omnyfy\Enquiry\Api\Data\EnquiriesInterface
     */
    public function setCustomerCompany($customerCompany)
    {
        return $this->setData(self::CUSTOMER_COMPANY, $customerCompany);
    }

    /**
     * Get created_date
     * @return string
     */
    public function getCreatedDate()
    {
        return $this->getData(self::CREATED_DATE);
    }

    /**
     * Set created_date
     * @param string $createdDate
     * @return \Omnyfy\Enquiry\Api\Data\EnquiriesInterface
     */
    public function setCreatedDate($createdDate)
    {
        return $this->setData(self::CREATED_DATE, $createdDate);
    }

    /**
     * Get updated_date
     * @return string
     */
    public function getUpdatedDate()
    {
        return $this->getData(self::UPDATED_DATE);
    }

    /**
     * Set updated_date
     * @param string $updatedDate
     * @return \Omnyfy\Enquiry\Api\Data\EnquiriesInterface
     */
    public function setUpdatedDate($updatedDate)
    {
        return $this->setData(self::UPDATED_DATE, $updatedDate);
    }

    /**
     * Get status
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set status
     * @param string $status
     * @return \Omnyfy\Enquiry\Api\Data\EnquiriesInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get store_id
     * @return string
     */
    public function getStoreId() {
        return $this->getData(self::STORE_ID);
    }

    /**
     * Set store_id
     * @param string $storeId
     * @return \Omnyfy\Enquiry\Api\Data\EnquiriesInterface
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }
}
