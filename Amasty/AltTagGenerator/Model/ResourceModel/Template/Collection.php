<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\ResourceModel\Template;

use Amasty\AltTagGenerator\Api\Data\TemplateInterface;
use Amasty\AltTagGenerator\Model\ResourceModel\Template as TemplateResource;
use Amasty\AltTagGenerator\Model\Template as TemplateModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = TemplateInterface::ID;

    public function _construct()
    {
        $this->_init(TemplateModel::class, TemplateResource::class);
    }
}
