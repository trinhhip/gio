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
class Edit extends \Magento\Integration\Block\Adminhtml\Integration\Edit
{

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Integration\Helper\Data $integrationHelper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $integrationHelper, $data);
    }
}