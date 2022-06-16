<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Template\Store\Command;

use Amasty\AltTagGenerator\Model\ResourceModel\Template\Store\DeleteByTemplateId;
use Amasty\AltTagGenerator\Model\ResourceModel\Template\Store\InsertMultiple;
use Amasty\AltTagGenerator\Model\ResourceModel\Template\Store\LoadByTemplateId;
use Amasty\AltTagGenerator\Model\ResourceModel\Template\Store\Table as StoreTable;
use Magento\Framework\App\ResourceConnection;
use Zend_Db_Exception;

class SaveMultiple implements SaveMultipleInterface
{

    /**
     * @var InsertMultiple
     */
    private $insertMultiple;

    /**
     * @var DeleteByTemplateId
     */
    private $deleteByTemplateId;

    /**
     * @var LoadByTemplateId
     */
    private $loadByTemplateId;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        InsertMultiple $insertMultiple,
        DeleteByTemplateId $deleteByTemplateId,
        LoadByTemplateId $loadByTemplateId,
        ResourceConnection $resourceConnection
    ) {
        $this->insertMultiple = $insertMultiple;
        $this->deleteByTemplateId = $deleteByTemplateId;
        $this->loadByTemplateId = $loadByTemplateId;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param int $templateId
     * @param array $stores
     * @return bool
     * @throws Zend_Db_Exception
     */
    public function execute(int $templateId, array $stores): bool
    {
        $result = false;

        $origStores = $this->loadByTemplateId->execute($templateId);
        if (array_diff($origStores, $stores) || array_diff($stores, $origStores)) {
            $data = [];
            foreach ($stores as $storeId) {
                $data[] = [
                    StoreTable::TEMPLATE_COLUMN => $templateId,
                    StoreTable::STORE_COLUMN    => (int)$storeId
                ];
            }

            if ($data) {
                $this->resourceConnection->getConnection()->beginTransaction();
                try {
                    $this->deleteByTemplateId->execute($templateId);
                    $this->insertMultiple->execute($data);
                    $this->resourceConnection->getConnection()->commit();
                    $result = true;
                } catch (Zend_Db_Exception $e) {
                    $this->resourceConnection->getConnection()->rollBack();
                    throw new Zend_Db_Exception($e->getMessage(), $e->getCode(), $e);
                }
            }
        }

        return $result;
    }
}
