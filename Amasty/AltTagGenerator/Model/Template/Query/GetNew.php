<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Template\Query;

use Amasty\AltTagGenerator\Api\Data\TemplateInterface;
use Amasty\AltTagGenerator\Api\Data\TemplateInterfaceFactory;

class GetNew implements GetNewInterface
{
    /**
     * @var TemplateInterfaceFactory
     */
    private $templateFactory;

    public function __construct(TemplateInterfaceFactory $templateFactory)
    {
        $this->templateFactory = $templateFactory;
    }

    public function execute(): TemplateInterface
    {
        return $this->templateFactory->create();
    }
}
