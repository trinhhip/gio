<?php
/**
 * Project: Omnyfy Multi Vendor.
 * User: jing
 * Date: 5/5/17
 * Time: 4:26 PM
 */
namespace Omnyfy\Vendor\Plugin\Quote\Model\Quote\Address\Total;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class Shipping
{
    protected $priceCurrency;

    protected $helper;

    protected $locationHelper;

    protected $vSourceStock;
    protected $request;

    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        \Omnyfy\Vendor\Helper\Data $helper,
        \Omnyfy\Vendor\Helper\Location $locationHelper,
        \Omnyfy\Vendor\Model\VendorSourceStock $vSourceStock,
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->priceCurrency = $priceCurrency;
        $this->helper = $helper;
        $this->locationHelper = $locationHelper;
        $this->vSourceStock = $vSourceStock;
        $this->request = $request;
    }

    public function aroundCollect(
            $subject,
            callable $proceed,
            \Magento\Quote\Model\Quote $quote,
            \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
            \Magento\Quote\Model\Quote\Address\Total $total
        )
    {
        $address = $shippingAssignment->getShipping()->getAddress();
        $method = $shippingAssignment->getShipping()->getMethod();
        if ('_' === $method) {
            $method = null;
        }

        $result = $proceed($quote, $shippingAssignment, $total);

        if ($quote->getIsMultiShipping()) {
            //return $result;
        }

        $compareLocation = true;
        if (is_array($method)) {
            $methods = $method;
            $address->setShippingMethod($this->helper->shippingMethodArrayToString($method));
        }
        elseif (is_string($method)) {
            $address->setShippingMethod($method);
            if ('{' == substr($method, 0, 1)) {
                $methods = $this->helper->shippingMethodStringToArray($method);
            }
            else {
                $methods = [$method];
                $compareLocation = false;
            }
        }
        else{
            $methods = [];
        }

        if (!empty($methods)) {
            $shippingDescription = '';
            $totalAmount = $baseTotal = $shippingAmount = $baseShippingAmount = 0;
            $shippingRates =$address->getAllShippingRates();
            $data = [];
            foreach($methods as $sourceStockId => $methodCode) {
                foreach ($shippingRates as $rate) {
                    if ((!$compareLocation || $rate->getLocationId() == $sourceStockId) && $rate->getCode() == $methodCode) {
                        $store = $quote->getStore();
                        $amountPrice = $this->priceCurrency->convert(
                            $rate->getPrice(),
                            $store
                        );

                        if($this->request->getFullActionName() == 'quotation_quote_loadBlock' || $this->request->getFullActionName() == 'quotation_quote_save'){
                            $amountPrice = $rate->getPrice();
                        }

                        $totalAmount += $amountPrice;
                        $baseTotal += $rate->getPrice();
                        $shippingAmount += $amountPrice;
                        $baseShippingAmount += $rate->getPrice();
                        $shippingDescription .= (empty($shippingDescription) ? '' : "\n") . $rate->getCarrierTitle() . ' - ' . $rate->getMethodTitle();
                        $data[] = [
                            'quote_id' => $quote->getId(),
                            'address_id' => $address->getId(),
                            'location_id' => $sourceStockId,
                            'source_stock_id' => $sourceStockId,
                            'rate_id' => $rate->getId(),
                            'method_code' => $rate->getCode(),
                            'amount' => $amountPrice,
                            'base_amount' => $rate->getPrice(),
                            'carrier' => $rate->getCarrierTitle(),
                            'method_title' => $rate->getMethodTitle(),
                            'vendor_id' => $this->vSourceStock->load($sourceStockId)->getVendorId()
                        ];
                        break;
                    }
                }
            }
            $address->setShippingDescription(trim($shippingDescription, ' -'));
            $total->setTotalAmount($subject->getCode(), $totalAmount);
            $total->setBaseTotalAmount($subject->getCode(), $baseTotal);
            $total->setBaseShippingAmount($baseShippingAmount);
            $total->setShippingAmount($shippingAmount);
            $total->setShippingDescription($shippingDescription);

            //save methods and shipping amount for quote
            $this->helper->saveQuoteShipping($quote->getId(), $data);
        }

        return $result;
    }
}
