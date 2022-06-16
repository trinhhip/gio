<?php


namespace OmnyfyCustomzation\SalesSequence\Model\ResourceModel\Order;


use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\SalesSequence\Model\Manager;
use Magento\Sales\Model\ResourceModel\Attribute;
use OmnyfyCustomzation\SalesSequence\Helper\Data;
use Magento\Framework\App\ResourceConnection;
use WeltPixel\GoogleTagManager\lib\Google\Exception;

class Invoice extends \Magento\Sales\Model\ResourceModel\Order\Invoice
{
    /**
     * @var Data
     */
    protected $helperData;
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $resource;

    public function __construct(
        Context $context,
        Snapshot $entitySnapshot, RelationComposite $entityRelationComposite,
        Attribute $attribute,
        Manager $sequenceManager,
        Data $helperData,
        $connectionName = null
    )
    {
        $this->helperData = $helperData;
        parent::__construct(
            $context,
            $entitySnapshot,
            $entityRelationComposite,
            $attribute,
            $sequenceManager,
            $connectionName
        );
    }

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Magento\Sales\Model\Order\Invoice $object */
        if ($object->getIncrementId() == null) {
            $lastSequence = $this->getLastSequence($object->getStoreId());
            $incrementId = $this->helperData->getIncrementId($object->getShippingAddress(), ($lastSequence + 1));
            $object->setIncrementId($incrementId);
        }
        return parent::_beforeSave($object);
    }

    protected function getLastSequence($storeId)
    {
        $connection = $this->getConnection();
        $sequenceTable = $connection->getTableName('sequence_invoice_' . $storeId);
        $sql = $connection->select()->from(
            ['iv' => $sequenceTable],
            ['sequence_value' => 'iv.sequence_value']
        )->order('sequence_value DESC');
        $lastSequence = (int)$connection->fetchOne($sql) + 1;
        $connection->insert($sequenceTable, ['sequence_value' => $lastSequence]);
        return $lastSequence;
    }
}