<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Controller\Adminhtml\Template;

use Amasty\AltTagGenerator\Api\Data\TemplateInterface;
use Amasty\AltTagGenerator\Model\ResourceModel\Template\CollectionFactory;
use Amasty\AltTagGenerator\Model\Template\Command\DeleteTemplateInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Phrase;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

class MassDelete extends AbstractMassAction
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_AltTagGenerator::template_delete';

    /**
     * @var DeleteTemplateInterface
     */
    private $deleteTemplate;

    public function __construct(
        DeleteTemplateInterface $deleteTemplate,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Context $context,
        LoggerInterface $logger
    ) {
        parent::__construct($filter, $collectionFactory, $context, $logger);
        $this->deleteTemplate = $deleteTemplate;
    }

    protected function itemAction(TemplateInterface $template): void
    {
        $this->deleteTemplate->execute($template);
    }

    protected function getSuccessMessage(int $collectionSize = 0): Phrase
    {
        if ($collectionSize) {
            return __('A total of %1 record(s) have been deleted.', $collectionSize);
        }

        return __('No records have been deleted.');
    }
}
