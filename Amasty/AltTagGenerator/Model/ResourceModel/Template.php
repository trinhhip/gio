<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\ResourceModel;

use Amasty\AltTagGenerator\Api\Data\TemplateInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Template extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(TemplateInterface::MAIN_TABLE, TemplateInterface::ID);
    }
}
