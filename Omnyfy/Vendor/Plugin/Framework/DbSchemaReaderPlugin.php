<?php
namespace Omnyfy\Vendor\Plugin\Framework;

use Magento\Framework\Setup\Declaration\Schema\Db\DefinitionAggregator;

/**
 * Rename to correct table when creating referrence while migrate 2.2 to 2.4
 */
class DbSchemaReaderPlugin
{
    /**
     * @var DefinitionAggregator
     */
    protected $definitionAggregator;

    public function __construct(
        DefinitionAggregator $definitionAggregator
    ) {
        $this->definitionAggregator = $definitionAggregator;
    }

    public function aroundReadReferences(
        $subject,
        callable $proceed,
        $tableName,
        $resource
    ) {
        $createTableSql = $subject->getCreateTableSql($tableName, $resource);
        $createTableSql['type'] = 'reference';
        $createTableSql['Create Table'] = str_replace('`omnyfy_vendor_entity`', '`omnyfy_vendor_vendor_entity`', $createTableSql['Create Table']);
        $createTableSql['Create Table'] = str_replace('`omnyfy__vendor_vendor_entity`', '`omnyfy_vendor_vendor_entity`', $createTableSql['Create Table']);
        return $this->definitionAggregator->fromDefinition($createTableSql);
    }
}