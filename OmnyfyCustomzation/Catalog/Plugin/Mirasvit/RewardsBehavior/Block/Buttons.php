<?php


namespace OmnyfyCustomzation\Catalog\Plugin\Mirasvit\RewardsBehavior\Block;

/**
 * Class Buttons
 *
 * @package OmnyfyCustomzation\Catalog\Plugin\Mirasvit\RewardsBehavior\Block
 */
class Buttons extends \Mirasvit\RewardsBehavior\Block\Buttons\AbstractButtons
{
    /**
     * @return bool
     */
    public function aroundIsPinActive()
    {
        return ($this->context->getRequest()->getActionName() == 'view'
                && in_array($this->context->getRequest()->getControllerName(), ['catalog_product', 'product'])
            ) && $this->getConfig()->getPinterestIsActive();
    }
}