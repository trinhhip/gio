<?php

namespace Omnyfy\Mcm\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class CartSaveAfter implements ObserverInterface
{
    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;
    /**
     * @var Session
     */
    protected $checkoutSession;

    public function __construct(
        CartRepositoryInterface $quoteRepository,
        Session $checkoutSession
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->checkoutSession = $checkoutSession;
    }

    public function execute(EventObserver $observer)
    {
        $cart = $observer->getData('cart');
        $cart->getQuote()->setTotalsCollectedFlag(false);
        $cart->getQuote()->collectTotals();
        $this->quoteRepository->save($cart->getQuote());
        $this->checkoutSession->setQuoteId($cart->getQuote()->getId());
    }
}


