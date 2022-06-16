<?php
declare(strict_types=1);

namespace Amasty\AdminActionsLog\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Operation\UpdateSchemaTo200
     */
    private $updateSchemaTo200;

    /**
     * @var Operation\UpdateSchemaTo201
     */
    private $updateSchemaTo201;

    public function __construct(
        Operation\UpdateSchemaTo200 $updateSchemaTo200,
        Operation\UpdateSchemaTo201 $updateSchemaTo201
    ) {
        $this->updateSchemaTo200 = $updateSchemaTo200;
        $this->updateSchemaTo201 = $updateSchemaTo201;
    }

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $setup->startSetup();

        if ($context->getVersion() && version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->updateSchemaTo200->execute($setup);
        }

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->updateSchemaTo201->execute($setup);
        }

        $setup->endSetup();
    }
}
