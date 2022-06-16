<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


declare(strict_types=1);

namespace Amasty\Groupcat\Model\Rule\Condition;

use Magento\Framework\View\LayoutInterface;

class TooltipRenderer
{
    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var string|null
     */
    private $tooltipTemplate;

    public function __construct(
        LayoutInterface $layout,
        string $tooltipTemplate = null
    ) {
        $this->layout = $layout;
        $this->tooltipTemplate = $tooltipTemplate;
    }

    public function renderTooltip(): string
    {
        if ($this->tooltipTemplate) {
            return $this->layout->createBlock(\Magento\Backend\Block\Template::class)
                ->setTemplate($this->tooltipTemplate)
                ->toHtml();
        }

        return '';
    }
}
