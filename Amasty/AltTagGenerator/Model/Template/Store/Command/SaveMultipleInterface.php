<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Template\Store\Command;

use Zend_Db_Exception;

interface SaveMultipleInterface
{
    /**
     * @param int $templateId
     * @param array $stores
     * @return bool Return true if database changed.
     * @throws Zend_Db_Exception
     */
    public function execute(int $templateId, array $stores): bool;
}
