<?php

namespace OmnyfyCustomzation\Customer\Plugin\Magento\Customer\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Controller\Account\ForgotPasswordPost as ForgotPasswordPostBase;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ForgotPasswordPost
 */
class ForgotPasswordPost extends ForgotPasswordPostBase
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ForgotPasswordPost constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param Escaper $escaper
     * @param CustomerRepositoryInterface $customerRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        Escaper $escaper,
        CustomerRepositoryInterface $customerRepository,
        StoreManagerInterface $storeManager
    )
    {
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
        parent::__construct(
            $context,
            $customerSession,
            $customerAccountManagement,
            $escaper
        );
    }

    /**
     * @param ForgotPasswordPostBase $subject
     * @param $proceed
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    function aroundExecute(
        ForgotPasswordPostBase $subject,
        $proceed
    )
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();

        try {
            $this->customerRepository->get($subject->getRequest()->getParam('email'), $websiteId);
        } catch (\Exception $exception) {
            $this->messageManager->addComplexSuccessMessage(
                'addAfterForgotPasswordMessage',
                [
                    "message" => 'There is no account associated with this email address. Please try again or <a href="%1">click here</a> to create an account'
                ]
            );
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/forgotpassword');
        }

        return $proceed();
    }
}
