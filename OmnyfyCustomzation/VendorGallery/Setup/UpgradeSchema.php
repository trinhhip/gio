<?php


namespace OmnyfyCustomzation\VendorGallery\Setup;


use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

class UpgradeSchema implements  UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $vendorGalleryTable = 'omnyfy_vendor_gallery_album';

        $version = $context->getVersion();
        $connection = $setup->getConnection();

        if (version_compare($version, '1.0.2') < 0) {
            $connection->changeColumn(
                $setup->getTable($vendorGalleryTable),
                'description',
                'description',
                [
                    'type'     => Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment'  => 'Gallery Description'
                ]
            );
        }

        $installer->endSetup();
    }
}
