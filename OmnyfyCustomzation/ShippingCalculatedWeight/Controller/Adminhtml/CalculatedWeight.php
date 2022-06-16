<?php


namespace OmnyfyCustomzation\ShippingCalculatedWeight\Controller\Adminhtml;


use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Registry;

abstract class CalculatedWeight extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'OmnyfyCustomzation_ShippingCalculatedWeight::vermillion';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry
    )
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Init page
     *
     * @param Page $resultPage
     * @return Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('OmnyfyCustomzation_ShippingCalculatedWeight::vermillion')
            ->addBreadcrumb(__('Calculation Rules'), __('Calculation Rules'))
            ->addBreadcrumb(__('Calculation Rules'), __('Calculation Rules'));
        return $resultPage;
    }
}