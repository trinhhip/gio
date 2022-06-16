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



namespace Mirasvit\RewardsCheckout\Plugin\Klarna\Base\Model\Checkout\Orderline;

/**
 * @see \Klarna\Base\Model\Checkout\Orderline\OrderLineFactory::create()
 *
 * Use this plugin for m2.1 and m2.2
 */
class AddRewardsOrderLineClassPlugin
{
    /**
     * @param \Klarna\Base\Model\Checkout\Orderline\OrderLineFactory $subject
     * @param \callable                                              $proceed
     * @param string                                                 $className
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCreate(
        \Klarna\Base\Model\Checkout\Orderline\OrderLineFactory $subject, $proceed, $className
    ) {
        if ($className !== '\Mirasvit\RewardsCheckout\Model\KlarnaCheckout\Orderline\Items\RewardsDiscount') {
            $result = $proceed($className);
        } else {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            $result = $objectManager->get('\Mirasvit\RewardsCheckout\Model\KlarnaCheckout\Orderline\Items\RewardsDiscount');
        }

        return $result;
    }
}
