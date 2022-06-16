<?php
/**
 * Project: CMS M2.
 * User: abhay
 * Date: 01/06/18
 * Time: 11:00 AM
 */

namespace OmnyfyCustomzation\CmsBlog\Controller\ToolTemplate;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    protected $resultPageFactory;

    protected $resultForwardFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }
}
