<?php
namespace Omnyfy\Vendor\Controller\Adminhtml\Vendor\Type;

use Magento\Ui\Component\MassAction\Filter;

class MassDisable extends \Omnyfy\Vendor\Controller\Adminhtml\AbstractAction
{
    const ADMIN_RESOURCE = 'Omnyfy_Vendor::vendor_types';

    protected $resourceKey = 'Omnyfy_Vendor::vendor_types';

    protected $filter;

    protected $collectionFactory;

    protected $vendorResource;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Omnyfy\Vendor\Model\Resource\VendorType\CollectionFactory $collectionFactory,
        \Omnyfy\Vendor\Model\Resource\Vendor $vendorResource
    )
    {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->vendorResource = $vendorResource;
        parent::__construct($context, $coreRegistry, $resultForwardFactory, $resultPageFactory, $authSession, $logger);
    }



    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        if ($collection->getItems() > 0) {
            $countSuccess = 0;
            foreach ($collection->getItems() as $item) {
                $item->setStatus(0);
                $item->save();
                $countSuccess++;
            }
            $this->messageManager->addSuccessMessage("Inactive $countSuccess Vendor Types successfully.");
        } else {
            $this->messageManager->addErrorMessage('We can\'t find the any Vendor Types.');
        }

        return $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)->setPath('omnyfy_vendor/vendor_type/index');
    }
}