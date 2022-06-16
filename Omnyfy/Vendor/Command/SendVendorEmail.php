<?php
/**
 * Project: Multi Vendor M2.
 * User: jing
 * Date: 2/8/17
 * Time: 9:44 AM
 */
namespace Omnyfy\Vendor\Command;

use Magento\Framework\DataObject;
use Magento\Sales\Model\Order;
use Omnyfy\Core\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendVendorEmail extends Command
{
    const VENDOR_NOTIFY_TEMPLATE = 'omnyfy_vendor_order_notification_template';
    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;
    /**
     * @var \Omnyfy\Core\Helper\Queue
     */
    protected $queueHelper;
    /**
     * @var \Omnyfy\Vendor\Helper\Data
     */
    protected $vendorHelper;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Omnyfy\Vendor\Model\Config
     */
    protected $_config;

    protected $orderRepository;

    protected $resource;

    protected $addressRenderer;

    protected $vendorOrderCollection;

    /**
     * SendVendorEmail constructor.
     * @param \Magento\Framework\App\State $state
     * @param \Omnyfy\Core\Helper\Queue $queueHelper
     * @param \Omnyfy\Vendor\Helper\Data $vendorHelper
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Omnyfy\Vendor\Model\Resource\Vendor $vendorResource
     * @param \Magento\Email\Model\ResourceModel\Template\CollectionFactory $templateCollectionFactory
     * @param \Omnyfy\Vendor\Model\Config $config
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \Omnyfy\Core\Helper\Queue $queueHelper,
        \Omnyfy\Vendor\Helper\Data $vendorHelper,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\App\ResourceConnection $resource,
        \Omnyfy\Vendor\Model\Config $config,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Omnyfy\Mcm\Model\ResourceModel\VendorOrder\CollectionFactory $vendorOrderCollection
    )
    {
        $this->appState = $state;
        $this->queueHelper = $queueHelper;
        $this->vendorHelper = $vendorHelper;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->orderRepository = $orderRepository;
        $this->resource = $resource;
        $this->_config = $config;
        $this->addressRenderer = $addressRenderer;
        $this->vendorOrderCollection = $vendorOrderCollection;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('omnyfy:vendor:notification_email');
        $this->setDescription('Process Vendor Notification Emails');
        parent::configure();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    )
    {
        if (!$this->lock()) {
            return;
        }

        try{
            $code = $this->appState->getAreaCode();
        }
        catch(\Exception $e) {
            $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        }

        //Prepare template Id
        $templateId = $this->_config->getOrderNotificationTemplate();

        if(empty($templateId)){
            $output->writeln('Sending vendor email process ended, please create "vendor_order_notification_template" in magento sales order email templated ');
            return;
        }

        $output->writeln('Start to process - with email template id '.$templateId);

        $i = $done = $failed = $invalid = 0;

        while($qItem = $this->queueHelper->takeMsgFromQueue('vendor_notification_email')) {
            $i++;
            if (!isset($qItem['id']) || empty($qItem['id'])) {
                $output->writeln('Got an item without id at '. $i);
                $invalid ++;
                continue;
            }
            if (!isset($qItem['message']) || empty($qItem['message'])) {
                $this->queueHelper->updateQueueMsgStatus($qItem['id'], 'blocking');
                $invalid ++;
                continue;
            }
            $itemData = json_decode($qItem['message'], true);
            if (empty($itemData) || !isset($itemData['order_id'])) {
                $this->queueHelper->updateQueueMsgStatus($qItem['id'], 'blocking');
                $invalid++;
                continue;
            }

            $orderId = $itemData['order_id'];

            $order = $this->getOrder($orderId);
            $vendorIds = $itemData['vendor_ids'];

            // Only process queue if status is correct and vendor order was processed
            if($this->_config->getOrderNotificationOrderStatus() == $order->getStatus() && $this->isMcmOrderCalculated($orderId, $vendorIds, $output)){

                $from = $this->_config->getEmailSentFrom();
                $vendors = $this->vendorHelper->getVendorsByIds($vendorIds);

                foreach ($vendors as $vendor){
                    $vendorEmail = $vendor->getData("email");
                    $vendorName = $vendor->getData("name");

                    $transportData = [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'order' => $order,
                        'billing' => $order->getBillingAddress(),
                        'store' => $order->getStore(),
                        'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
                        'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
                        'created_at_formatted' => $order->getCreatedAtFormatted(2),
                        'vendor' => [
                            'id' => $vendor->getId(),
                            'name' => $vendorName,
                            'abn' => $vendor->getData('abn'),
                            'tax_name' => $this->vendorHelper->getTaxNumberByVendorId($vendor->getId())
                        ],
                        'order_data' => [
                            'customer_name' => $order->getCustomerName(),
                            'is_not_virtual' => $order->getIsNotVirtual(),
                            'email_customer_note' => $order->getEmailCustomerNote(),
                            'frontend_status_label' => $order->getFrontendStatusLabel()
                        ]
                    ];
                    $transportObject = new DataObject($transportData);
                    $transport = $this->_transportBuilder->setTemplateIdentifier($templateId)
                        ->setTemplateOptions(
                            [
                                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                                'store' => $this->_storeManager->getStore()->getId(),
                            ]
                        )
                        ->setTemplateVars($transportObject->getData())
                        ->setFrom($from)
                        ->addTo($vendorEmail, $vendorName)
                        ->getTransport();
                    $transport->sendMessage();

                    $output->writeln('Sending Enquiry For Vendor '.$vendorEmail.'||'.$orderId);
                }

                $this->queueHelper->updateQueueMsgStatus($qItem['id'], 'done');
                $done++;
            }else{
                $failed++;
                $this->queueHelper->updateQueueMsgStatus($qItem['id'], 'temporary_status');
            }
        }

        while($qItem = $this->takeTemporaryMsgFromQueue('vendor_notification_email', 'temporary_status')) {
            $this->queueHelper->updateQueueMsgStatus($qItem['id'], 'pending');
        }

        $output->writeln('Done. Got '. $i . ' items in total.');
        $output->writeln('Invalid items: '. $invalid);
        $output->writeln('Succeeded: '. $done);
        $output->writeln('Failed: ' . $failed);
    }

    public function getOrder($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        return $order;
    }

    public function takeTemporaryMsgFromQueue($topic, $status) {
        $conn = $this->resource->getConnection('core_write');

        $queueTable = $this->resource->getTableName('omnyfy_core_queue');

        $select = $conn->select()->from($queueTable)
            ->where('topic=?', $topic)
            ->where('status=?', $status)
            ->order('id')
            ->limit(1)
        ;

        $row = $conn->fetchRow($select);

        if (empty($row)) {
            return false;
        }

        return $row;
    }

    /**
     * Render shipping address into html.
     *
     * @param Order $order
     * @return string|null
     */
    protected function getFormattedShippingAddress($order)
    {
        return $order->getIsVirtual()
            ? null
            : $this->addressRenderer->format($order->getShippingAddress(), 'html');
    }

    /**
     * Render billing address into html.
     *
     * @param Order $order
     * @return string|null
     */
    protected function getFormattedBillingAddress($order)
    {
        return $this->addressRenderer->format($order->getBillingAddress(), 'html');
    }

    protected function isMcmOrderCalculated($orderId, $vendorIds, $output)
    {
        $calculatedVendorIds = $this->vendorOrderCollection->create()
            ->addFieldToFilter('order_id', $orderId)->getColumnValues('vendor_id');
        $uncalculatedVendors = array_diff($vendorIds, $calculatedVendorIds);
        if (empty($calculatedVendorIds) || count($uncalculatedVendors) > 0) {
            $output->writeln('Order Id: ' . $orderId . ' has not been sent due to mcm calculation not being run');
            return false;
        }

        return true;
    }
}
