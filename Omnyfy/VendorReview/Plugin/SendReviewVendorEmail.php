<?php

namespace Omnyfy\VendorReview\Plugin;

class SendReviewVendorEmail
{
    /**
     * @var \Omnyfy\Vendor\Helper\Data
     */
    private $data;
    /**
     * @var \Omnyfy\Vendor\Model\VendorRepository
     */
    private $vendorRepository;

    public function __construct(
        \Omnyfy\Vendor\Helper\Data $data,
        \Omnyfy\Vendor\Model\VendorRepository $vendorRepository
    )
    {
        $this->data = $data;
        $this->vendorRepository = $vendorRepository;
    }

    public function beforeExecute(\Omnyfy\VendorReview\Controller\Vendor\Post $subject)
    {
        $params = $subject->getRequest()->getParams();
        if (isset($params['id'])) {
            try {
                $vendor = $this->vendorRepository->getById($params['id']);
                $this->data->sendReviewVendorEmailToMo($vendor->getName(), $params['nickname'], $params['title'], $params['detail']);
                $this->data->sendReviewVendorEmailToVendor($vendor, $params['nickname'], $params['title'], $params['detail']);
            } catch (\Exception $e) {
                throw new \Exception(__('Some thing wen\'t wrong send email review vendor'));
            }
        }
    }
}