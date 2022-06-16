<?php
namespace Omnyfy\Mcm\Command;

use Omnyfy\Core\Command\Command;
use Omnyfy\Vendor\Model\Resource\Vendor\CollectionFactory as VendorCollection;
use Omnyfy\Mcm\Model\VendorPayoutTypeFactory;
use Omnyfy\Mcm\Model\ResourceModel\VendorPayoutType\CollectionFactory as VendorPayoutTypeCollection;
use Omnyfy\Mcm\Model\ResourceModel\PayoutType\CollectionFactory as PayoutTypeCollection;
use Omnyfy\Mcm\Model\PayoutType;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;

class MaintainPayoutType extends Command
{
    protected $vendorCollection;
    protected $vendorPayoutTypeFactory;
    protected $vendorPayoutTypeCollection;
    protected $payoutTypeResource;
    protected $payoutTypeCollection;
    protected $appState;

    public function __construct(
        VendorCollection $vendorCollection,
        VendorPayoutTypeFactory $vendorPayoutTypeFactory,
        VendorPayoutTypeCollection $vendorPayoutTypeCollection,
        PayoutTypeCollection $payoutTypeCollection,
        State $appState
    ) {
        $this->vendorCollection = $vendorCollection;
        $this->vendorPayoutTypeFactory = $vendorPayoutTypeFactory;
        $this->vendorPayoutTypeCollection = $vendorPayoutTypeCollection;
        $this->payoutTypeCollection = $payoutTypeCollection;
        $this->appState = $appState;
        parent::__construct();
    }

    /**
     *
     */
    protected function configure()
    {
        $this->setName('omnyfy:mcm:maintain_payout_type');
        $this->setDescription('Create Payout Type for Existing Vendors');
        parent::configure();
    }

    public function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        try{
            $code = $this->appState->getAreaCode();
        } catch(\Exception $e) {
            $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        }
        $defaultTypeId = $this->payoutTypeCollection->create()->addFieldToFilter('payout_type', ['eq' => PayoutType::DEFAULT_TYPE])->getFirstItem()->getId();
        if (empty($defaultTypeId)) {
            $output->writeln('<error>Cannot get default payout type</error>');
            return;
        }
        $existedVendorIds = $this->vendorPayoutTypeCollection->create()
            ->addFieldToSelect('vendor_id')->getColumnValues('vendor_id');
        $vendorColleciton = $this->vendorCollection->create();
        if (!empty($existedVendorIds)) {
            $vendorColleciton->addFieldToFilter('entity_id', ['nin' => $existedVendorIds]);
        }
        if ($vendorColleciton->getSize() == 0) {
            $output->writeln('<error>All the vendors already have payout type. Nothing to create.</error>');
            return;
        }

        $countVendor = 0;
        try {
            foreach ($vendorColleciton as $vendor) {
                $vendorPayoutType = $this->vendorPayoutTypeFactory->create();
                $vendorPayoutType->setData('vendor_id', $vendor->getId());
                $vendorPayoutType->setData('payout_type_id', $defaultTypeId);
                $vendorPayoutType->save();
                $countVendor++;
            }
        } catch (\Exception $e) {
            $output->writeln('<error>Error: ' . $e->getMessage() . ' </error>');
        }

        $output->writeln('<info>Created Payout Type for ' . $countVendor . ' vendors</info>');
    }
}
