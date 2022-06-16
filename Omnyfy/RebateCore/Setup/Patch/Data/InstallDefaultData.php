<?php


namespace Omnyfy\RebateCore\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Email\Model\TemplateFactory;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;

/**
 * Class AddColorProductAttribute
 *
 * @package Vendor\Module\Setup\Patch\Data
 */
class InstallDefaultData implements DataPatchInterface
{

    const EMAIL_TEMPLATE_CODE = 'orig_template_code';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var TemplateFactory
     */
    private $emailTemplate;
    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var TypeListInterface
     */
    private $typeList;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        TemplateFactory $emailTemplate,
        WriterInterface $configWriter,
        State $appState,
        TypeListInterface $typeList
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->emailTemplate = $emailTemplate;
        $this->configWriter = $configWriter;
        $this->appState = $appState;
        $this->typeList = $typeList;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $adminEmailTemplate = $this->emailTemplate->create()->load('omnyfy_rebate_core_admin_email_template', self::EMAIL_TEMPLATE_CODE);
        if ($adminEmailTemplate) {
            $adminEmailTemplate->delete();
        }
        $moEmailTemplate = $this->emailTemplate->create()->load('omnyfy_rebate_core_mo_email_template', self::EMAIL_TEMPLATE_CODE);
        if ($moEmailTemplate) {
            $moEmailTemplate->delete();
        }
        $invoiceEmailTemplate = $this->emailTemplate->create()->load('omnyfy_rebate_core_invoice_email_template', self::EMAIL_TEMPLATE_CODE);
        if ($invoiceEmailTemplate) {
            $invoiceEmailTemplate->delete();
        }

        $this->appState->emulateAreaCode(Area::AREA_FRONTEND, [$this, 'saveAndSetEmails']);
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return void
     */
    public function saveAndSetEmails()
    {
        $this->saveAndSetEmail(
            'Change to Rebate Email Template Vendor Email',
            'omnyfy_rebate_core_admin_email_template',
            Area::AREA_FRONTEND
        );
        $this->saveAndSetEmail(
            'Change to Rebate Email Template MO Email',
            'omnyfy_rebate_core_mo_email_template',
            Area::AREA_FRONTEND
        );
        $this->saveAndSetEmail(
            'Invoice Rebate Email Template Email',
            'omnyfy_rebate_core_invoice_email_template',
            Area::AREA_FRONTEND
        );
        $this->typeList->invalidate(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
    }

    /**
     * @param string $code
     * @param string $originalCode
     * @param string $configPath
     * @param string $area
     */
    private function saveAndSetEmail($code, $originalCode, $area = Area::AREA_FRONTEND)
    {
        try {
            /** @var \Magento\Email\Model\Template $mailTemplate */
            $mailTemplate = $this->emailTemplate->create();

            $mailTemplate->setDesignConfig(
                ['area' => $area, 'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID]
            )->loadDefault(
                $originalCode
            )->setTemplateCode(
                $code
            )->setOrigTemplateCode(
                $originalCode
            )->setId(
                null
            )->save();
            $this->configWriter->save("omnymart_adhocconsignment/general/template", $mailTemplate->getId());
        } catch (\Exception $e) {
            null;
        }
    }
}