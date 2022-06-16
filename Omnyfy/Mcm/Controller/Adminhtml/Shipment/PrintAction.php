<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Omnyfy\Mcm\Controller\Adminhtml\Shipment;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;

class PrintAction extends \Magento\Sales\Controller\Adminhtml\Shipment\PrintAction
{

    protected $shipmentRepository;

    protected $dateTime;

    protected $pdfHelper;

    protected $layoutFactory;

    protected $directoryList;

    protected $file;

    protected $orderRepository;

    protected $mcmHelper;

    protected $vendorSignupModel;
    /**
     * @var \Magento\Theme\Block\Html\Header\Logo
     */
    private $logo;
    /**
     * @var \Omnyfy\Vendor\Helper\OrderAttributeHelper
     */
    private $orderAttributeHelper;

    /**
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param ForwardFactory $resultForwardFactory
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        ForwardFactory $resultForwardFactory,
        DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Omnyfy\Core\Helper\DomPdfInterface $pdfHelper,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Omnyfy\Mcm\Helper\Data $mcmHelper,
        \Omnyfy\VendorSignUp\Model\SignUpFactory $vendorSignupModel,
        \Omnyfy\Mcm\Helper\OrderAttributeHelper $orderAttributeHelper,
        \Magento\Theme\Block\Html\Header\Logo $logo
    ) {
        $this->shipmentRepository = $shipmentRepository;
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->dateTime = $dateTime;
        $this->pdfHelper = $pdfHelper;
        $this->layoutFactory = $layoutFactory;
        $this->orderRepository = $orderRepository;
        $this->mcmHelper = $mcmHelper;
        $this->vendorSignupModel = $vendorSignupModel;
        $this->orderAttributeHelper = $orderAttributeHelper;

        parent::__construct($context, $fileFactory, $resultForwardFactory);
        $this->logo = $logo;
    }

    /**
     * @return ResponseInterface|\Magento\Backend\Model\View\Result\Forward
     * @throws \Exception
     */
    public function execute()
    {
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        if ($shipmentId) {
            $shipment = $this->shipmentRepository->get($shipmentId);
            if ($shipment) {
                $htmlContent = $this->generateShipmentHtml($shipment);
                $this->pdfHelper->setData($htmlContent);

                $date = $this->dateTime->date('Y-m-d_H-i-s');
                $fileName = 'packing_slip' . $date . '.pdf';
                $filePath = $this->directoryList->getPath(DirectoryList::MEDIA)."/packing_slip/";
                if ( ! file_exists($filePath)) {
                    $this->file->mkdir($filePath);
                }

                $this->saveFile($filePath . $fileName);
                $this->downloadFile($fileName);
            }
        } else {
            /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }
    }

    public function generateShipmentHtml($shipment)
    {
        /** @var \Magento\Framework\View\Element\Template $block */
        $block = $this->layoutFactory->create()->createBlock('Magento\Framework\View\Element\Template');
        $block->setTemplate('Omnyfy_Mcm::order/shipment/packing_slip.phtml');

        $data = [
            'shipment_data' => $shipment,
            'order_data' => $this->getOrderData($shipment->getOrderId()),
            'invoice_data' => $this->mcmHelper->getInvoiceFromData(),
            'order_attributes' => $this->orderAttributeHelper->getOrderAttributeData($shipment->getOrder()),
            'sold_data' => $this->getBillingAddress($shipment->getOrderId()),
            'shipping_data' => $this->getShippingAddress($shipment->getOrderId()),
            'order_items' => $this->getOrderItems($shipment->getOrderId()),
            'vendor_info' => $this->vendorInfo(),
            'vendor_signup' => $this->vendorSignUp(),
            'logo_url' => $this->logo->getLogoSrc()
        ];

        $block->setData($data);

        return $block->toHtml();
    }

    protected function saveFile($filePath)
    {
        file_put_contents($filePath, $this->pdfHelper->save());
    }

    protected function downloadFile($fileName)
    {
        try {
            $this->_fileFactory->create(
                $fileName,
                [
                    'type' => 'filename',
                    'value' => 'media/packing_slip/'.$fileName,
                    'rm' => 1
                ],
                DirectoryList::PUB,
                'application/pdf'
            );
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__("Error file download"));
        }
    }

    protected function getOrderData($orderId) {
        $order = $this->orderRepository->get($orderId);

        $data = [
            'increment_id' => $order->getIncrementId(),
            'date' => $this->mcmHelper->getDateWithFormat($order->getCreatedAt(), 'd M Y'),
            'mo_name' => $this->mcmHelper->getMoName(),
            'logo_src' => $this->mcmHelper->getLogoSrc()
        ];

        return $data;
    }

    protected function getBillingAddress($orderId)
    {
        $order = $this->orderRepository->get($orderId);

        if ($order->getBillingAddress()) {
            $name = $order->getBillingAddress()->getName();
            $street = $order->getBillingAddress()->getStreet();
            $city = $order->getBillingAddress()->getCity();
            $region = $order->getBillingAddress()->getRegion();
            $postCode = $order->getBillingAddress()->getPostcode();
            $country = $this->mcmHelper->getCountryName($order->getBillingAddress()->getCountryId());
            $telephone = $order->getBillingAddress()->getTelephone();
        } else {
            $name = '';
            $street[0] = '';
            $city = '';
            $region = '';
            $postCode = '';
            $country = '';
            $telephone = '';
        }

        $data = [
            'customer_name' => $name,
            'street' => $street[0],
            'city' => $city.', '.$region.', '.$postCode,
            'country' => $country,
            'telephone' => $telephone
        ];

        return $data;
    }

    protected function getShippingAddress($orderId)
    {
        $order = $this->orderRepository->get($orderId);

        if ($order->getShippingAddress()) {
            $name = $order->getShippingAddress()->getName();
            $street = $order->getShippingAddress()->getStreet();
            $city = $order->getShippingAddress()->getCity();
            $region = $order->getShippingAddress()->getRegion();
            $postCode = $order->getShippingAddress()->getPostcode();
            $country = $this->mcmHelper->getCountryName($order->getShippingAddress()->getCountryId());
            $telephone = $order->getShippingAddress()->getTelephone();

            return [
                'customer_name' => $name,
                'street' => $street[0],
                'city' => $city.', '.$region.', '.$postCode,
                'country' => $country,
                'telephone' => $telephone
            ];
        }

        return null;
    }

    protected function vendorInfo()
    {
        return $this->mcmHelper->vendorInfo();
    }

    protected function getOrderItems($orderId) {
        $order = $this->orderRepository->get($orderId);
        return $order->getAllVisibleItems();
    }

    protected function vendorSignUp()
    {
        $vendorInfo = $this->vendorInfo();
        if (!empty($vendorInfo)) {
            $vendorId = $vendorInfo['vendor_id'];
            $info = $this->vendorSignupModel->create()->load($vendorId);
            return $info;
        }
        return [];
    }
}
