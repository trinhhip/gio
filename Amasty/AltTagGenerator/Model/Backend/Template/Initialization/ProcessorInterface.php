<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Backend\Template\Initialization;

use Amasty\AltTagGenerator\Api\Data\TemplateInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;

interface ProcessorInterface
{
    /**
     * @param TemplateInterface $template
     * @param array $inputTemplateData
     * @return void
     * @throws LocalizedException
     * @throws InputException
     */
    public function execute(TemplateInterface $template, array $inputTemplateData): void;
}
