<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Block\Adminhtml\Sitemap;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class ResetButton extends GenericButton implements ButtonProviderInterface
{
    public function getButtonData(): array
    {
        return [
            'label' => __('Reset'),
            'class' => 'reset',
            'on_click' => 'location.reload();',
            'sort_order' => 2
        ];
    }
}
