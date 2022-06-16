<?php

namespace Omnyfy\RebateCore\Observer;

use Magento\Framework\Exception\LocalizedException;
use Omnyfy\RebateCore\Helper\Data;

/**
 * Class AfterCreditmemoSave
 * @package Omnyfy\RebateCore\Observer
 */
class AfterCreditmemoSave implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * AfterCreditmemoSave constructor.
     */
    public function __construct(
        Data $helper,
        \Magento\Framework\Registry $registry
    )
    {
        $this->helper = $helper;
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->isEnable()) {
            $creditmemo = $observer->getEvent()->getCreditmemo();
            $this->registry->register('creditmemo_save_after', $creditmemo);
        }
    }

}
 