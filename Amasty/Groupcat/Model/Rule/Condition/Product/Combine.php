<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


declare(strict_types=1);

namespace Amasty\Groupcat\Model\Rule\Condition\Product;

use Amasty\Groupcat\Model\Rule\Condition;

class Combine extends \Magento\CatalogRule\Model\Rule\Condition\Combine
{
    /**
     * @var Condition\TooltipRendererFactory
     */
    private $tooltipRendererFactory;

    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $conditionFactory,
        Condition\TooltipRendererFactory $tooltipRendererFactory,
        array $data = []
    ) {
        parent::__construct($context, $conditionFactory, $data);
        $this->tooltipRendererFactory = $tooltipRendererFactory;
        $this->setType(\Amasty\Groupcat\Model\Rule\Condition\Product\Combine::class);
    }

    public function asHtml()
    {
        /** @var Condition\TooltipRenderer $tooltipRenderer */
        $tooltipRenderer = $this->tooltipRendererFactory->create(
            [
                'tooltipTemplate' => 'Amasty_Groupcat::rule/tooltip/product.phtml'
            ]
        );

        return parent::asHtml() . $tooltipRenderer->renderTooltip();
    }
}
