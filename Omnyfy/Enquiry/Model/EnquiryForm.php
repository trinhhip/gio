<?php
/**
 * Created by PhpStorm.
 * User: Sanjaya-offline
 * Date: 4/30/2018
 * Time: 3:19 PM
 */

namespace Omnyfy\Enquiry\Model;

use Omnyfy\Enquiry\Api\EnquiryFormInterface;


class EnquiryForm implements EnquiryFormInterface
{
    private $_data;

    public function __construct(\Omnyfy\Enquiry\Helper\Data $data)
    {
        $this->_data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnable($vendorId, $productId) {
        //Check if the Manage Enquiry Module is enabled
        if($this->_data->isEnabled($vendorId)) {
            if ($productId == 0) {
                if ($this->_data->isVendorEnabled($vendorId)) {
                    return true;
                }
            } else {
                if ($this->_data->isProductEnabled($vendorId, $productId)) {
                    return true;
                }
            }
        }
        return false;
    }



    /**
     * {@inheritdoc}
     */
    public function getForm($vendor, $product) {
        return $this->isEnable($vendor, $product);
    }
}