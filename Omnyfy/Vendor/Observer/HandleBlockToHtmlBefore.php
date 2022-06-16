<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_AdminActionsLog
 */


namespace Omnyfy\Vendor\Observer;

use Amasty\AdminActionsLog\Block\Adminhtml\ActionsLog\Tabs\Order;
use Amasty\AdminActionsLog\Block\Adminhtml\ActionsLog\Tabs\Product;
use Magento\Framework\Event\ObserverInterface;

class HandleBlockToHtmlBefore implements ObserverInterface
{
    public function __construct(
        \Magento\Framework\AuthorizationInterface $authorization
    )
    {
        $this->authorization = $authorization;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $block = $observer->getBlock();
        if($this->authorization->isAllowed('Amasty_AdminActionsLog::actions_log')) {
            if ($block instanceof \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs) {
                $this->_addBlock($block, Product::class, '');
            }
            if ($block instanceof \Magento\Sales\Block\Adminhtml\Order\View\Tabs) {
                $this->_addBlock($block, Order::class, '');
            }
        }

    }

    protected function _addBlock($block, $createdBlock, $lastElement)
    {
        if (method_exists($block, 'addTabAfter')) {
            $block->addTabAfter('tabid', [
                'label' => __('History of Changes'),
                'content' => $block->getLayout()
                    ->createBlock($createdBlock)->toHtml(),
            ], $lastElement);
        } else {
            $block->addTab('tabid', [
                'label' => __('History of Changes'),
                'content' => $block->getLayout()
                    ->createBlock($createdBlock)->toHtml(),
            ]);
        }
    }
}
