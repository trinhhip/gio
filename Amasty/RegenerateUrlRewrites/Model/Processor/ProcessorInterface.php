<?php

declare(strict_types=1);

namespace Amasty\RegenerateUrlRewrites\Model\Processor;

use Generator;

interface ProcessorInterface
{
    /**
     * @param int $storeId
     * @param array $entityIds
     * @return Generator
     */
    public function process(int $storeId, array $entityIds): Generator;
}
