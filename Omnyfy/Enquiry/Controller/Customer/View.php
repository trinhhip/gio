<?php
/**
 * Project: CMS M2.
 * User: abhay
 * Date: 21/05/18
 * Time: 11:00 AM
 */

namespace Omnyfy\Enquiry\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NotFoundException;

class View extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;

    protected $resultForwardFactory;

    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\Registry $coreRegistry,
        \Omnyfy\Enquiry\Model\Enquiries $enquiresRepository
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
		$this->_customerSession = $customerSession;
		$this->_coreRegistry = $coreRegistry;
        $this->enquiresRepository = $enquiresRepository;

        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->_customerSession->isLoggedIn()) {
            /** @var \Magento\Framework\UrlInterface $urlInterface */
            $urlInterface = $this->_objectManager->get('Magento\Framework\UrlInterface');
            $currentUrl = $urlInterface->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
            return $this->_redirect('customer/account/login',array('referer' => base64_encode($currentUrl)));
        }
        $industry = $this->_initEnquires();
        if (empty($industry)) {
            //404
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');
            return $resultForward;
        }
        return $this->resultPageFactory->create();
    }

    protected function _initEnquires() {
        $enquiryId = $this->getRequest()->getParam('id');

		if (!$this->_customerSession->isLoggedIn()) {
            $this->_redirect('customer/account');
            return;
        }

        if (empty($enquiryId)) return false;

        try {
            $enquiry = $this->enquiresRepository->load($enquiryId);
			$this->_coreRegistry->register('current_enquiry', $enquiry);

            if ($enquiryId != $enquiry->getId()) {
                return false;
            }

			// Validate customer
			if ($enquiry->getCustomerId() != $this->_customerSession->getCustomerId()) {
				$this->messageManager->addError(__('Invalid enquiry'));
				$this->_redirect('*/*/index');
				return;
			}

            if (!$enquiry->getStatus()) {
                return false;
            }

            return $enquiry;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }
    }
}
