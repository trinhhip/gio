<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Template\Store\Query;

interface GetByTemplateIdInterface
{
    public function execute(int $templateId): array;
}
