<?php

declare(strict_types=1);

namespace Amasty\RegenerateUrlRewrites\Controller\Adminhtml\Command;

use Amasty\RegenerateUrlRewrites\Api\GeneratorInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

class Terminate extends Action
{
    const ADMIN_RESOURCE = 'Amasty_RegenerateUrlRewrites::config';

    /**
     * @var GeneratorInterface
     */
    private $generator;

    public function __construct(
        Context $context,
        GeneratorInterface $generator
    ) {
        parent::__construct($context);
        $this->generator = $generator;
    }

    public function execute()
    {
        if ($processIdentity = $this->getRequest()->getParam('processIdentity')) {
            $this->generator->terminate($processIdentity);
        }
    }
}
