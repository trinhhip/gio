<?php


namespace OmnyfyCustomzation\ShippingCalculatedWeight\Controller\Adminhtml\Rules;

use Magento\Backend\App\Action;
use OmnyfyCustomzation\ShippingCalculatedWeight\Model\CalculateWeightFactory;

/**
 * Skeleton for save.
 */
class Save extends Action
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
            $data['entity_id'] = $id ? $id : null;
            $rule = $this->calculateWeightFactory->create();
            $data['ship_from_country'] = isset($data['ship_from_country']) && is_array($data['ship_from_country']) ? implode(',', $data['ship_from_country']) : null;
            $data['ship_to_country'] = isset($data['ship_to_country']) && is_array($data['ship_to_country']) ? implode(',', $data['ship_to_country']) : null;

            $rule->setData($data);
            $rule->save();
            $this->messageManager->addSuccessMessage(__('You saved the rule.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $this->_redirect('*/*/');
    }
}
