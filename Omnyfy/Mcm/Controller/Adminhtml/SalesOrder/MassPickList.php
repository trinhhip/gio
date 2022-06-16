<?php

namespace Omnyfy\Mcm\Controller\Adminhtml\SalesOrder;

use Magento\Backend\Model\Session as BackendSession;
use Magento\Framework\App\Filesystem\DirectoryList;
use Omnyfy\Mcm\Helper\Data as HelperData;
use Omnyfy\Mcm\Plugin\Vendor\Model\Vendor;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\AuthorizationInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class MassPickList extends \Omnyfy\Mcm\Controller\Adminhtml\AbstractAction {

    protected $_abstractPdf;

    protected $_mcmHelper;

    protected $orderRepository;

    protected $layoutFactory;

    protected $pdfHelper;

    protected $countryInformationAcquirerInterface;

    protected $backendSession;

    private $authorization;

    protected $filter;

    protected $collectionFactory;

    protected $fileFactory;

    protected $directoryList;

    protected $file;

    protected $deleteDirectory;
    /**
     * @var \Magento\Theme\Block\Html\Header\Logo
     */
    private $logo;

    /**
     * Y coordinate
     *
     * @var int
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Psr\Log\LoggerInterface $logger,
        \Omnyfy\Mcm\Model\VendorPayoutInvoice\Pdf\AbstractPdf $abstractPdf,
        HelperData $helper,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Omnyfy\Core\Helper\DomPdfInterface $pdfHelper,
        CountryInformationAcquirerInterface $countryInformationAcquirerInterface,
        AuthorizationInterface $authorization,
        Filter $filter,
        CollectionFactory $collectionFactory,
        BackendSession $backendSession,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Io\File $file,
        \Omnyfy\Mcm\Model\DeleteDirectory $deleteDirectory,
        \Magento\Theme\Block\Html\Header\Logo $logo
    ) {
        $this->_abstractPdf = $abstractPdf;
        $this->_mcmHelper = $helper;
        $this->orderRepository = $orderRepository;
        $this->layoutFactory = $layoutFactory;
        $this->pdfHelper = $pdfHelper;
        $this->countryInformationAcquirerInterface = $countryInformationAcquirerInterface;
        $this->authorization = $authorization;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->backendSession = $backendSession;
        $this->fileFactory = $fileFactory;
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->deleteDirectory = $deleteDirectory;

        parent::__construct($context, $coreRegistry, $resultForwardFactory, $resultPageFactory, $authSession, $logger);
        $this->logo = $logo;
    }

    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $orderIds = $collection->getAllIds();

        if (!empty($orderIds)) {
            $zipPath = 'picklist_'.time();
            try {
                foreach ($orderIds as $orderId) {
                    $orderItems = $this->getOrderItems($orderId);
                    $htmlContent = $this->generateOrderHtml($orderId, $orderItems);
                    $this->pdfHelper->newDompdf();
                    $this->pdfHelper->setData($htmlContent);

                    $fileName = 'vendor_order_' . $orderId . '.pdf';
                    $filePath = $this->directoryList->getPath(DirectoryList::MEDIA)."/picklist/".$zipPath;
                    if ( ! file_exists($filePath)) {
                        $this->file->mkdir($filePath);
                    }

                    $this->saveFile($filePath .'/'. $fileName);
                }
                $this->downloadFile($zipPath);
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage(__("These options are not downloadable"));
            }
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('sales/order/index');
            return $resultRedirect;
        }
    }

    public function generateOrderHtml($orderId, $orderItems)
    {
        /** @var \Magento\Framework\View\Element\Template $block */
        $block = $this->layoutFactory->create()->createBlock('Magento\Framework\View\Element\Template');
        $block->setTemplate('Omnyfy_Mcm::order/picklist.phtml');

        $data = [
            'order_data' => $this->getOrderData($orderId),
            'order_items' => $orderItems,
            'vendor_id' => $this->getVendor(),
            'logo_url' => $this->logo->getLogoSrc()
        ];

        $block->setData($data);

        return $block->toHtml();
    }

    public function downloadFile($zipPath)
    {
        try {
            $dir = $this->directoryList->getPath(DirectoryList::ROOT);
            $rootPath = $dir.'/pub/media/picklist/'.$zipPath;
            chdir($rootPath);

            $zip = new \ZipArchive();
            $zip->open($zipPath.'.zip', \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($rootPath),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file)
            {
                // Skip directories (they would be added automatically)
                if (!$file->isDir())
                {
                    // Get real and relative path for current file
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($rootPath) + 1);

                    // Add current file to archive
                    $zip->addFile($filePath, $relativePath);
                }
            }

            $zip->close();

            //Download
            $this->fileFactory->create(
                $zipPath.'.zip',
                [
                    'type' => 'filename',
                    'value' => 'media/picklist/'.$zipPath.'/'.$zipPath.'.zip',
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
            $this->messageManager->addErrorMessage(__("ZipArchive class not found"));
        }
    }

    public function saveFile($filePath)
    {
        file_put_contents($filePath, $this->pdfHelper->save());
    }

    public function getOrderData($orderId) {
        $order = $this->orderRepository->get($orderId);

        if ($order->getShippingAddress()) {
            $name = $order->getShippingAddress()->getName();
            $telephone = $order->getShippingAddress()->getTelephone();
            $address = $this->getAddress($order->getShippingAddress());
        } else {
            $name = '';
            $telephone = '';
            $address = '';
        }

        $data = [
            'increment_id' => $order->getIncrementId(),
            'date' => $this->_abstractPdf->getDateWithFormat($order->getCreatedAt(), 'd M Y'),
            'name' => $name,
            'telephone' => $telephone,
            'address' => $address,
            'mo_name' => $this->_mcmHelper->getMoName(),
            'logo_src' => $this->_mcmHelper->getLogoSrc()
        ];

        return $data;
    }

    public function getAddress($shippingAddress) {
        $street = $shippingAddress->getStreet();
        $city = $shippingAddress->getCity();
        $region = $shippingAddress->getRegion();
        $country = $this->getCountryName($shippingAddress->getCountryId());

        return $street[0] . ', ' . $city . ', ' . $region . ', ' . $country;
    }

    public function getCountryName($countryCode, $type="local") {
        $countryName = null;
        try {
            $data = $this->countryInformationAcquirerInterface->getCountryInfo($countryCode);
            if($type == "local"){
                $countryName = $data->getFullNameLocale();
            }else {
                $countryName = $data->getFullNameLocale();
            }
        } catch (NoSuchEntityException $e) {}
        return $countryName;
    }

    public function getOrderItems($orderId) {
        $order = $this->orderRepository->get($orderId);
        return $order->getAllVisibleItems();
    }

    public function getVendor() {
        $vendorInfo = $this->backendSession->getVendorInfo();
        return $vendorInfo['vendor_id'];
    }

    protected function _isAllowed()
    {
        return $this->authorization->isAllowed('Omnyfy_Mcm::pick_list');
    }
}
