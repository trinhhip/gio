<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Extensions\Template\Stores;

use Amasty\AltTagGenerator\Model\Template;
use Amasty\AltTagGenerator\Model\Template\Store\Query\GetByTemplateIdInterface;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

class ReadHandler implements ExtensionInterface
{
    /**
     * @var GetByTemplateIdInterface
     */
    private $getByTemplateId;

    public function __construct(GetByTemplateIdInterface $getByTemplateId)
    {
        $this->getByTemplateId = $getByTemplateId;
    }

    /**
     * @param Template|object $entity
     * @param array $arguments
     * @return Template|bool|object|void
     */
    public function execute($entity, $arguments = [])
    {
        $entity->getExtensionAttributes()->setStores(
            $this->getByTemplateId->execute((int) $entity->getId())
        );

        return $entity;
    }
}
