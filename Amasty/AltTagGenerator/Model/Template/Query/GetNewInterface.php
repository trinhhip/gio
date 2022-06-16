<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Template\Query;

use Amasty\AltTagGenerator\Api\Data\TemplateInterface;

interface GetNewInterface
{
    public function execute(): TemplateInterface;
}
