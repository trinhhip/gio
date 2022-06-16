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

class ProcessPayoutPending extends Command
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
        \Omnyfy\Mcm\Model\VendorOrderFactory $vendorOrderFactory
    )
    {
        $this->orderFactory = $orderFactory;
        $this->resourceConnection = $resourceConnection;
        $this->appState = $state;
        $this->vendorOrderFactory = $vendorOrderFactory;
        parent::__construct();
    }


    protected function configure()
    {
        $this->setName('omnyfy:mcm:process_payout_pending')
            ->setDescription('Process Payout Pending for mcm orders')
            ->setDefinition([
                new InputOption('increment_id', 'i', InputOption::VALUE_OPTIONAL, 'Id of order'),
            ]);

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

        $increment_id = $input->getOption('increment_id');
        $connection = $this->resourceConnection->getConnection();
        if($increment_id){
            $ids = explode(",","$increment_id");
            $order = [];
            foreach ($ids as $id){
                $orderModel = $this->orderFactory->create()->loadByIncrementId($id);
                $orderId = $orderModel->getId();

                //table omnyfy_mcm_vendor_order
                $mcm_vendor_order = $this->vendorOrderFactory->create()->getCollection()
                    ->addFieldToFilter('payout_status', 0)
                    ->addFieldToFilter('payout_action', 0)
                    ->addFieldToFilter('order_increment_id', $id)
                    ->getFirstItem();
                //table omnyfy_core_queue
                $queueTable = $this->resourceConnection->getTableName('omnyfy_core_queue');
                $core_queue = "";
                if($orderId){
                    $select = $connection->select()->from($queueTable)
                        ->where('topic=?', 'mcm_after_place_order')
                        ->where('message like ?', "%$orderId%");
                    $core_queue = $connection->fetchOne($select);
                }

                if($orderId && !$mcm_vendor_order->getId() && !$core_queue){
                    $order[] = [
                      "topic" => "mcm_after_place_order",
                      "message" =>'{"order_id":"'.$orderId.'"}'
                    ];
                    $output->writeln("Order Id: " . $orderId);
                }
            }
            if($order){
                $tableName = $this->resourceConnection->getTableName("omnyfy_core_queue");
                $connection->insertMultiple($tableName, $order);
            }
        }
        $output->writeln("Succeeded");
    }
}
