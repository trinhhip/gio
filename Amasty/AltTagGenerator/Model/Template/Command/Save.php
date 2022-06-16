<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Template\Command;

use Amasty\AltTagGenerator\Api\Data\TemplateInterface;
use Amasty\AltTagGenerator\Model\ResourceModel\Template as TemplateResource;
use Amasty\AltTagGenerator\Model\Template;
use Exception;
use Magento\Framework\EntityManager\Operation\Update\UpdateExtensions;
use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;

class Save implements SaveInterface
{
    /**
     * @var TemplateResource
     */
    private $templateResource;

    /**
     * @var UpdateExtensions
     */
    private $updateExtensions;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        TemplateResource $templateResource,
        UpdateExtensions $updateExtensions,
        LoggerInterface $logger
    ) {
        $this->templateResource = $templateResource;
        $this->updateExtensions = $updateExtensions;
        $this->logger = $logger;
    }

    /**
     * @param TemplateInterface|Template $template
     * @return void
     * @throws CouldNotSaveException
     */
    public function execute(TemplateInterface $template): void
    {
        try {
            $this->templateResource->save($template);
            $this->updateExtensions->execute($template);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('Could not save Rule'), $e);
        }
    }
}
