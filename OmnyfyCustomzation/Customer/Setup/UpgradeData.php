<?php

namespace OmnyfyCustomzation\Customer\Setup;

use Exception;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use OmnyfyCustomzation\BuyerApproval\Model\Config\Source\AttributeOptions;

/**
 * Class UpgradeData
 *
 * @package OmnyfyCustomzation\Customer\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var Config
     */
    protected $eavConfig;

    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        IndexerRegistry $indexerRegistry,
        Config $eavConfig,
        AttributeSetFactory $attributeSetFactory
    )
    {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->indexerRegistry = $indexerRegistry;
        $this->eavConfig = $eavConfig;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws Exception
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
            $attributeSetId = $customerEntity->getDefaultAttributeSetId();

            $attributeSet = $this->attributeSetFactory->create();
            $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
            $attributeCodes = ['business_name', 'business_url', 'designation', 'business_location', 'business_type', 'country_code', 'phone_number'];

            foreach ($attributeCodes as $code) {
                $customerSetup->removeAttribute(Customer::ENTITY, $code);
            }
            /** @var CustomerSetup $customerSetup */
            $customerSetup->addAttribute(Customer::ENTITY, 'business_name', [
                'type' => 'text',
                'length' => 255,
                'label' => 'Business Name',
                'input' => 'text',
                'source' => '',
                'required' => false,
                'default' => '',
                'visible' => true,
                'user_defined' => true,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'sort_order' => 210,
                'position' => 999,
                'system' => false,
            ]);

            $customerSetup->addAttribute(Customer::ENTITY, 'business_url', [
                'type' => 'text',
                'length' => 255,
                'label' => 'Business URL',
                'input' => 'text',
                'source' => '',
                'required' => false,
                'default' => '',
                'visible' => true,
                'user_defined' => true,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'sort_order' => 210,
                'position' => 999,
                'system' => false,
            ]);

            $customerSetup->addAttribute(Customer::ENTITY, 'designation', [
                'type' => 'text',
                'length' => 255,
                'label' => 'Your Designation',
                'input' => 'text',
                'source' => '',
                'required' => false,
                'default' => '',
                'visible' => true,
                'user_defined' => true,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'sort_order' => 210,
                'position' => 999,
                'system' => false,
            ]);

            $customerSetup->addAttribute(Customer::ENTITY, 'business_location', [
                'type' => 'text',
                'length' => 255,
                'label' => 'Business Location',
                'input' => 'select',
                'source' => \Magento\Customer\Model\ResourceModel\Address\Attribute\Source\Country::class,
                'required' => false,
                'default' => '',
                'visible' => true,
                'user_defined' => true,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'sort_order' => 210,
                'position' => 999,
                'system' => false,
            ]);

            $customerSetup->addAttribute(Customer::ENTITY, 'country_code', [
                'type' => 'text',
                'length' => 255,
                'label' => 'Country/Region Code',
                'input' => 'select',
                'source' => \OmnyfyCustomzation\Customer\Model\Config\Source\CountryCode::class,
                'required' => false,
                'default' => '',
                'visible' => true,
                'user_defined' => true,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'sort_order' => 210,
                'position' => 999,
                'system' => false,
            ]);

            $customerSetup->addAttribute(Customer::ENTITY, 'phone_number', [
                'type' => 'text',
                'length' => 20,
                'label' => 'Phone Number',
                'input' => 'text',
                'source' => '',
                'required' => false,
                'default' => '',
                'visible' => true,
                'user_defined' => true,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'sort_order' => 210,
                'position' => 999,
                'system' => false,
            ]);

            $customerSetup->addAttribute(Customer::ENTITY, 'business_type', [
                'type' => 'text',
                'length' => 255,
                'label' => 'Business Type',
                'input' => 'select',
                'source' => \OmnyfyCustomzation\Customer\Model\Config\Source\BusinessType::class,
                'required' => true,
                'default' => '',
                'visible' => true,
                'user_defined' => true,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'sort_order' => 210,
                'position' => 999,
                'system' => false,
            ]);

            foreach ($attributeCodes as $code) {
                $attribute = $customerSetup->getEavConfig()
                    ->getAttribute(Customer::ENTITY, $code)
                    ->addData([
                        'attribute_set_id' => $attributeSetId,
                        'attribute_group_id' => $attributeGroupId,
                        'used_in_forms' => ['customer_account_edit', 'customer_account_create', 'adminhtml_customer'],
                    ]);
                $attribute->save();
            }
//            $attribute = $customerSetup->getEavConfig()
//                ->getAttribute(Customer::ENTITY, self::IS_APPROVED)
//                ->addData([
//                    'attribute_set_id' => $attributeSetId,
//                    'attribute_group_id' => $attributeGroupId,
//                    'used_in_forms' => ['customer_account_edit', 'customer_account_create','adminhtml_customer'],
//                ]);

            $setup->endSetup();
        }
    }

    private function getBusinessTypes()
    {
        $options = [];
        $data = [
            'Art Gallery / Art Consultancy',
            'Co-Working / Co-Living Space',
            'Food & Beverage',
            'Hotel / Resort / Spa',
            'Interior Design / Architecture',
            'Real Estate Developer',
            'Retailer',
            'Sourcing Agent',
            'Other',
        ];
        foreach ($data as $option) {
            $options[] = [
                'value' => $option,
                'label' => $option
            ];
        }
        return $options;
    }
}
