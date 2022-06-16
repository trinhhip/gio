<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Template\Command;

use Amasty\AltTagGenerator\Api\Data\TemplateInterface;
use Magento\Framework\Exception\CouldNotDeleteException;

interface DeleteTemplateInterface
{
    /**
     * @param TemplateInterface $template
     * @return void
     * @throws CouldNotDeleteException
     */
    public function execute(TemplateInterface $template): void;
}
