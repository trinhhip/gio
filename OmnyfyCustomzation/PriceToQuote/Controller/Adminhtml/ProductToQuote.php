<?php


namespace OmnyfyCustomzation\PriceToQuote\Controller\Adminhtml;


use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Registry;

abstract class ProductToQuote extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'OmnyfyCustomzation_PriceToQuote::vermillion';

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
            ->addBreadcrumb(__('Product To Be Quoted'), __('Product To Be Quoted'))
            ->addBreadcrumb(__('Product To Be Quoted'), __('Product To Be Quoted'));
        return $resultPage;
    }
}
