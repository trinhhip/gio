<?php
namespace Omnyfy\Webhook\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $version = $context->getVersion();
        if (version_compare($version, '1.0.1', '<')) {
            $conn = $setup->getConnection();
            $webhookTypeTable = $conn->getTableName('omnyfy_webhook_type');
            $typeList = [
                'order.created',
                'order.updated',
                'cart.added',
                'cart.updated',
                'cart.deleted',
                'customer.login'
            ];
            foreach ($typeList as $type) {
                $conn->insert($webhookTypeTable, ['type' => $type]);
            }
        }

        if (version_compare($version, '1.0.5', '<')) {
            $conn = $setup->getConnection();
            $webhookTypeTable = $conn->getTableName('omnyfy_webhook_type');
            $typeList = [
                'product.updated',
                'product.inventory.updated',
                'shipment.updated'
            ];
            foreach ($typeList as $type) {
                $conn->insert($webhookTypeTable, ['type' => $type]);
            }
        }

        $setup->endSetup();
    }
}
