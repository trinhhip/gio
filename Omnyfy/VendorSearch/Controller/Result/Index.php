<?php


namespace Omnyfy\VendorSearch\Controller\Result;

use Magento\Framework\Controller\Result\JsonFactory;
use Omnyfy\VendorSearch\Block\Search\Result;
use Omnyfy\VendorSearch\Model\VendorSearch\ToolbarMemorizer;

class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Omnyfy\VendorSearch\Helper\Data
     */
    protected $_helperData;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    protected $_searchResult;
    protected $_toolbarMemorizer;
    private $mapSearchData;
    protected $_jsonResultFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Omnyfy\VendorSearch\Helper\Data $helperData,
        \Omnyfy\VendorSearch\Helper\MapSearchData $mapSearchData,
        ToolbarMemorizer $toolbarMemorizer,
        Result $searchResult,
        JsonFactory $jsonResultFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_helperData = $helperData;
        $this->_searchResult = $searchResult;
        $this->_toolbarMemorizer = $toolbarMemorizer;
        $this->mapSearchData = $mapSearchData;
        $this->_jsonResultFactory = $jsonResultFactory;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();

        if ($this->mapSearchData->isEnabled()
            && $this->getRequest()->isAjax()
        ) {
            $navigation = $resultPage->getLayout()->getBlock('vendor.search.result.filter');
            $vendors   = $resultPage->getLayout()->getBlock('vendor.search.result.result');
            $vendorCounter   = $resultPage->getLayout()->getBlock('vendor.search.summery');
            $result     = ['vendors' => $vendors->toHtml(), 'navigation' => $navigation->toHtml(), 'vendorCounter' => $vendorCounter->toHtml()];

            $resultPage = $this->_jsonResultFactory->create();
            $resultPage->setData($this->mapSearchData->jsonEncode($result));
            return $resultPage;
        } else {
            if(!$this->_searchResult->getFilters()){
                $resultPage->getConfig()->setPageLayout('1column');
            }
            $resultPage->getConfig()->getTitle()->set($this->_helperData->getPageTitle());
            $this->_toolbarMemorizer->memorizeParams();
            return $resultPage;
        }
    }
}
