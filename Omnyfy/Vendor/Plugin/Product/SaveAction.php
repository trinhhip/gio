<?php
/**
 * Project: Multi Vendor
 * User: jing
 * Date: 2019-08-23
 * Time: 11:09
 */
namespace Omnyfy\Vendor\Plugin\Product;

class SaveAction
{
    protected $resultRedirectFactory;

    protected $messageManager;

    protected $session;

    protected $vendorConfig;

    protected $vendorResource;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Backend\Model\Session $session,
        \Omnyfy\Vendor\Model\Config $vendorConfig,
        \Omnyfy\Vendor\Model\Resource\Vendor $vendorResource
    ) {
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->messageManager = $context->getMessageManager();
        $this->session = $session;
        $this->vendorConfig = $vendorConfig;
        $this->vendorResource = $vendorResource;
    }

    public function aroundExecute($subject, callable $process)
    {
        $vendorInfo = $this->session->getVendorInfo();

        if (empty($vendorInfo) || !isset($vendorInfo['vendor_id']) || 0 == $vendorInfo['vendor_id']) {
            return $process();
        }

        $id = $subject->getRequest()->getParam('id');
        if (empty($id)) {
            return $process();
        }

        $vendorIds = $this->vendorResource->getVendorIdArrayByProductId($id);

        return $process();
    }

}
