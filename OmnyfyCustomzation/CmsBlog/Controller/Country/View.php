<?php

/**
 * Project: CMS M2.
 * User: abhay
 * Date: 27/3/18
 * Time: 03:00 PM
 */

namespace OmnyfyCustomzation\CmsBlog\Controller\Country;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use OmnyfyCustomzation\CmsBlog\Model\Country;

class View extends Action
{

    protected $resultPageFactory;
    protected $resultForwardFactory;
    protected $countryRepository;

    public function __construct(
        Context $context, PageFactory $resultPageFactory, ForwardFactory $resultForwardFactory, Registry $coreRegistry, Country $countryRepository
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
        $country = $this->_initCountry();
        if (empty($country)) {
            //404
            $this->_forward('index', 'noroute', 'cms');
            return;
        }
        return $this->resultPageFactory->create();
    }

    protected function _initCountry()
    {
        $countryId = $this->getRequest()->getParam('id');

        if (empty($countryId))
            return false;

        try {
            $country = $this->countryRepository->load($countryId);
            $country->setVisitiors($country->getVisitiors() + 1);
            $country->save();

            $this->_coreRegistry->register('current_country', $country);

            if ($countryId != $country->getId()) {
                return false;
            }

            if (!$country->getStatus()) {
                return false;
            }

            return $country;
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }

}
