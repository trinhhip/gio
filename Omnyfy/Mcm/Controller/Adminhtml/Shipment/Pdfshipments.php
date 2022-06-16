<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Omnyfy\Mcm\Controller\Adminhtml\Shipment;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\Order\Pdf\Shipment;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Pdfshipments extends \Magento\Sales\Controller\Adminhtml\Order\Pdfshipments
{

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
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param DateTime $dateTime
     * @param FileFactory $fileFactory
     * @param Shipment $shipment
     * @param ShipmentCollectionFactory $shipmentCollectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        DateTime $dateTime,
        FileFactory $fileFactory,
        Shipment $shipment,
        ShipmentCollectionFactory $shipmentCollectionFactory,
        DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Io\File $file,
        \Omnyfy\Core\Helper\DomPdfInterface $pdfHelper,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Omnyfy\Mcm\Helper\Data $mcmHelper,
        \Omnyfy\VendorSignUp\Model\SignUpFactory $vendorSignupModel,
        \Magento\Theme\Block\Html\Header\Logo $logo
    ) {
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->pdfHelper = $pdfHelper;
        $this->layoutFactory = $layoutFactory;
        $this->orderRepository = $orderRepository;
        $this->mcmHelper = $mcmHelper;
        $this->vendorSignupModel = $vendorSignupModel;

        parent::__construct($context, $filter, $collectionFactory, $dateTime, $fileFactory, $shipment, $shipmentCollectionFactory);
        $this->logo = $logo;
    }

    /**
     * Print shipments for selected orders
     *
     * @param AbstractCollection $collection
     * @return ResponseInterface|\Magento\Backend\Model\View\Result\Redirect
     * @throws \Exception
     */
    protected function massAction(AbstractCollection $collection)
    {
        $shipmentsCollection = $this->shipmentCollectionFactory
            ->create()
            ->setOrderFilter(['in' => $collection->getAllIds()]);
        if (!$shipmentsCollection->getSize()) {
            $this->messageManager->addError(__('There are no printable documents related to selected orders.'));
            return $this->resultRedirectFactory->create()->setPath($this->getComponentRefererUrl());
        }

        foreach ($shipmentsCollection as $shipment) {
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
            $this->fileFactory->create(
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
