<?php
namespace OmnyfyCustomzation\BuyerApproval\Setup;

use Exception;
use Magento\Cms\Block\Adminhtml\Page\Edit\GenericButton;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use OmnyfyCustomzation\BuyerApproval\Model\Config\Source\AttributeOptions;
use Zend_Validate_Exception;

/**
 * Class InstallData
 *
 * @package OmnyfyCustomzation\BuyerApproval\Setup
 */
class InstallData implements InstallDataInterface
{
    const IS_APPROVED = 'is_approved';

    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * InstallData constructor.
     *
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     * @param IndexerRegistry $indexerRegistry
     * @param PageFactory $pageFactory
     * @param Config $eavConfig
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory,
        IndexerRegistry $indexerRegistry,
        PageFactory $pageFactory,
        Config $eavConfig
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->indexerRegistry = $indexerRegistry;
        $this->_pageFactory = $pageFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->removeAttribute(Customer::ENTITY, self::IS_APPROVED);
        /** @var CustomerSetup $customerSetup */
        $customerSetup->addAttribute(Customer::ENTITY, self::IS_APPROVED, [
            'type' => 'varchar',
            'label' => 'Approval Status',
            'input' => 'select',
            'source' => AttributeOptions::class,
            'required' => false,
            'default' => AttributeOptions::NEW_STATUS,
            'visible' => true,
            'user_defined' => true,
            'is_used_in_grid' => true,
            'is_visible_in_grid' => true,
            'sort_order' => 210,
            'position' => 999,
            'system' => false,
        ]);

        $attribute = $customerSetup->getEavConfig()
            ->getAttribute(Customer::ENTITY, self::IS_APPROVED)
            ->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['checkout_register', 'adminhtml_checkout'],
            ]);
        $attribute->save();

        $this->initApprovedForAllCustomer($setup, $attribute->getId());

        $indexer = $this->indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID);
        $indexer->reindexAll();
        $this->eavConfig->clear();

        $setup->endSetup();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param $attributeId
     */
    private function initApprovedForAllCustomer($setup, $attributeId)
    {
        $customerEntityTable = $setup->getTable('customer_entity');
        $customerEntityVarcharTable = $setup->getTable('customer_entity_varchar');
        $data = [];

        $select = $setup->getConnection()->select()->from($customerEntityTable, ['entity_id']);
        $customerIds = $setup->getConnection()->fetchCol($select);
        foreach ($customerIds as $id) {
            $data[] = [
                'attribute_id' => $attributeId,
                'entity_id' => $id,
                'value' => AttributeOptions::APPROVED
            ];

            if (sizeof($data) >= 1000) {
                $setup->getConnection()->insertMultiple($customerEntityVarcharTable, $data);
                $data = [];
            }
        }

        if (!empty($data)) {
            $setup->getConnection()->insertMultiple($customerEntityVarcharTable, $data);
        }
    }

    /**
     * @param $identifier
     *
     * @return $this
     * @throws Exception
     */
    public function deleteCmsExist($identifier)
    {
        /** @var GenericButton $cmsFactory */
        $cmsFactory = $this->_pageFactory->create()->load($identifier, 'identifier');
        if ($cmsFactory->getPageId()) {
            /** @var Page $cmsFactory */
            $cmsFactory->load($cmsFactory->getPageId())->delete();
            $cmsFactory->save();
        }

        return $this;
    }
}
