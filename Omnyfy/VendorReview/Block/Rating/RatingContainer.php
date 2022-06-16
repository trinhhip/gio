<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Omnyfy\VendorReview\Block\Rating;

use Magento\Framework\View\Element\Template;

class RatingContainer extends \Magento\Framework\View\Element\Template
{

    public function __construct(
        Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    public function getContainerHtml($vendorId){
        $childNames = $this->getChildNames();
        $html = '';
        foreach ($childNames as $childName){
            $html .= $this->getChildBlock($childName)->setData('vendor_id', $vendorId)->toHtml();
        }
        return $html;
    }

}