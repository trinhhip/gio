<?php
namespace Omnyfy\Mcm\Command;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\ResourceConnection;

class ProcessMcmInvoice extends Command
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;
    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;
    /**
     * @var \Omnyfy\Mcm\Model\VendorOrderFactory
     */
    protected $vendorOrderFactory;

    protected $_feesManagement;
    /**
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param ResourceConnection $resourceConnection
     * @param \Magento\Framework\App\State $state
     * @param \Omnyfy\Mcm\Model\VendorOrderFactory $vendorOrderFactory
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        ResourceConnection $resourceConnection,
        \Magento\Framework\App\State $state,
        \Omnyfy\Mcm\Model\VendorOrderFactory $vendorOrderFactory,
        \Omnyfy\Mcm\Model\ResourceModel\FeesManagement  $feesManagement
    )
    {
        $this->orderFactory = $orderFactory;
        $this->resourceConnection = $resourceConnection;
        $this->appState = $state;
        $this->vendorOrderFactory = $vendorOrderFactory;
        $this->_feesManagement = $feesManagement;
        parent::__construct();
    }


    protected function configure()
    {
        $this->setName('omnyfy:mcm:process_mcm_invoice')
            ->setDescription('Process Mcm Invoice');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        try{
            $code = $this->appState->getAreaCode();
        }
        catch(\Exception $e) {
            $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        }

        $output->writeln('Start to process');

        $invoices = $this->_feesManagement->getVendorInvoiceIsNull();
        $connection = $this->resourceConnection->getConnection();
        $i = 0;

        if($invoices){
            foreach ($invoices as $invoice){
                try {
                    $vendorOrderTotals = $this->_feesManagement->getVendorOrderTotals($invoice['vendor_id'], $invoice['order_id']);
                    $data = [
                        'subtotal' => $vendorOrderTotals['subtotal'],
                        'base_subtotal' => $vendorOrderTotals['base_subtotal'],
                        'subtotal_incl_tax' => $vendorOrderTotals['subtotal_incl_tax'],
                        'base_subtotal_incl_tax' => $vendorOrderTotals['base_subtotal_incl_tax'],
                        'tax_amount' => $vendorOrderTotals['tax_amount'] + $vendorOrderTotals['shipping_tax'],
                        'base_tax_amount' => $vendorOrderTotals['base_tax_amount'],
                        'discount_amount' => $vendorOrderTotals['discount_amount'] + $vendorOrderTotals['shipping_discount_amount'],
                        'base_discount_amount' => $vendorOrderTotals['base_discount_amount'] + $vendorOrderTotals['shipping_discount_amount'],
                        'shipping_amount' => $vendorOrderTotals['shipping_amount'],
                        'base_shipping_amount' => $vendorOrderTotals['base_shipping_amount'],
                        'shipping_incl_tax' => $vendorOrderTotals['shipping_incl_tax'],
                        'base_shipping_incl_tax' => $vendorOrderTotals['base_shipping_incl_tax'],
                        'shipping_tax' => $vendorOrderTotals['shipping_tax'],
                        'base_shipping_tax' => $vendorOrderTotals['base_shipping_tax'],
                        'shipping_discount_amount' => $vendorOrderTotals['shipping_discount_amount'],
                        'grand_total' => ($vendorOrderTotals['grand_total'] + $vendorOrderTotals['shipping_amount'] + $vendorOrderTotals['shipping_tax'] - ($vendorOrderTotals['shipping_discount_amount'])),
                        'base_grand_total' => ($vendorOrderTotals['base_grand_total'] + $vendorOrderTotals['shipping_amount'] + $vendorOrderTotals['shipping_tax'] - ($vendorOrderTotals['shipping_discount_amount']))
                    ];

                    if($invoice['id']){
                        $tableName = $this->resourceConnection->getTableName("omnyfy_mcm_vendor_invoice");
                        $connection->update($tableName, $data, ['id = ?' => (int)$invoice['id']]);
                        $output->writeln("Invoice Id: " . $invoice['id']);
                    }
                    $i++;
                } catch (\Exception $exception) {
                    $output->writeln($exception->getMessage());
                }
            }
        }
        $output->writeln('Done. Got '. $i . ' invoices been processed.');
        $output->writeln("Succeeded");
    }
}
