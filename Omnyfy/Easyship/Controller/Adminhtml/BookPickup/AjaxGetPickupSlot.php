<?php
namespace Omnyfy\Easyship\Controller\Adminhtml\BookPickup;

class AjaxGetPickupSlot extends \Magento\Backend\App\Action
{
    protected $jsonFactory;
    protected $easyVendorLocFactory;
    protected $apiHelper;
    protected $sourceCollectionFactory;
    protected $vSourceStockResource;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Omnyfy\Easyship\Model\EasyshipVendorLocationFactory $easyVendorLocFactory,
        \Omnyfy\Easyship\Helper\Api $apiHelper,
        \Omnyfy\Easyship\Model\ResourceModel\Source\CollectionFactory $sourceCollectionFactory,
        \Omnyfy\Vendor\Model\Resource\VendorSourceStock $vSourceStockResource
    ){
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->easyVendorLocFactory = $easyVendorLocFactory;
        $this->apiHelper = $apiHelper;
        $this->sourceCollectionFactory = $sourceCollectionFactory;
        $this->vSourceStockResource = $vSourceStockResource;
    }

    public function execute(){
        $token = null;
        $data['error'] = false;
        $data['message'] = null;

        $courierId = $this->getRequest()->getParam('courier_id');
        $sourceStockId = $this->getRequest()->getParam('source_stock_id');
        $sourceCode = $this->vSourceStockResource->getSourceCodeById($sourceStockId);

        $source = $this->sourceCollectionFactory->create()->getSourceAccount($sourceCode);
        if ($source != null) {
            $token = $source->getAccessToken();
        }

        if($token){
            try {
                $slots = $this->apiHelper->getPickupSlot($token, $courierId);
                $data['data'] = json_decode($slots, true);
                if(isset($data['data']['error'])){
                    $msg = $data['data']['error'];
                    $data['error'] = true;
                    $data['message'] = $msg;
                    $this->messageManager->addError(__($msg));
                }
            } catch (\Exception $e) {
                $data['error'] = true;
                $data['message'] = $e->getMessage();
            }
        }else{
            $data['error'] = true;
            $data['message'] = 'No access token found';
            $this->messageManager->addError(__('No access token found'));
        }

        return $this->jsonFactory->create()->setData($data);
    }
}
