<?php
namespace Omnyfy\VendorReview\Controller\Adminhtml\Customer;

class VendorReviews extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Get customer's product reviews list
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $customerId = $this->initCurrentCustomer();
        $resultLayout = $this->resultLayoutFactory->create();
        $block = $resultLayout->getLayout()->getBlock('admin.vendor.reviews');
        $block->setCustomerId($customerId)->setUseAjax(true);
        return $resultLayout;
    }
}
