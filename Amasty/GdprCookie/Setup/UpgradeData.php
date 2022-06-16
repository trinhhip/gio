<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Setup;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var Operation\UninstallCmsPagesData
     */
    private $uninstallCmsPagesData;

    /**
     * @var Operation\UpdateDataTo220
     */
    private $updateDataTo220;

    /**
     * @var Operation\UpdateDataTo240
     */
    private $updateDataTo240;

    /**
     * @var Operation\UpdateDataTo260
     */
    private $updateDataTo260;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter,
        Operation\UninstallCmsPagesData $uninstallCmsPagesData,
        Operation\UpdateDataTo220 $updateDataTo220,
        Operation\UpdateDataTo240 $updateDataTo240,
        Operation\UpdateDataTo260 $updadeDataTo260
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->uninstallCmsPagesData = $uninstallCmsPagesData;
        $this->updateDataTo220 = $updateDataTo220;
        $this->updateDataTo240 = $updateDataTo240;
        $this->updateDataTo260 = $updadeDataTo260;
    }

    /**
     * Set new setting value based on old setting value
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if ($context->getVersion() && version_compare($context->getVersion(), '1.0.5', '<')) {
            $notificationWebsiteInteraction = $this->scopeConfig
                ->getValue('amasty_gdprcookie/cookie_policy/notification_website_interaction');
            $confirmationWebsiteInteraction = $this->scopeConfig
                ->getValue('amasty_gdprcookie/cookie_policy/confirmation_website_interaction');

            if ($notificationWebsiteInteraction || $confirmationWebsiteInteraction) {
                $this->configWriter->save('amasty_gdprcookie/cookie_policy/website_interaction', 1);
            }

            $this->configWriter->delete('amasty_gdprcookie/cookie_policy/notification_website_interaction');
            $this->configWriter->delete('amasty_gdprcookie/cookie_policy/confirmation_website_interaction');
        }

        if (!$context->getVersion() || version_compare($context->getVersion(), '2.1.0', '<')) {
            $this->uninstallCmsPagesData->execute();
        }

        if ($context->getVersion() && version_compare($context->getVersion(), '2.2.0', '<')) {
            $this->updateDataTo220->upgrade();
        }

        if (!$context->getVersion() || version_compare($context->getVersion(), '2.4.0', '<')) {
            $this->updateDataTo240->upgrade();
        }

        if (!$context->getVersion() || version_compare($context->getVersion(), '2.6.0', '<')) {
            $this->updateDataTo260->upgrade();
        }

        $setup->endSetup();
    }
}
