<?php
namespace Omnyfy\Vendor\Block\Express;

use Magento\Framework\View\Element\BlockInterface;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Sales\Model\ConfigInterface;
use Omnyfy\Vendor\Model\Resource\VendorSourceStock;
use Magento\Inventory\Model\SourceFactory;
use Omnyfy\Vendor\Model\VendorFactory;

class Details extends \Magento\Paypal\Block\Express\Review\Details
{
    protected $vSourceStock;
    protected $sourceFactory;
    protected $vendorFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        ConfigInterface $salesConfig,
        VendorSourceStock $vSourceStock,
        SourceFactory $sourceFactory,
        VendorFactory $vendorFactory,
        array $layoutProcessors = [],
        array $data = []
    ) { 
        parent::__construct($context, $customerSession, $checkoutSession, $salesConfig, $layoutProcessors, $data);
        $this->vSourceStock = $vSourceStock;
        $this->sourceFactory = $sourceFactory;
        $this->vendorFactory = $vendorFactory;
    }

    public function groupItemsBySource() {
        $groupItems = [];
        $items = $this->getItems();
        $sourceModel = $this->sourceFactory->create();
        $vendorModel = $this->vendorFactory->create();
        foreach ($items as $item) {
            $sourceStockId = $item->getSourceStockId();
            $sourceCode = $this->vSourceStock->getSourceCodeById($sourceStockId);
            $source = $sourceModel->load($sourceCode);
            if (isset($groupItems[$sourceCode])) {
                $groupItems[$sourceCode]['items'][] = $item;
            } else {
                $vendorId = $source->getVendorId();
                $vendorName = $vendorModel->load($vendorId)->getName();
                $groupItems[$sourceCode]['items'] = [];
                $groupItems[$sourceCode]['items'][] = $item;
                $groupItems[$sourceCode]['source_name'] = $source->getName();
                $groupItems[$sourceCode]['vendor_name'] = $vendorName;
            }
        }

        return $groupItems;
    }
}