<?php
namespace Omnyfy\Vendor\Block\Express;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Inventory\Model\SourceFactory;
use Omnyfy\Vendor\Model\Resource\VendorSourceStock;
use Omnyfy\Vendor\Model\VendorFactory;

class Review extends \Magento\Paypal\Block\Express\Review
{
    protected $vSourceStock;
    protected $sourceFactory;
    protected $vendorFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Customer\Model\Address\Config $addressConfig,
        PriceCurrencyInterface $priceCurrency,
        VendorSourceStock $vSourceStock,
        SourceFactory $sourceFactory,
        VendorFactory $vendorFactory,
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->_taxHelper = $taxHelper;
        $this->_addressConfig = $addressConfig;
        $this->vSourceStock = $vSourceStock;
        $this->sourceFactory = $sourceFactory;
        $this->vendorFactory = $vendorFactory;
        parent::__construct($context, $taxHelper, $addressConfig, $priceCurrency, $data);
    }

    public function getSourceStockIdOfRate($rate) {
        return $rate->getSourceStockId();
    }

    public function groupShippingMethodBySource() {
        $group = $this->getShippingRateGroups();
        $groupBySource = [];

        foreach ($group as $shippingName => $methods) {
            foreach ($methods as $method) {
                $sourceStockId = $method->getSourceStockId();
                $groupBySource[$sourceStockId][$shippingName][] = $method;
            }
        }
        return $groupBySource;
    }

    public function getInfor($sourceStockId) {
        $sourceModel = $this->sourceFactory->create();
        $vendorModel = $this->vendorFactory->create();
        $sourceCode = $this->vSourceStock->getSourceCodeById($sourceStockId);
        $vendorId = $this->vSourceStock->getVendorIdBySourceStockId($sourceStockId);
        $vendorName = $vendorModel->load($vendorId)->getName();
        $sourceName = $sourceModel->load($sourceCode)->getName();
        $info['vendor_name'] = $vendorName;
        $info['source_name']= $sourceName;
        return $info;
    }
}