<?php
namespace Omnyfy\Enquiry\Controller\Enquiry\Usage;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Validate extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{
    protected $resultJsonFactory;

    private $enquiryBlock;

    public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Omnyfy\Enquiry\Block\Catalog\Vendor $enquiryBlock,
        Context $context
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->enquiryBlock = $enquiryBlock;
    }

    public function execute()
    {
        try{
            $productId = $this->getRequest()->getParam('productId');
            $vendorId = $this->getRequest()->getParam('vendorId');
            $data['isEnqAvailable'] = $this->enquiryBlock->isProductEnquiryActive($vendorId, $productId);
        } catch (NoSuchEntityException $e) {
            $data['errors'] = $e->getMessage();
        }
        $result = $this->resultJsonFactory->create();
        return $result->setData($data);
    }
}
