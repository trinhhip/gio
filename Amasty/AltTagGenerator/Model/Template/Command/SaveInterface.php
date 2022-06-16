<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Template\Command;

use Amasty\AltTagGenerator\Api\Data\TemplateInterface;
use Magento\Framework\Exception\CouldNotSaveException;

interface SaveInterface
{
    /**
     * @param TemplateInterface $template
     * @return void
     * @throws CouldNotSaveException
     */
    public function execute(TemplateInterface $template): void;
}
