<?php

namespace Omnyfy\Mcm\Controller\Adminhtml\SalesOrder;

use Magento\Backend\Model\Session as BackendSession;
use Omnyfy\Mcm\Helper\Data as HelperData;
use Omnyfy\Mcm\Plugin\Vendor\Model\Vendor;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\AuthorizationInterface;

class PickList extends \Omnyfy\Mcm\Controller\Adminhtml\AbstractAction {

    protected $_abstractPdf;

    protected $_mcmHelper;

    protected $orderRepository;

    protected $layoutFactory;

    protected $pdfHelper;

    protected $countryInformationAcquirerInterface;

    protected $backendSession;

    private $authorization;
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
        BackendSession $backendSession,
        \Magento\Theme\Block\Html\Header\Logo $logo
    ) {
        $this->_abstractPdf = $abstractPdf;
        $this->_mcmHelper = $helper;
        $this->orderRepository = $orderRepository;
        $this->layoutFactory = $layoutFactory;
        $this->pdfHelper = $pdfHelper;
        $this->countryInformationAcquirerInterface = $countryInformationAcquirerInterface;
        $this->authorization = $authorization;
        $this->backendSession = $backendSession;

        parent::__construct($context, $coreRegistry, $resultForwardFactory, $resultPageFactory, $authSession, $logger);
        $this->logo = $logo;
    }

    public function execute()
    {
        $error = 1;
        $orderId = $this->getRequest()->getParam('order_id');
        if ($orderId) {
            $orderItems = $this->getOrderItems($orderId);
            if (!empty($orderItems)) {
                $error = 0;
                try {
                    $htmlContent = $this->generateOrderHtml($orderId, $orderItems);
                    $this->pdfHelper->setData($htmlContent);
                    $this->pdfHelper->renderPickList();
                } catch (\Exception $exception) {
                    $error = 1;
                    $this->messageManager->addErrorMessage(__($exception->getMessage()));
                    $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
                    $resultRedirect->setPath('sales/order/index');
                    return $resultRedirect;
                }
            }
        }
        if ($error) {
            $this->messageManager->addErrorMessage(__("This Order doesn't exist."));
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
            'vendor_id' => $this->_mcmHelper->vendorInfo()['vendor_id'],
            'logo_url' => $this->logo->getLogoSrc()
        ];

        $block->setData($data);

        return $block->toHtml();
    }

    public function getOrderData($orderId) {
        $order = $this->orderRepository->get($orderId);
        $moName = $this->_mcmHelper->getMoName();

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
            'mo_name' => $moName,
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
