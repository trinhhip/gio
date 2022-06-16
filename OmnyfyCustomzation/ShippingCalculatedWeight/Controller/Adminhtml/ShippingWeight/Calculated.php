<?php


namespace OmnyfyCustomzation\ShippingCalculatedWeight\Controller\Adminhtml\ShippingWeight;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use OmnyfyCustomzation\ShippingCalculatedWeight\Model\ShippingCalculate;

class Calculated extends Action
{
    /**
     * @var ShippingCalculate
     */
    public $shippingCalculate;

    public function __construct(
        Context $context,
        ShippingCalculate $shippingCalculate
    )
    {
        $this->shippingCalculate = $shippingCalculate;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $productUpdated = $this->shippingCalculate->updateCalculatedShippingWeight();
            $this->messageManager->addSuccessMessage(__('You have successfully updated %1 products.', $productUpdated));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $this->_redirect('catalog/product');
    }
}