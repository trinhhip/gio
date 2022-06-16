<?php
namespace Omnyfy\VendorSubscription\Controller\Adminhtml\Plan;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;

class MassDelete extends \Omnyfy\Vendor\Controller\Adminhtml\AbstractAction implements  HttpPostActionInterface
{
    protected $subscriptionResource;
    protected $planResource;
    protected $planCollectionFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Psr\Log\LoggerInterface $logger,
        \Omnyfy\VendorSubscription\Model\Resource\Subscription $subscriptionResource,
        \Omnyfy\VendorSubscription\Model\Resource\Plan $planResource,
        \Omnyfy\VendorSubscription\Model\Resource\Plan\CollectionFactory $planCollectionFactory
    ) {
        parent::__construct($context, $coreRegistry, $resultForwardFactory,$resultPageFactory, $authSession, $logger);
        $this->subscriptionResource = $subscriptionResource;
        $this->planResource = $planResource;
        $this->planCollectionFactory = $planCollectionFactory;
    }

    public function execute()
    {
        $postData = $this->getRequest()->getPostValue();
        $planDeleted = 0;
        $planDeletedError = 0;
        $plandIds = [];
        if (isset($postData['selected'])) {
            $plandIds = $postData['selected'];
        } elseif (isset($postData['excluded'])) {
            $plandIds = $this->planCollectionFactory->create()->getAllIds();
        }

        foreach ($plandIds as $planId) {
            if (!$this->subscriptionResource->getVendorActiveAssignedByPlanId($planId)) {
                $this->planResource->updateById('is_delete', 1, $planId);
                $planDeleted++;
            } else {
                $planDeletedError++;
            }
        }

        if ($planDeleted) {
            $this->getMessageManager()->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $planDeleted));
        }
        if ($planDeletedError) {
            $this->getMessageManager()->addErrorMessage(__('A total of %1 record(s) haven\'t been deleted.', $planDeletedError));
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('omnyfy_subscription/plan/index');
    }
}