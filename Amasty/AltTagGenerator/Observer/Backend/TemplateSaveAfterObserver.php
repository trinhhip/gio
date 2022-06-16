<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Observer\Backend;

use Amasty\AltTagGenerator\Api\Data\TemplateInterface;
use Amasty\AltTagGenerator\Model\Indexer\Template\TemplateProcessor;
use Amasty\AltTagGenerator\Model\ResourceModel\Template as TemplateResource;
use Amasty\AltTagGenerator\Model\Template;
use Amasty\AltTagGenerator\Model\Template\Registry;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class TemplateSaveAfterObserver implements ObserverInterface
{
    /**
     * @var TemplateResource
     */
    private $templateResource;

    /**
     * @var TemplateProcessor
     */
    private $templateProcessor;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        TemplateResource $templateResource,
        TemplateProcessor $templateProcessor,
        Registry $registry
    ) {
        $this->templateResource = $templateResource;
        $this->templateProcessor = $templateProcessor;
        $this->registry = $registry;
    }

    public function execute(Observer $observer)
    {
        /** @var Template $template */
        $template = $observer->getEvent()->getTemplate();

        if ($template) {
            $this->templateResource->addCommitCallback(function () use ($template) {
                $this->registry->save($template);
            });
            if ($template->dataHasChangedFor(TemplateInterface::CONDITIONS_SERIALIZED)) {
                $this->templateResource->addCommitCallback(function () use ($template) {
                    $this->templateProcessor->reindexRow((int) $template->getId());
                });
            }
        }
    }

    private function reindex($template)
    {
        $this->templateProcessor->reindexRow((int) $template->getId());
    }
}
