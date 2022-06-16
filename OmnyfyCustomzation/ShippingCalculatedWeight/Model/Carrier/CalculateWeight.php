<?php
/**
 * Lucas
 * Copyright (C) 2019
 *
 * This file is part of OmnyfyCustomzation/ShippingCalculatedWeight.
 *
 * OmnyfyCustomzation/ShippingCalculatedWeight is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace OmnyfyCustomzation\ShippingCalculatedWeight\Model\Carrier;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use OmnyfyCustomzation\ShippingCalculatedWeight\Model\ResourceModel\CalculateWeight\CollectionFactory as WeightCollection;
use OmnyfyCustomzation\ShippingCalculatedWeight\Model\Rules\Config\Surcharge;
use OmnyfyCustomzation\ShippingCalculatedWeight\Model\Rules\Config\TypePrice;
use Psr\Log\LoggerInterface;

class CalculateWeight extends AbstractCarrier implements CarrierInterface
{
    const WEIGHT_PARAM = '{weight}';
    const IS_ACTIVE = 1;

    protected $_code = 'calculateweight';
    protected $_isFixed = true;
    protected $shipFromCountry = [];

    /**
     * @var ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var WeightCollection
     */
    private $weightCollection;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        ProductRepositoryInterface $productRepository,
        WeightCollection $weightCollection,
        array $data = []
    )
    {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->productRepository = $productRepository;
        $this->weightCollection = $weightCollection;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    public function collectRates(RateRequest $request)
    {

        if (!$this->isActive()) {
            return false;
        }

        $shippingPrice = $this->getRatePrice($request);
        if (!$shippingPrice) {
            return false;
        }
        $result = $this->_rateResultFactory->create();
        $method = $this->_rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));

        $method->setPrice($shippingPrice);
        $method->setCost($shippingPrice);

        $result->append($method);
        return $result;
    }

    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    public function getRatePrice(RateRequest $request)
    {
        $ratePrice = 0;
        $calcWeight = 0;
        $shipFromCountry = [];
        $listSku = [];
        try {
            foreach ($request->getAllItems() as $item) {
                switch ($item->getProductType()) {
                    case 'configurable':
                    {
                        $childItems = $item->getChildren();
                        foreach ($childItems as $childItem) {
                            if (!in_array($childItem->getSku(), $listSku)) {
                                array_push($listSku, $childItem->getSku());
                                $calcWeight += $this->getCalcWeight($item->getProduct()) * (int)$item->getQty();
                                $shipFromCountry[] = $childItem->getProduct()->getShipFromCountry();
                            }
                        }
                        break;
                    }
                    case 'simple':
                    {
                        if (!in_array($item->getSku(), $listSku)) {
                            array_push($listSku, $item->getSku());
                            $calcWeight += $this->getCalcWeight($item->getProduct()) * (int)$item->getQty();
                            $shipFromCountry[] = $item->getProduct()->getShipFromCountry();
                        }
                        break;
                    }
                }
            }

            $rule = $this->getRuleApply($request, $calcWeight, $shipFromCountry)->getFirstItem();

            //check type of rule
            switch ($rule->getType()) {
                case TypePrice::FIXED:
                    $weightPrice = $rule->getPrice();
                    break;
                case TypePrice::FORMULA:
                default:
                    $roundFactor = $rule->getRoundFactor() && $rule->getRoundFactor() > 0 ? $rule->getRoundFactor() : 0.5;
                    $weight = $this->ceiling($calcWeight, (float)$roundFactor);
                    $formula = str_replace(self::WEIGHT_PARAM, $weight, $rule->getCalcFormula());
                    $weightPrice = (float)eval(sprintf("return%s;", $formula));
                    break;
            }

            //apply fee
            switch ($rule->getSurchargeApply()) {
                case Surcharge::PERCENTAGE:
                    $ratePrice += $weightPrice + ($weightPrice * (float)$rule->getSurchargeFee()) / 100;
                    break;
                case Surcharge::FIXED:
                    $ratePrice += $weightPrice + (float)$rule->getSurchargeFee();
                    break;
                default:
                    $ratePrice += $weightPrice;
                    break;
            }

        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
            return 0;
        }
        return $ratePrice;
    }

    protected function ceiling($number, $significance)
    {
        return ceil($number / $significance) * $significance;
    }

    public function getRuleApply($request, $calcWeight, $shipFromCountries)
    {
        $shipToCountry = $request->getDestCountryId();
        $rules = $this->weightCollection->create();
        $rules->addFieldToFilter('is_active', self::IS_ACTIVE)
            ->addFieldToFilter('weight_from', ['lteq' => $calcWeight])
            ->addFieldToFilter('weight_to', ['gteq' => $calcWeight])
            ->addFieldToFilter('ship_to_country', ['like' => '%' . $shipToCountry . '%'])
            ->setOrder('priority', 'ASC')
            ->setOrder('entity_id', 'DESC');
        foreach ($shipFromCountries as $shipFromCountry) {
            $rules->addFieldToFilter('ship_from_country', ['like' => '%' . $shipFromCountry . '%']);
        }
        return $rules;
    }

    protected function getCalcWeight($product)
    {
        $product = $this->productRepository->getById($product->getId());
        if (!in_array($product->getShipFromCountry(), $this->shipFromCountry)) {
            array_push($this->shipFromCountry, $product->getShipFromCountry());
        }
        return (float)$product->getCalculatedShippingWeight();
    }
}
