<?php
namespace Amasty\Meta\Block\Adminhtml\Widget\Grid\Column\Renderer;

class Store
    extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Store

{
    public function render(\Magento\Framework\DataObject $row)
    {
        $out = parent::render($row);
        if (empty($out)) {
            return __('Default');
        }

        return $out;
    }
}
