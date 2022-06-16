<?php


namespace Omnyfy\Enquiry\Api\Data;

interface EnquiriesInterface
{

    const CUSTOMER_EMAIL = 'customer_email';
    const CREATED_DATE = 'created_date';
    const UPDATED_DATE = 'updated_date';
    const VENDOR_ID = 'vendor_id';
    const CUSTOMER_COMPANY = 'customer_company';
    const ENQUIRIES_ID = 'enquiries_id';
    const CUSTOMER_ID = 'customer_id';
    const CUSTOMER_MOBILE = 'customer_mobile';
    const PRODUCT_ID = 'product_id';
    const CUSTOMER_FIRST_NAME = 'customer_first_name';
    const CUSTOMER_LAST_NAME = 'customer_last_name';
    const STATUS = 'status';
    const STORE_ID = 'store_id';


    /**
     * Get enquiries_id
     * @return string|null
     */
    public function getEnquiriesId();

    /**
     * Set enquiries_id
     * @param string $enquiriesId
     * @return \Omnyfy\Enquiry\Api\Data\EnquiriesInterface
     */
    public function setEnquiriesId($enquiriesId);

    /**
     * Get vendor_id
     * @return string|null
     */
    public function getVendorId();

    /**
     * Set vendor_id
     * @param string $vendorId
     * @return \Omnyfy\Enquiry\Api\Data\EnquiriesInterface
     */
    public function setVendorId($vendorId);

    /**
     * Get product_id
     * @return string|null
     */
    public function getProductId();

    /**
     * Set product_id
     * @param string $productId
     * @return \Omnyfy\Enquiry\Api\Data\EnquiriesInterface
     */
    public function setProductId($productId);

    /**
     * Get customer_id
     * @return string|null
     */
    public function getCustomerId();

    /**
     * Set customer_id
     * @param string $customerId
     * @return \Omnyfy\Enquiry\Api\Data\EnquiriesInterface
     */
    public function setCustomerId($customerId);

    /**
     * Get customer_first_name
     * @return string|null
     */
    public function getCustomerFirstName();

    /**
     * Set customer_first_name
     * @param string $customerFirstName
     * @return \Omnyfy\Enquiry\Api\Data\EnquiriesInterface
     */
    public function setCustomerFirstName($customerFirstName);

    /**
     * Get customer_last_name
     * @return string|null
     */
    public function getCustomerLastName();

    /**
     * Set customer_last_name
     * @param string $customerLastName
     * @return \Omnyfy\Enquiry\Api\Data\EnquiriesInterface
     */
    public function setCustomerLastName($customerLastName);

    /**
     * Get customer_email
     * @return string|null
     */
    public function getCustomerEmail();

    /**
     * Set customer_email
     * @param string $customerEmail
     * @return \Omnyfy\Enquiry\Api\Data\EnquiriesInterface
     */
    public function setCustomerEmail($customerEmail);

    /**
     * Get customer_mobile
     * @return string|null
     */
    public function getCustomerMobile();

    /**
     * Set customer_mobile
     * @param string $customerMobile
     * @return \Omnyfy\Enquiry\Api\Data\EnquiriesInterface
     */
    public function setCustomerMobile($customerMobile);

    /**
     * Get customer_company
     * @return string|null
     */
    public function getCustomerCompany();

    /**
     * Set customer_company
     * @param string $customerCompany
     * @return \Omnyfy\Enquiry\Api\Data\EnquiriesInterface
     */
    public function setCustomerCompany($customerCompany);

    /**
     * Get created_date
     * @return string|null
     */
    public function getCreatedDate();

    /**
     * Set created_date
     * @param string $createdDate
     * @return \Omnyfy\Enquiry\Api\Data\EnquiriesInterface
     */
    public function setCreatedDate($createdDate);

    /**
     * Get updated_date
     * @return string|null
     */
    public function getUpdatedDate();

    /**
     * Set updated_date
     * @param string $updatedDate
     * @return \Omnyfy\Enquiry\Api\Data\EnquiriesInterface
     */
    public function setUpdatedDate($updatedDate);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Omnyfy\Enquiry\Api\Data\EnquiriesInterface
     */
    public function setStatus($status);

    /**
     * Get store_id
     * @return string|null
     */
    public function getStoreId();

    /**
     * Set store_id
     * @param string $store_id
     * @return \Omnyfy\Enquiry\Api\Data\EnquiriesInterface
     */
    public function setStoreId($store_id);
}
