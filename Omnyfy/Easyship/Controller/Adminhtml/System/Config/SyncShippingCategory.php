<?php
namespace Omnyfy\Easyship\Controller\Adminhtml\System\Config;

class SyncShippingCategory extends \Magento\Backend\App\Action
{
    protected $resultJsonFactory;
    protected $eavSetupFactory;
    protected $eavConfig;
    protected $attribute;
    protected $apiHelper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $attribute,
        \Omnyfy\Easyship\Helper\Api $apiHelper
    ){
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->attribute = $attribute;
        $this->apiHelper = $apiHelper;
    }

    public function execute()
    {
        $attributeId = $this->attribute->getIdByCode('catalog_product', 'easyship_shipping_category');
        $attribute = $this->eavConfig->getAttribute('catalog_product', 'easyship_shipping_category');
        $options = $attribute->getSource()->getAllOptions();
        $optionsExists = array();
        foreach($options as $option) {
            $optionsExists[] = $option['label'];
        }
        $newOption['attribute_id'] = $attributeId;
        $success = false;

        if ($this->apiHelper->getShippingCategory()) {
            $cats = json_decode($this->apiHelper->getShippingCategory(), true);
            if (array_key_exists('error', $cats)) {
                $this->messageManager->addError($cats['error']);
            }else{
                foreach ($cats['categories'] as $cat) {
                    if (!in_array($cat['slug'], $optionsExists)) {
                        $newOption['value'][$cat['slug']][0] = $cat['slug'];
                    }
                }
                if (array_key_exists('value', $newOption)) {
                    $eavSetup = $this->eavSetupFactory->create();
                    $eavSetup->addAttributeOption($newOption);
                    $this->messageManager->addSuccess('Shipping Category has been updated.');
                }else{
                    $this->messageManager->addSuccess('Shipping Category is already up to date.');
                }
                $success = true;
            }
        }
        $result = $this->resultJsonFactory->create();
        return $result->setData(['success' => $success]);
    }
}
