<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Block;

use Magento\Framework\View\Element\Template;

class Popup extends Template
{
    protected $_template = 'popup.phtml';

    /**
     * @return string
     */
    public function getTextUrl()
    {
        return $this->getUrl('gdpr/policy/policytext');
    }
}
