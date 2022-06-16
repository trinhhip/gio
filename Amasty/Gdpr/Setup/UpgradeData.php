<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Setup;

use Amasty\Gdpr\Model\PolicyFactory;
use Amasty\Gdpr\Model\SampleData;
use Amasty\Gdpr\Setup\Operation\MovePrivacyCheckboxConfigToCheckboxes;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var ConfigInterface
     */
    private $resourceConfig;

    /**
     * @var SampleData\Policy\Installer
     */
    private $policySampleInstaller;

    /**
     * @var MovePrivacyCheckboxConfigToCheckboxes
     */
    private $movePrivacyCheckboxConfigToCheckboxes;

    public function __construct(
        ConfigInterface $resourceConfig,
        SampleData\Policy\Installer $policySampleInstaller,
        MovePrivacyCheckboxConfigToCheckboxes $movePrivacyCheckboxConfigToCheckboxes
    ) {
        $this->resourceConfig = $resourceConfig;
        $this->policySampleInstaller = $policySampleInstaller;
        $this->movePrivacyCheckboxConfigToCheckboxes = $movePrivacyCheckboxConfigToCheckboxes;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if ($context->getVersion() && version_compare($context->getVersion(), '1.1.5', '<')) {
            $path = 'amasty_gdpr/deletion_notification/';
            $fields = ['sender', 'reply_to', 'template'];
            $connection = $this->resourceConfig->getConnection();

            foreach ($fields as $key => $fieldId) {
                $data = ['path' => $path . 'deny_' . $fieldId];
                $whereCondition = ['path = ?' => $path . $fieldId];
                $connection->update($this->resourceConfig->getMainTable(), $data, $whereCondition);
            }
        }

        if (!$context->getVersion() || version_compare($context->getVersion(), '1.2.0', '<')) {
            $this->policySampleInstaller->install();
        }

        if ($context->getVersion() && version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->movePrivacyCheckboxConfigToCheckboxes->execute($setup);
        }
    }
}
