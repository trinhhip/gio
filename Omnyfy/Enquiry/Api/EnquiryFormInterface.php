<?php

namespace Omnyfy\Enquiry\Api;


interface EnquiryFormInterface
{
    public function __construct(\Omnyfy\Enquiry\Helper\Data $data);

    /**
     * Returns boolean to display the form
     *
     * @api
     * @param integer $vendor Vendor id.
     * @param integer $product Product id.
     * @return boolean Weather to display For.
     */
    public function isEnable($vendorId, $productId);
    /**
     * Returns the enquiry form to the user
     *
     * @api
     * @param integer $vendor Vendor id.
     * @param integer $product Product id.
     * @return string Enquiry Form.
     */
    public function getForm($vendor, $product);
}