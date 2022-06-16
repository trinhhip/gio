<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Omnyfy\VendorAuth\Block\Adminhtml\Integration;

/**
 * Integration block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SelectVendor extends \Magento\Backend\Block\Template
{

    protected $vendorFactory;


    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Omnyfy\Vendor\Model\VendorFactory $vendorFactory
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Omnyfy\Vendor\Model\VendorFactory $vendorFactory,    
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->vendorFactory = $vendorFactory;
    }


    /*
     * return an array
     */
    public function getVendorIds()
    {
        $vendorCollection = $this->vendorFactory->create()->getCollection();
        $vendorIds = [''=>__('Select Vendor')];
        foreach($vendorCollection as $vendor){
            $vendorIds[$vendor->getData('entity_id')] = $vendor->getName();
        }
        return $vendorIds;
    }
}