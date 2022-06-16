<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Template\Command;

use Amasty\AltTagGenerator\Api\Data\TemplateInterface;
use Amasty\AltTagGenerator\Model\ResourceModel\Template as TemplateResource;
use Magento\Framework\Exception\CouldNotDeleteException;

class DeleteTemplate implements DeleteTemplateInterface
{
    /**
     * @var TemplateResource
     */
    private $templateResource;

    public function __construct(TemplateResource $templateResource)
    {
        $this->templateResource = $templateResource;
    }

    /**
     * @param TemplateInterface $template
     * @return void
     * @throws CouldNotDeleteException
     */
    public function execute(TemplateInterface $template): void
    {
        try {
            $this->templateResource->delete($template);
        } catch (\Exception $e) {
            if ($template->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove template with ID %1. Error: %2',
                        [$template->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove template. Error: %1', $e->getMessage()));
        }
    }
}
