<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Omnyfy\VendorSignUp\Block\SignUp\Renderers;

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * Class Text
 * @package Omnyfy\VendorSignUp\Block\Block\SignUp\Renderers
 */
class Renderer extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element implements RendererInterface
{
    protected $_template = 'signup/renderers/renderer.phtml';
}