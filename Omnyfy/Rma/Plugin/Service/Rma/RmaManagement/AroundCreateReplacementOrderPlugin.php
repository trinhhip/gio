<?php

namespace Omnyfy\Rma\Plugin\Service\Rma\RmaManagement;

use Magento\Framework\Exception\LocalizedException;
use Mirasvit\Rma\Model\Carrier\RmaFree;

class AroundCreateReplacementOrderPlugin
{
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface
     */
    private $rmaManagement;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    private $cartManagement;
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;
    /**
     * @var \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface
     */
    private $itemManagement;
    /**
     * @var \Mirasvit\Rma\Api\Config\BackendConfigInterface
     */
    private $rmaBackendConfig;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface
     */
    private $rmaSearchManagement;

    private $shippingHelper;
    /**
     * @var \Omnyfy\Vendor\Model\Resource\Vendor
     */
    private $vendorResource;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mirasvit\Rma\Api\Config\BackendConfigInterface $rmaBackendConfig,
        \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface $itemManagement,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement,
        \Omnyfy\Vendor\Helper\Shipping $shippingHelper,
        \Omnyfy\Vendor\Model\Resource\Vendor $vendorResource
    ) {
        $this->rmaManagement = $rmaManagement;
        $this->storeManager = $storeManager;
        $this->cartManagement = $cartManagement;
        $this->cartRepository = $cartRepository;
        $this->customerRepository = $customerRepository;
        $this->itemManagement = $itemManagement;
        $this->productRepository = $productRepository;
        $this->rmaBackendConfig = $rmaBackendConfig;
        $this->rmaSearchManagement = $rmaSearchManagement;
        $this->shippingHelper = $shippingHelper;
        $this->vendorResource = $vendorResource;
    }

    public function aroundCreate(\Mirasvit\Rma\Service\Rma\RmaManagement\CreateReplacementOrder $subject, callable $proceed, $rma)
    {
        if (!$rma->getCustomerId()) {
            return $proceed();
        }
        $shippingConfiguration = $this->shippingHelper->getCalculateShippingBy();
        if ($shippingConfiguration == 'overall_cart') {
            $shippingPickupLocation = $this->shippingHelper->getShippingConfiguration('overall_pickup_location');
        }

        $paymentMethod = 'free';
        $originOrder = $this->rmaManagement->getOrder($rma);
        $orderStore = $this->storeManager->getStore($originOrder->getStoreId());

        $cartId = $this->cartManagement->createEmptyCart();
        /** @var \Magento\Quote\Model\Quote $cart */
        $cart = $this->cartRepository->get($cartId);
        $cart->setStore($orderStore);
        $cart->setCurrency($originOrder->getCurrency());
        if ($rma->getCustomerId()) {
            $customer = $this->customerRepository->getById($originOrder->getCustomerId());
            $cart->assignCustomer($customer);
            $cart->setCustomerIsGuest("0");
        } else {
            $cart->setCheckoutMethod('guest');
            $cart->setCustomerEmail($originOrder->getCustomerEmail());
        }

        $hasExchangeItems = false;
        $items = $this->getItems($rma);
        $sourceStockIds = [];
        foreach ($items as $item) {
            if ($this->itemManagement->isExchange($item)) {
                $product = $this->productRepository->get($item->getProductSku());
                $product->setPrice(0);
                $cart->addProduct($product, $item->getQtyRequested());
                $hasExchangeItems = true;
                $cart->getItemByProduct($product)->setVendorId($this->vendorResource->getVendorIdByProductId($product->getId()));
                $sourceStockIds[]= $cart->getItemByProduct($product)->getSourceStockId();
            }
        }
        $sourceStockIds = array_unique($sourceStockIds);
        $shippingMethod = [];
        if(empty($shippingPickupLocation)){
            foreach ($sourceStockIds as $sourceStockId){
                $shippingMethod[$sourceStockId] =  RmaFree::SHIPPING_CODE . '_' . RmaFree::SHIPPING_CODE;
            }
        }else{
            $shippingMethod = [$shippingPickupLocation => RmaFree::SHIPPING_CODE . '_' . RmaFree::SHIPPING_CODE];
        }
        $shippingMethod = json_encode($shippingMethod);
        if (!$hasExchangeItems) {
            throw new LocalizedException(
                __('At least one RMA item should have the resolution "Exchange".')
            );
        }
        if (!$this->rmaBackendConfig->isRmaFreeShippingEnabled($orderStore->getWebsiteId())) {
            throw new LocalizedException(
                __('"RMA Free Shipping" method is required.')
            );
        }
        $cart->getBillingAddress()->addData($originOrder->getBillingAddress()->getData());
        if ($originOrder->getShippingAddress()) {
            $cart->getShippingAddress()->addData($originOrder->getShippingAddress()->getData());
            $cart->getShippingAddress()->setCollectShippingRates(true)->collectShippingRates()
                ->setShippingMethod($shippingMethod);
        }

        $cart->setPaymentMethod($paymentMethod);
        $cart->setInventoryProcessed(false);
        $cart->getPayment()->importData(['method' => $paymentMethod]);

        // Collect total and save
        $cart->collectTotals();

        // Submit the quote and create the order
        $cart->save();
        $cart = $this->cartRepository->get($cart->getId());
        $orderId = $this->cartManagement->placeOrder($cart->getId());
        $replacementOrderIds = $rma->getReplacementOrderIds();
        $replacementOrderIds[] = $orderId;
        $rma->setReplacementOrderIds($replacementOrderIds);
        $rma->save();

        return $orderId;
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return \Mirasvit\Rma\Api\Data\ItemInterface[]
     */
    private function getItems(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        return $this->rmaSearchManagement->getRequestedItems($rma);
    }

}