<?php


namespace OmnyfyCustomzation\Catalog\Controller\Product;


use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\View\Result\PageFactory;

class View extends \Magento\Catalog\Controller\Product\View
{
    /**
     * @var Data
     */
    private $jsonHelper;

    public function __construct(
        Context $context,
        \Magento\Catalog\Helper\Product\View $viewHelper,
        ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        Data $jsonHelper
    )
    {
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $viewHelper, $resultForwardFactory, $resultPageFactory);
    }

    public function execute()
    {
        if ($this->getRequest()->getParam('options')
            && !$this->_request->getParam('___from_store')
            && $this->_request->isPost()
            && $this->_request->getParam(self::PARAM_NAME_URL_ENCODED)
        ) {
            $product = $this->_initProduct();

            if (!$product) {
                return $this->noProductRedirect();
            }
            $notice = $product->getTypeInstance()->getSpecifyOptionMessage();
            $this->messageManager->addNoticeMessage($notice);

            if ($this->getRequest()->isAjax()) {
                $this->getResponse()->representJson(
                    $this->jsonHelper->jsonEncode(
                        [
                            'backUrl' => $this->_url->getCurrentUrl()
                        ]
                    )
                );
                return;
            }
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setRefererOrBaseUrl();
            return $resultRedirect;
        }
        return parent::execute();
    }
}
