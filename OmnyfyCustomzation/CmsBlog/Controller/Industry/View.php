<?php

/**
 * Project: CMS M2.
 * User: abhay
 * Date: 3/05/18
 * Time: 11:00 AM
 */

namespace OmnyfyCustomzation\CmsBlog\Controller\Industry;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use OmnyfyCustomzation\CmsBlog\Model\Industry;

class View extends Action
{

    protected $resultPageFactory;
    protected $resultForwardFactory;
    protected $countryRepository;

    public function __construct(
        Context $context, PageFactory $resultPageFactory, ForwardFactory $resultForwardFactory, Registry $coreRegistry, Industry $industryRepository
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->industryRepository = $industryRepository;

        parent::__construct($context);
    }

    public function execute()
    {
        $industry = $this->_initIndustry();
        if (empty($industry)) {
            //404
            $this->_forward('index', 'noroute', 'cms');
            return;
        }
        return $this->resultPageFactory->create();
    }

    protected function _initIndustry()
    {
        $industryId = $this->getRequest()->getParam('id');

        if (empty($industryId))
            return false;

        try {
            $industry = $this->industryRepository->load($industryId);
            $this->_coreRegistry->register('current_industry', $industry);

            if ($industryId != $industry->getId()) {
                return false;
            }

            if (!$industry->getStatus()) {
                return false;
            }

            return $industry;
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }

}
