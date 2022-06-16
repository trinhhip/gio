<?php
namespace Omnyfy\ManualPayout\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Omnyfy\Mcm\Model\PayoutType;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $conn = $installer->getConnection();

        $table = $conn->getTableName('omnyfy_mcm_payout_type');
        if ($conn->isTableExists($table)) {
            $conn->insert($table, ['payout_type' => PayoutType::DEFAULT_TYPE]);
            $conn->insert($table, ['payout_type' => PayoutType::STRIPE_TYPE]);
        }

        $installer->endSetup();
    }
}
