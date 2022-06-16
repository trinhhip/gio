<?php
/**
 * Project: Multi Vendor
 * User: jing
 * Date: 2019-08-22
 * Time: 17:56
 */
namespace Omnyfy\Vendor\Plugin\Product;

class AttributeAction
{
    protected $resultRedirectFactory;

    protected $messageManager;

    protected $session;

    protected $vendorConfig;

    protected $vendorResource;

    protected $attributeHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Backend\Model\Session $session,
        \Omnyfy\Vendor\Model\Config $vendorConfig,
        \Omnyfy\Vendor\Model\Resource\Vendor $vendorResource,
        \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper
    ) {
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->messageManager = $context->getMessageManager();
        $this->session = $session;
        $this->vendorConfig = $vendorConfig;
        $this->vendorResource = $vendorResource;
        $this->attributeHelper = $attributeHelper;
    }

    public function aroundExecute($subject, callable $process)
    {
        $vendorInfo = $this->session->getVendorInfo();

        if (empty($vendorInfo) || !isset($vendorInfo['vendor_id']) || 0 == $vendorInfo['vendor_id']) {
            return $process();
        }

        //load selected ids, check if mo product included
        //if so redirect back to product list page with error message
        $ids = $this->attributeHelper->getProductIds();
        $vendorIds = $this->vendorResource->getVendorIdByProducts($ids);
        $vendorIds = array_unique(array_values($vendorIds));

        return $process();
    }

}
