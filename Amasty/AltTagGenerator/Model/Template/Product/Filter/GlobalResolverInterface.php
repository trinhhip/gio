<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Template\Product\Filter;

interface GlobalResolverInterface
{
    public function execute(): string;
}
