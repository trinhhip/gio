<?php
/**
 * Project: Multi Vendor
 * User: jing
 * Date: 2019-04-26
 * Time: 17:21
 */
namespace Omnyfy\Vendor\Model\Indexer\Vendor\Flat;

/**
 * Class TableData
 */
class TableData implements TableDataInterface
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(\Magento\Framework\App\ResourceConnection $resource)
    {
        $this->_resource = $resource;
    }

    /**
     * Move data from temporary tables to flat
     *
     * @param string $flatTable
     * @param string $flatDropName
     * @param string $temporaryFlatTableName
     * @return void
     */
    public function move($flatTable, $flatDropName, $temporaryFlatTableName)
    {
        $connection = $this->_resource->getConnection();
        $renameTables = [];

        if ($connection->isTableExists($flatTable)) {
            $renameTables[] = ['oldName' => $flatTable, 'newName' => $flatDropName];
        }
        $renameTables[] = ['oldName' => $temporaryFlatTableName, 'newName' => $flatTable];

        $connection->dropTable($flatDropName);
        $connection->renameTablesBatch($renameTables);
        $connection->dropTable($flatDropName);
    }
}
 