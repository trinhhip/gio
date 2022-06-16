<?php
/**
 * Project: Approval
 * User: jing
 * Date: 2019-08-21
 * Time: 17:01
 */
namespace Omnyfy\Approval\Block\Adminhtml\Record\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Omnyfy\Core\Block\Adminhtml\Button;

class Back extends Button implements ButtonProviderInterface
{
    public function getButtonData()
    {
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->getUrl('catalog/product/')),
            'class' => 'back',
            'sort_order' => 10
        ];
    }
}
 