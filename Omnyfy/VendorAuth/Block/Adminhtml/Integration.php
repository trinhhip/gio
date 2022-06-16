<?php
/**
 * Copyright © Omnyfy, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Omnyfy\VendorAuth\Block\Adminhtml;

use Magento\Backend\Block\Widget\Context;

/**
 * Integration block.
 *
 * @api
 * @codeCoverageIgnore
 * @since 100.0.2
 */
class Integration extends \Magento\Backend\Block\Widget\Grid\Container
{

    public function __construct(
        Context $context,
        array $data = [])
    {
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_controller = 'adminhtml_integration';
        $this->_blockGroup = 'Magento_Integration';
        $this->_headerText = __('Vendor\' Integrations');
        parent::_construct();
        $this->buttonList->remove('add');
    }

}
