<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Omnyfy\Mcm\Controller\Adminhtml\Creditmemo;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Sales\Api\CreditmemoRepositoryInterface;

class PrintAction extends \Magento\Sales\Controller\Adminhtml\Order\Creditmemo\PrintAction
{

    /**
     * Payment data
     *
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentData;

    /**
     * @var \Mirasvit\Rewards\Helper\Purchase
     */
    protected $rewardsPurchase;

    protected $cancelEarnedPointsService;

    protected $pdfHelper;

    protected $layoutFactory;

    protected $orderRepository;

    protected $mcmHelper;

    protected $directoryList;

    protected $file;

    protected $dateTime;

    protected $deleteDirectory;
    /**
     * @var \Magento\Theme\Block\Html\Header\Logo
     */
    private $logo;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        CreditmemoRepositoryInterface $creditmemoRepository,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader,
        \Magento\Payment\Helper\Data $paymentData,
        \Mirasvit\Rewards\Helper\Purchase $rewardsPurchase,
        \Mirasvit\Rewards\Service\Order\Transaction\CancelEarnedPoints $cancelEarnedPointsService,
        \Omnyfy\Core\Helper\DomPdfInterface $pdfHelper,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Omnyfy\Mcm\Helper\Data $mcmHelper,
        DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Omnyfy\Mcm\Model\DeleteDirectory $deleteDirectory,
        \Magento\Theme\Block\Html\Header\Logo $logo
    ) {
        $this->_paymentData = $paymentData;
        $this->rewardsPurchase = $rewardsPurchase;
        $this->cancelEarnedPointsService = $cancelEarnedPointsService;
        $this->pdfHelper = $pdfHelper;
        $this->layoutFactory = $layoutFactory;
        $this->orderRepository = $orderRepository;
        $this->mcmHelper = $mcmHelper;
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->dateTime = $dateTime;
        $this->deleteDirectory = $deleteDirectory;

        parent::__construct($context, $fileFactory, $resultForwardFactory, $creditmemoRepository, $creditmemoLoader);
        $this->logo = $logo;
    }

    public function execute()
    {
        $this->creditmemoLoader->setOrderId($this->getRequest()->getParam('order_id'));
        $this->creditmemoLoader->setCreditmemoId($this->getRequest()->getParam('creditmemo_id'));
        $this->creditmemoLoader->setCreditmemo($this->getRequest()->getParam('creditmemo'));
        $this->creditmemoLoader->setInvoiceId($this->getRequest()->getParam('invoice_id'));
        $this->creditmemoLoader->load();

        $creditmemoId = $this->getRequest()->getParam('creditmemo_id');
        if ($creditmemoId) {
            $creditmemo = $this->creditmemoRepository->get($creditmemoId);

            if ($creditmemo) {
                $htmlContent = $this->generateCreditmemoHtml($creditmemo);
                $this->pdfHelper->setData($htmlContent);

                $date = $this->dateTime->date('Y-m-d_H-i-s');
                $fileName = 'creditmemo' . $date . '.pdf';
                $filePath = $this->directoryList->getPath(DirectoryList::MEDIA)."/credit_memo/";
                if ( ! file_exists($filePath)) {
                    $this->file->mkdir($filePath);
                }

                $this->saveFile($filePath . $fileName);
                $this->downloadFile($fileName);
            }
        } else {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');
            return $resultForward;
        }
    }

    public function generateCreditmemoHtml($creditmemo)
    {
        /** @var \Magento\Framework\View\Element\Template $block */
        $block = $this->layoutFactory->create()->createBlock('Magento\Framework\View\Element\Template');
        $block->setTemplate('Omnyfy_Mcm::order/creditmemo/creditmemo.phtml');

        $data = [
            'order_data' => $this->getOrderData($creditmemo->getOrderId()),
            'order_items' => $this->getOrderItems($creditmemo->getOrderId()),
            'invoice_data' => $this->mcmHelper->getInvoiceFromData(),
            'sold_data' => $this->getBillingAddress($creditmemo->getOrderId()),
            'shipping_data' => $this->getShippingAddress($creditmemo->getOrderId()),
            'vendor_info' => $this->vendorInfo(),
            'totals' => $creditmemo,
            'reward_discount' => $this->rewardDiscount($creditmemo),
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
            $dir = $this->directoryList->getPath(DirectoryList::ROOT);
            $rootPath = $dir.'/pub/media/credit_memo/'.$fileName;

            //Download
            $this->_fileFactory->create(
                $fileName,
                [
                    'type' => 'filename',
                    'value' => 'media/credit_memo/'.$fileName,
                    'rm' => 1
                ],
                DirectoryList::PUB,
                'application/zip'
            );

            //Delete file
            if ($rootPath) {
                $this->deleteDirectory->deleteDirectory($rootPath);
            }
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
            'logo_src' => $this->mcmHelper->getLogoSrc(),
            'payment' => $this->getPaymentInfo($order),
            'shipment' => $this->getShippingInfo($order)
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

    protected function getPaymentInfo($order)
    {
        $paymentInfo = $this->_paymentData->getInfoBlock($order->getPayment())->setIsSecureMode(true)->toPdf();
        $paymentInfo = htmlspecialchars_decode($paymentInfo, ENT_QUOTES);
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key => $value) {
            if (strip_tags(trim($value)) == '') {
                unset($payment[$key]);
            }
        }
        reset($payment);

        $result = [];
        if (count($payment) > 1) {
            $result = $payment;
        } else {
            $result['of_payment'] = $payment;
        }

        return $result;
    }

    protected function getShippingInfo($order)
    {
        if (!$order->getIsVirtual()) {
            $shippingMethod = $order->getShippingDescription();
            $totalShippingChargesText = "(" . __(
                    'Total Shipping Charges'
                ) . " " . $order->formatPriceTxt(
                    $order->getShippingAmount()
                ) . ")";
            return [
                'shipping_method' => $shippingMethod,
                'shipping_total' => $totalShippingChargesText
            ];
        }
        return null;
    }

    public function vendorInfo()
    {
        return $this->mcmHelper->vendorInfo();
    }

    protected function getOrderItems($orderId) {
        $order = $this->orderRepository->get($orderId);
        return $order->getAllVisibleItems();
    }

    protected function rewardDiscount($creditmemo)
    {
        $order = $this->orderRepository->get($creditmemo->getOrderId());
        $purchase = $this->rewardsPurchase->getByOrder($order);
        $proportion = $this->getProportion($creditmemo);
        $spentAmount = round($purchase->getSpendAmount() * $proportion, 2);

        return -$spentAmount;
    }

    private function getProportion($creditmemo)
    {
        $order = $this->orderRepository->get($creditmemo->getOrderId());
        if ($order->getSubtotal() > 0) {
            $proportion = $creditmemo['subtotal'] / $order->getSubtotal();
        } else { // for zero orders with earning points
            $proportion = $this->cancelEarnedPointsService->getCreditmemoItemsQty($creditmemo) /
                $this->cancelEarnedPointsService->getCreditmemoOrderItemsQty($creditmemo);
        }
        if ($proportion > 1) {
            $proportion = 1;
        }

        return $proportion;
    }
}
