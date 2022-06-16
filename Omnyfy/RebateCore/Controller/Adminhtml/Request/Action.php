<?php

namespace Omnyfy\RebateCore\Controller\Adminhtml\Request;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\Action as ActionFramework;
use Magento\Framework\Controller\Result\JsonFactory;
use Omnyfy\RebateCore\Api\Data\IVendorRebateRepository;

/**
 * Class Action
 *
 * @package Omnyfy\RebateCore\Controller\Adminhtml\Request\Action
 */
class Action extends ActionFramework
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;


    /**
     * @var IVendorRebateRepository
     */
    protected $rebateVendorRepository;

    /**
     * Action constructor.
     * @param Context $context
     * @param IVendorRebateRepository $rebateVendorRepository
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        IVendorRebateRepository $rebateVendorRepository,
        JsonFactory $resultJsonFactory
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->rebateVendorRepository = $rebateVendorRepository;
    }

    /**
     * Execute
     *
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $vendorRebateId = $this->getRequest()->getParam('vendorRebateId') ?? null;
        $percentageUp = $this->getRequest()->getParam('percentageUp') ?? null;
        $action = $this->getRequest()->getParam('action') ?? null;
        $result = $this->resultJsonFactory->create();
        if (!empty($vendorRebateId) && !empty($percentageUp)) {
            try {
                if ($action) {
                    $rebateModel = $this->rebateVendorRepository->getRebateVendor($vendorRebateId);
                    $rebateModel->setLockedRebatePercentage($percentageUp);
                    $rebateModel->save();
                    $this->rebateVendorRepository->deleteChangeRequestData($vendorRebateId);
                    $data = [
                        'content' => __('Accepted'),
                        'action' => "active",
                        'percentage' => $percentageUp
                    ];
                    return $result->setData($data);
                }
                $this->rebateVendorRepository->deleteChangeRequestData($vendorRebateId);
                $data = [
                    'content' => __('Declined'),
                    'action' => "decline"
                ];
                return $result->setData($data);
            } catch (Exception $e) {
            }
        }
    }

}
