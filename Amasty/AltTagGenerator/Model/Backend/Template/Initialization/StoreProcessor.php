<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Backend\Template\Initialization;

use Amasty\AltTagGenerator\Api\Data\TemplateInterface;
use Amasty\AltTagGenerator\Model\Template;
use Amasty\AltTagGenerator\Ui\DataProvider\Template\Form\Modifier\AddStores;

class StoreProcessor implements ProcessorInterface
{
    /**
     * @param TemplateInterface|Template $template
     * @param array $inputTemplateData
     * @return void
     */
    public function execute(TemplateInterface $template, array $inputTemplateData): void
    {
        $template->getExtensionAttributes()->setStores($inputTemplateData[AddStores::STORES_FIELD] ?? []);
    }
}
