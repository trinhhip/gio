<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Extensions\Template\Stores;

use Amasty\AltTagGenerator\Api\Data\TemplateInterface;
use Amasty\AltTagGenerator\Model\Indexer\Template\TemplateProcessor;
use Amasty\AltTagGenerator\Model\Template;
use Amasty\AltTagGenerator\Model\Template\Store\Command\SaveMultipleInterface;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Zend_Db_Exception;

class SaveHandler implements ExtensionInterface
{
    /**
     * @var SaveMultipleInterface
     */
    private $saveMultiple;

    /**
     * @var TemplateProcessor
     */
    private $templateProcessor;

    public function __construct(
        SaveMultipleInterface $saveMultiple,
        TemplateProcessor $templateProcessor
    ) {
        $this->saveMultiple = $saveMultiple;
        $this->templateProcessor = $templateProcessor;
    }

    /**
     * @param Template|object $entity
     * @param array $arguments
     * @return Template|bool|object|void
     * @throws Zend_Db_Exception
     */
    public function execute($entity, $arguments = [])
    {
        $extensionAttributes = $entity->getExtensionAttributes();
        $stores = $extensionAttributes->getStores();

        if ($stores !== null) {
            $isStoresModified = $this->saveMultiple->execute((int) $entity->getId(), $stores);
            if ($isStoresModified && !$entity->dataHasChangedFor(TemplateInterface::CONDITIONS_SERIALIZED)) {
                $this->templateProcessor->reindexRow((int) $entity->getId());
            }
        }

        return $entity;
    }
}
