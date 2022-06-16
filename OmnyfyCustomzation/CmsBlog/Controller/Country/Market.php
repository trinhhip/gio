<?php
/**
 * Project: CMS M2.
 * User: abhay
 * Date: 26/4/18
 * Time: 03:00 PM
 */

namespace OmnyfyCustomzation\CmsBlog\Controller\Country;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use OmnyfyCustomzation\CmsBlog\Model\Country;

class Market extends Action
{
    protected $resultPageFactory;

    protected $resultForwardFactory;

    protected $countryRepository;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        Registry $coreRegistry,
        Country $countryRepository
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->countryRepository = $countryRepository;

        parent::__construct($context);
    }

    public function execute()
    {
        return $this->resultPageFactory->create();
    }
}
