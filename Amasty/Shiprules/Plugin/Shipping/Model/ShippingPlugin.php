<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprules
 */


namespace Amasty\Shiprules\Plugin\Shipping\Model;

use Amasty\Shiprules\Api\ShippingRuleApplierInterface as ApplierInterface;
use Amasty\Shiprules\Model\Rule\Applier;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Shipping\Model\Shipping;

/**
 * Entry point of RuleApplier.
 */
class ShippingPlugin
{
    /**
     * @var Applier|ApplierInterface
     */
    private $applier;

    /**
     * @var string
     */
    private $currentCarrier = null;

    /**
     * @var boolean
     */
    private $lockCollectRates = false;

    public function __construct(ApplierInterface $applier)
    {
        $this->applier = $applier;
    }

    /**
     * @param Shipping $subject
     * @param Shipping $result
     * @param RateRequest $request
     * @return Shipping
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterCollectRates(Shipping $subject, $result, RateRequest $request)
    {
        if ($this->getLockCollectRates()) {
            return $result;
        }

        $this->lockCollectRates();

        if (!$this->applier->canApplyAnyRule($request, $subject->getResult()->getAllRates())) {
            return $result;
        }

        //Save original result for correct return.
        $originalResult = clone $subject->getResult();

        $this->applier->calculateAdjustments($originalResult->getAllRates());

        /** @var Method $rate */
        foreach ($originalResult->getAllRates() as $rate) {
            //Check all rate for `product tab` conditions.
            //If any condition is set, recollect ALL rates.
            if ($rate instanceof \Magento\Quote\Model\Quote\Address\RateResult\Error) {
                continue;
            }
            foreach ($this->applier->getRulesForCarrier($rate) as $rule) {
                if ($newRequest = $this->applier->getModifiedRequest($rate, $request, $rule)) {
                    $subject->getResult()->reset();

                    //Save carrier code to re-calculate only it.
                    $this->currentCarrier = $rate->getCarrier();
                    $subject->collectRates($newRequest);
                    $this->currentCarrier = null;

                    //And re-calculate adjustment using original ana new $rate value.
                    $newRate = $this->getNewRate($subject, $rate);
                    $this->applier->calculateRateAdjustment($newRate, $newRequest);
                }
            }
            //And apply changes.
            $this->applier->applyAdjustment($rate);
        }

        $subject->getResult()->reset();
        $subject->getResult()->append($originalResult);

        $this->unlockCollectRates();

        return $result;
    }

    /**
     * @param Shipping $subject
     * @param Method $oldRate
     * @return Method
     */
    private function getNewRate(Shipping $subject, Method $oldRate)
    {
        /** @var Method $rate */
        foreach ($subject->getResult()->getRatesByCarrier($oldRate->getCarrier()) as $rate) {
            if ($rate->getCode() === $oldRate->getCode()
                && $rate->getMethod() === $oldRate->getMethod()
            ) {
                return $rate;
            }
        }

        return $oldRate;
    }

    /**
     * @return bool
     */
    private function getLockCollectRates()
    {
        return $this->lockCollectRates;
    }

    private function lockCollectRates()
    {
        $this->lockCollectRates = true;
    }

    private function unlockCollectRates()
    {
        $this->lockCollectRates = false;
    }
}
