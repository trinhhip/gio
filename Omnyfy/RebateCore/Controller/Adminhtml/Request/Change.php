<?php

namespace Omnyfy\RebateCore\Controller\Adminhtml\Request;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Serialize\Serializer\Json as JsonHelper;
use Omnyfy\RebateCore\Helper\Data;
use Omnyfy\Vendor\Model\VendorRepository;
use Omnyfy\RebateCore\Api\Data\IVendorRebateRepository;

/**
 * Class Change
 *
 * @package Omnyfy\RebateCore\Controller\Adminhtml\Request\Change
 */
class Change extends Action
{
    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * @var helperRebate
     */
    protected $helperRebate;

    /**
     * @var VendorFactory
     */
    protected $vendor;

    /**
     * @var IVendorRebateRepository
     */
    protected $rebateVendorRepository;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * Change constructor.
     * @param Context $context
     * @param Data $helperRebate
     * @param IVendorRebateRepository $rebateVendorRepository
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param VendorRepository $vendor
     * @param JsonHelper $jsonHelper
     */
    public function __construct(
        Context $context,
        Data $helperRebate,
        IVendorRebateRepository $rebateVendorRepository,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        VendorRepository $vendor,
        JsonHelper $jsonHelper
    )
    {
        parent::__construct($context);
        $this->jsonHelper = $jsonHelper;
        $this->vendor = $vendor;
        $this->rebateVendorRepository = $rebateVendorRepository;
        $this->_messageManager = $messageManager;
        $this->helperRebate = $helperRebate;
    }

    /**
     * Execute
     *
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $vendorId = $this->getRequest()->getParam('vendorId') ?? null;
        $rebateId = $this->getRequest()->getParam('rebateId') ?? null;
        $percentageUp = $this->getRequest()->getParam('percentageUp') ?? null;
        if (!empty($vendorId) && !empty($rebateId) && !empty($percentageUp)) {
            try {
                $vendorModel = $this->vendor->getById($vendorId);
                $rebateModel = $this->rebateVendorRepository->getRebateVendor($rebateId);
                $dataRequest = [
                    "vendor_rebate_id" => $rebateId,
                    "percentage" => $percentageUp
                ];
                $this->rebateVendorRepository->deleteChangeRequestData($rebateId);
                $this->rebateVendorRepository->insertValues($dataRequest);
                $this->sendEmailVendor($vendorModel, $rebateModel, $percentageUp);
                $this->sendEmailMo($vendorModel, $rebateModel, $percentageUp);
                $this->_messageManager->addSuccess('Rebate changes have been sent.');
            } catch (Exception $e) {
                $this->_messageManager->addErrorMessage('Rebate changes were sent error.');
            }
        }
        return;
    }

    /**
     * @param $vendorModel
     * @param $rebateModel
     * @param $percentageUp
     */
    public function sendEmailVendor($vendorModel, $rebateModel, $percentageUp)
    {
        $vars = [
            "vendorname" => $vendorModel->getName(),
            "rebatecurent" => $rebateModel->getLockedRebatePercentage(),
            "rebatenew" => $percentageUp,
            "rebateId" => $rebateModel->getRebateId(),
            "rebatename" => $rebateModel->getLockName()
        ];
        $sendEmail = [
            "email" => $vendorModel->getEmail(),
            "name" => $vendorModel->getName()
        ];
        $this->helperRebate->sendEmailVendorRequest($vars, $sendEmail);
    }

    /**
     * @param $vendorModel
     * @param $rebateModel
     * @param $percentageUp
     */
    public function sendEmailMo($vendorModel, $rebateModel, $percentageUp)
    {
        $vars = [
            "vendorname" => $vendorModel->getName(),
            "vendorid" => $vendorModel->getId(),
            "rebatecurent" => $rebateModel->getLockedRebatePercentage(),
            "rebateId" => $rebateModel->getRebateId(),
            "rebatenew" => $percentageUp,
            "rebatename" => $rebateModel->getLockName()
        ];
        $this->helperRebate->sendEmailMOSubmit($vars);
    }
}
