<?php

namespace Amasty\RegenerateUrlRewrites\Controller\Adminhtml\Command;

use Amasty\RegenerateUrlRewrites\Api\GeneratorInterface;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;

class Status extends Action
{
    const ADMIN_RESOURCE = 'Amasty_RegenerateUrlRewrites::config';

    /**
     * @var GeneratorInterface
     */
    private $generator;

    public function __construct(
        Action\Context $context,
        GeneratorInterface $generator
    ) {
        parent::__construct($context);
        $this->generator = $generator;
    }

    /**
     * @return Json
     */
    public function execute(): Json
    {
        $processIdentity = $this->getRequest()->getParam('processIdentity');
        /** @var \Amasty\RegenerateUrlRewrites\Api\Data\GenerateStatusInterface $result */
        $result = $this->generator->getStatus($processIdentity);
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);

        return $resultJson;
    }
}
