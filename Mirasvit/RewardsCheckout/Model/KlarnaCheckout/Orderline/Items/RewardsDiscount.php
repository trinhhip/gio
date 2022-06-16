<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rewards
 * @version   3.0.24
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\RewardsCheckout\Model\KlarnaCheckout\Orderline\Items;

use /** @noinspection PhpUndefinedNamespaceInspection */ Klarna\Base\Model\Api\Parameter;
use /** @noinspection PhpUndefinedNamespaceInspection */ Klarna\Base\Model\Checkout\Orderline\DataHolder;
use /** @noinspection PhpUndefinedNamespaceInspection */ Klarna\Base\Helper\DataConverter;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;

if (interface_exists('\Klarna\Base\Api\OrderLineInterface', false)) {
    /** @noinspection PhpUndefinedNamespaceInspection */

    abstract class AbstractLineMediator implements \Klarna\Base\Api\OrderLineInterface{

        protected $registry;

        protected $helper;

        public function __construct(
            /** @noinspection PhpUndefinedNamespaceInspection */ \Klarna\Base\Helper\DataConverter $helper,
            \Mirasvit\RewardsCheckout\Registry $registry
        ) {
            $this->helper   = $helper;
            $this->registry = $registry;
        }
    }
} else {
    abstract class AbstractLineMediator {}
}

class RewardsDiscount extends AbstractLineMediator
{
    const ITEM_TYPE_REWARDS = 'rewards_calculations';

    /**
     * Order line code name
     *
     * @var string
     */
    private $code;

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function collectPrePurchase(Parameter $parameter, DataHolder $dataHolder, CartInterface $quote)
    {
        return $this->collect($parameter, $dataHolder);
    }

    /**
     * {@inheritdoc}
     */
    public function collectPostPurchase(Parameter $parameter, DataHolder $dataHolder, OrderInterface $order)
    {
        return $this->collect($parameter, $dataHolder);
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Parameter $checkout, DataHolder $dataHolder)
    {
        $totals = $dataHolder->getTotals();

        if (is_array($totals) && isset($totals['rewards_calculations'])) {
            $total = $totals['rewards_calculations'];
            $value = $this->helper->toApiFloat($total->getValue());
            $title = __('Rewards Discount')->getText();

            $this->registry->setKlarnaOrderLine([
                'type'             => 'discount',
                'reference'        => $total->getCode(),
                'name'             => $title,
                'quantity'         => 1,
                'unit_price'       => $value,
                'tax_rate'         => 0,
                'total_amount'     => $value,
                'total_tax_amount' => 0,
            ]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(Parameter $checkout)
    {
        $rewardsLine = $this->registry->getKlarnaOrderLine();

        if (isset($rewardsLine['total_amount'])) {
            $checkout->addOrderLine($rewardsLine);
        }

        return $this;
    }
}