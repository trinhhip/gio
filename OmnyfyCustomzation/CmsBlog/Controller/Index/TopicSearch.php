<?php

namespace OmnyfyCustomzation\CmsBlog\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class TopicSearch extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;


    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Default Article Search page
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();

        $resultRedirect = $this->resultPageFactory->create();
        $blockInstance = $resultRedirect->getLayout()->getBlock('cms.topic.search');
        $this->getResponse()->setBody($blockInstance->toHtml());
    }
}
