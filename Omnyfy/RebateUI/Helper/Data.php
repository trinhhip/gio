<?php

namespace Omnyfy\RebateUI\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class Data
 * @package Omnyfy\RebateUI\Helper
 */
class Data extends AbstractHelper {

    protected $_storeManager;

    protected $priceCurrency;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrencyInterface
    ) {
        $this->_storeManager = $storeManager;
        $this->priceCurrency = $priceCurrencyInterface;
        parent::__construct($context);
    }

    public function formatToBaseCurrency($amount = 0) {
        $baseCurrency = $this->_storeManager->getStore()->getBaseCurrency()->getCode();
        return $this->priceCurrency->format($amount, false, null, null, $baseCurrency);
    }
}
