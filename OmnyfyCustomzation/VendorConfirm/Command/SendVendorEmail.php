<?php
/**
 * Lucas
 * Copyright (C) 2019
 *
 * This file is part of OmnyfyCustomzation/VendorConfirm.
 *
 * OmnyfyCustomzation/VendorConfirm is free software: you can redistribute it and/or modify
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

namespace OmnyfyCustomzation\VendorConfirm\Command;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\DataObject;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\View\Element\TemplateFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Store\Model\StoreManagerInterface;
use Omnyfy\Core\Command\Command;
use Omnyfy\Core\Helper\Queue;
use Omnyfy\Mcm\Model\ResourceModel\VendorOrder\CollectionFactory;
use Omnyfy\Vendor\Helper\Data;
use Omnyfy\Vendor\Model\Config;
use OmnyfyCustomzation\VendorConfirm\Helper\Data as HelperData;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendVendorEmail extends Command
{
    protected $appState;
    protected $queueHelper;
    protected $vendorHelper;
    protected $_transportBuilder;
    protected $_storeManager;
    protected $_config;
    protected $orderRepository;
    protected $resource;
    protected $addressRenderer;
    protected $vendorOrderCollection;
    protected $template;
    protected $helperData;
    protected $inlineTranslation;

    public function __construct(
        State $state,
        Queue $queueHelper,
        Data $vendorHelper,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        OrderRepositoryInterface $orderRepository,
        ResourceConnection $resource,
        Config $config,
        Renderer $addressRenderer,
        CollectionFactory $vendorOrderCollection,
        TemplateFactory $templateFactory,
        HelperData $helperData,
        StateInterface $inlineTranslation,
        $name = null
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
        $this->templateFactory = $templateFactory;
        $this->helperData = $helperData;
        $this->inlineTranslation = $inlineTranslation;
        parent::__construct($name);
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
        // not send email confirm
        return false;
        if (!$this->lock()) {
            return;
        }
        $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        $templateId = $this->_config->getOrderNotificationTemplate();
        if (empty($templateId)) {
            $output->writeln('Sending vendor email process ended, please create "vendor_order_notification_template" in magento sales order email templated ');
            return;
        }

        $output->writeln('Start to process - with email template id ' . $templateId);
        $i = $done = $failed = $invalid = 0;

        while ($qItem = $this->queueHelper->takeMsgFromQueue('vendor_notification_email')) {
            $i++;
            if (!isset($qItem['id']) || empty($qItem['id'])) {
                $output->writeln('Got an item without id at ' . $i);
                $invalid++;
                continue;
            }
            if (!isset($qItem['message']) || empty($qItem['message'])) {
                $this->queueHelper->updateQueueMsgStatus($qItem['id'], 'blocking');
                $invalid++;
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
            if ($this->_config->getOrderNotificationOrderStatus()) {

                $from = $this->_config->getEmailSentFrom();
                $vendors = $this->vendorHelper->getVendorsByIds($vendorIds);

                foreach ($vendors as $vendor) {
                    $vendorEmail = $vendor->getData("email");
                    $vendorName = $vendor->getData("name");
                    $output->writeln('Vendor email: ' . $vendorEmail);

                    $transportData = [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'order' => $order,
                        'order_number' => $order->getIncrementId(),
                        'billing' => $order->getBillingAddress(),
                        'store' => $order->getStore(),
                        'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
                        'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
                        'created_at_formatted' => $order->getCreatedAtFormatted(2),
                        'order_item_template' => $this->getItemTemplate($vendor, $order),
                        'additional_info' => $this->getAdditionalInfo($order),
                        'payment_method' => $order->getPayment()->getMethodInstance()->getTitle(),
                        'vendor' => [
                            'id' => $vendor->getId(),
                            'name' => $vendorName,
                            'abn' => $vendor->getData('abn')
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

                    $output->writeln('Sending Enquiry For Vendor ' . $vendorEmail . '||' . $orderId);
                }
                $this->queueHelper->updateQueueMsgStatus($qItem['id'], 'done');
                $done++;
            } else {
                $failed++;
                $this->queueHelper->updateQueueMsgStatus($qItem['id'], 'temporary_status');
            }
        }

        while ($qItem = $this->takeTemporaryMsgFromQueue('vendor_notification_email', 'temporary_status')) {
            $this->queueHelper->updateQueueMsgStatus($qItem['id'], 'pending');
        }

        $output->writeln('Done. Got ' . $i . ' items in total.');
        $output->writeln('Invalid items: ' . $invalid);
        $output->writeln('Succeeded: ' . $done);
        $output->writeln('Failed: ' . $failed);
    }

    public function getItemTemplate($vendor, $order)
    {
        return $this->templateFactory->create()->getLayout()->createBlock(
            \Magento\Framework\View\Element\Template::class,
            null,
            [
                'data' => [
                    'vendor' => $vendor,
                    'order' => $order,
                    'helper' => $this->helperData
                ]
            ]
        )->setTemplate("OmnyfyCustomzation_VendorConfirm::/order/items.phtml")->toHtml();
    }

    public function getAdditionalInfo($order)
    {
        return $this->templateFactory->create()->getLayout()->createBlock(
            \Amasty\Orderattr\Block\Order\Email::class,
            null,
            [
                'data' => [
                    'order_entity' => $order
                ]
            ]
        )->setTemplate("Amasty_Orderattr::order/email/attributes.phtml")->toHtml();
    }

    public function getOrder($orderId)
    {
        return $this->orderRepository->get($orderId);
    }

    public function takeTemporaryMsgFromQueue($topic, $status)
    {
        $conn = $this->resource->getConnection('core_write');
        $queueTable = $this->resource->getTableName('omnyfy_core_queue');
        $select = $conn->select()->from($queueTable)
            ->where('topic=?', $topic)
            ->where('status=?', $status)
            ->order('id')
            ->limit(1);
        $row = $conn->fetchRow($select);
        if (empty($row)) {
            return false;
        }
        return $row;
    }

    protected function getFormattedShippingAddress($order)
    {
        return $order->getIsVirtual()
            ? null
            : $this->addressRenderer->format($order->getShippingAddress(), 'html');
    }

    protected function getFormattedBillingAddress($order)
    {
        return $this->addressRenderer->format($order->getBillingAddress(), 'html');
    }
}
