<?php


namespace OmnyfyCustomzation\PriceToQuote\Controller\Adminhtml\Product;


use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use OmnyfyCustomzation\PriceToQuote\Controller\Adminhtml\ProductToQuote;

class Quote extends ProductToQuote
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $coreRegistry);
    }

    public function execute()
    {
        return $this->resultPageFactory->create();
    }
}
