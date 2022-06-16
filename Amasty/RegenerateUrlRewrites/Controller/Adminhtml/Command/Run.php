<?php

declare(strict_types=1);

namespace Amasty\RegenerateUrlRewrites\Controller\Adminhtml\Command;

use Amasty\RegenerateUrlRewrites\Api\Data\GenerateStartResultInterface;
use Amasty\RegenerateUrlRewrites\Api\Data\GenerateStartResultInterfaceFactory;
use Amasty\RegenerateUrlRewrites\Api\GeneratorInterface;
use Amasty\RegenerateUrlRewrites\Generator\Command\CommandResultInterface;
use Amasty\RegenerateUrlRewrites\Generator\Generate\Config\ConfigResolver;
use Amasty\RegenerateUrlRewrites\Generator\Generate\Status\Message;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Stdlib\Parameters;

class Run extends Action
{
    const ADMIN_RESOURCE = 'Amasty_RegenerateUrlRewrites::config';

    /**
     * @var GeneratorInterface
     */
    private $generator;

    /**
     * @var ConfigResolver
     */
    private $configResolver;

    /**
     * @var Parameters
     */
    private $parameters;

    /**
     * @var GenerateStartResultInterfaceFactory
     */
    private $generateStartResultFactory;

    public function __construct(
        GeneratorInterface $generator,
        ConfigResolver $configResolver,
        Parameters $parameters,
        GenerateStartResultInterfaceFactory $generateStartResultFactory,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->generator = $generator;
        $this->configResolver = $configResolver;
        $this->parameters = $parameters;
        $this->generateStartResultFactory = $generateStartResultFactory;
    }

    /**
     * @return Json
     */
    public function execute(): Json
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {
            $entityTypeId = (int)$this->getRequest()->getParam('type_id');
            $formSerialized = (string)$this->getRequest()->getParam('form');
            $this->parameters->fromString($formSerialized);
            $formData = $this->parameters->toArray();
            $entityType = $this->configResolver->getEntityType($entityTypeId);
            $config = $this->configResolver->fromForm($entityType, $formData);
            $resultData = $this->generator->start($config);
        } catch (\Exception $e) {
            /** @var GenerateStartResultInterface $result */
            $resultData = $this->generateStartResultFactory->create();
            $resultData->setError(
                [
                    Message::TYPE => CommandResultInterface::MESSAGE_CRITICAL,
                    Message::MESSAGE => $e->getMessage()
                ]
            );
        }
        $resultJson->setData($resultData);

        return $resultJson;
    }
}
