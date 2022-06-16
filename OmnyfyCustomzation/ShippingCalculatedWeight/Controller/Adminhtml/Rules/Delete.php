<?php


namespace OmnyfyCustomzation\ShippingCalculatedWeight\Controller\Adminhtml\Rules;


use Magento\Backend\App\Action;
use OmnyfyCustomzation\ShippingCalculatedWeight\Model\CalculateWeightFactory;

/**
 * Skeleton for save.
 */
class Delete extends Action
{

    /**
     * @var CalculateWeightFactory
     */
    private $calculateWeightFactory;

    public function __construct(
        Action\Context $context,
        CalculateWeightFactory $calculateWeightFactory
    )
    {
        $this->calculateWeightFactory = $calculateWeightFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->getRequest()->getParams();
        try {
            $id = isset($data['entity_id']) ? $data['entity_id'] : null;
            $rule = $this->calculateWeightFactory->create();
            if ($id){
                $rule->load($id);
                $rule->delete();
                $this->messageManager->addSuccessMessage(__('Deleted the rule successfully.'));
            }else{
                $this->messageManager->addWarningMessage(__('Can\'t find the rule to delete'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $this->_redirect('*/*/');
    }
}