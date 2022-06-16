<?php

declare(strict_types=1);

namespace Amasty\RegenerateUrlRewrites\Model\GenerateProcess\ResourceModel;

use Amasty\RegenerateUrlRewrites\Model\GenerateProcess\GenerateProcess;
use Amasty\RegenerateUrlRewrites\Model\GenerateProcess\ResourceModel\GenerateProcess as GenerateProcessResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @return void
     */
    public function _construct(): void
    {
        $this->_init(GenerateProcess::class, GenerateProcessResource::class);
    }
}
