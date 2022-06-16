<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Operation\UpgradeTo130
     */
    private $upgradeTo130;

    /**
     * @var Operation\UpgradeTo150
     */
    private $upgradeTo150;

    /**
     * @var Operation\CreateConsentScopeTable
     */
    private $createConsentScopeTable;

    /**
     * @var Operation\CreateConsentsTable
     */
    private $createConsentsTable;

    /**
     * @var Operation\UpgradeTo200
     */
    private $upgradeTo200;

    /**
     * @var Operation\UpgradeTo230
     */
    private $upgradeTo230;

    /**
     * @var Operation\CreateVisitorConsentLogTable
     */
    private $visitorConsentLogTable;

    /**
     * @var Operation\UpgradeTo250
     */
    private $upgradeTo250;

    public function __construct(
        Operation\UpgradeTo130 $upgradeTo130,
        Operation\UpgradeTo150 $upgradeTo150,
        Operation\CreateConsentScopeTable $createConsentScopeTable,
        Operation\CreateConsentsTable $createConsentsTable,
        Operation\UpgradeTo200 $upgradeTo200,
        Operation\UpgradeTo230 $upgradeTo230,
        Operation\CreateVisitorConsentLogTable $visitorConsentLogTable,
        Operation\UpgradeTo250 $upgradeTo250
    ) {
        $this->upgradeTo130 = $upgradeTo130;
        $this->upgradeTo150 = $upgradeTo150;
        $this->createConsentScopeTable = $createConsentScopeTable;
        $this->createConsentsTable = $createConsentsTable;
        $this->upgradeTo200 = $upgradeTo200;
        $this->upgradeTo230 = $upgradeTo230;
        $this->visitorConsentLogTable = $visitorConsentLogTable;
        $this->upgradeTo250 = $upgradeTo250;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (!$context->getVersion() || version_compare($context->getVersion(), '1.3.0', '<')) {
            $this->upgradeTo130->execute($setup);
        }

        if (!$context->getVersion() || version_compare($context->getVersion(), '1.5.0', '<')) {
            $this->upgradeTo150->execute($setup);
        }

        if (!$context->getVersion() || version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->createConsentsTable->execute($setup);
            $this->createConsentScopeTable->execute($setup);
            $this->upgradeTo200->execute($setup);
        }

        if (!$context->getVersion() || version_compare($context->getVersion(), '2.3.0', '<')) {
            $this->upgradeTo230->execute($setup);
        }

        if (!$context->getVersion() || version_compare($context->getVersion(), '2.4.0', '<')) {
            $this->visitorConsentLogTable->execute($setup);
        }

        if (!$context->getVersion() || version_compare($context->getVersion(), '2.5.0', '<')) {
            $this->upgradeTo250->execute($setup);
        }

        $setup->endSetup();
    }
}
