<?php


namespace Omnyfy\Mcm\Helper;


use Amasty\Orderattr\Model\ConfigProvider;
use Amasty\Orderattr\Model\Entity\EntityResolver;
use Amasty\Orderattr\Model\Value\Metadata\FormFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class OrderAttributeHelper extends AbstractHelper
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;
    /**
     * @var FormFactory
     */
    private $metadataFormFactory;
    /**
     * @var EntityResolver
     */
    private $entityResolver;

    public function __construct(
        ConfigProvider $configProvider,
        FormFactory $metadataFormFactory,
        EntityResolver $entityResolver,
        Context $context
    )
    {
        $this->configProvider = $configProvider;
        $this->metadataFormFactory = $metadataFormFactory;
        $this->entityResolver = $entityResolver;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function isPrintAttributesAllowed()
    {
        return (bool)$this->configProvider->isIncludeToInvoicePdf();
    }

    public function getOrderAttributeData($order){
        $orderAttributesData = [];
        if($this->isPrintAttributesAllowed()){
            $entity = $this->entityResolver->getEntityByOrder($order);
            $form = $this->createEntityForm($entity, $order->getStore());
            $outputData = $form->outputData(\Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_HTML);
            foreach ($outputData as $attributeCode => $data) {
                if (!empty($data)) {
                    $orderAttributesData[] = [
                        'label' => $form->getAttribute($attributeCode)->getDefaultFrontendLabel(),
                        'value' => $this->resolveValue($data)
                    ];
                }
            }
        }
        return $orderAttributesData;
    }

    /**
     * Return Checkout Form instance
     *
     * @param \Amasty\Orderattr\Model\Entity\EntityData $entity
     * @param \Magento\Store\Model\Store                $store
     *
     * @return \Amasty\Orderattr\Model\Value\Metadata\Form
     */
    protected function createEntityForm($entity, $store)
    {
        /** @var \Amasty\Orderattr\Model\Value\Metadata\Form $formProcessor */
        $formProcessor = $this->metadataFormFactory->create();
        $formProcessor->setFormCode('adminhtml_order_print')
            ->setEntity($entity)
            ->setStore($store);

        return $formProcessor;
    }

    /**
     * @param array|string $attributeValue
     * @return string
     */
    private function resolveValue($attributeValue)
    {
        if (is_array($attributeValue) && isset($attributeValue['name'])) {
            $attributeValue = $attributeValue['name'];
        }

        return $attributeValue;
    }
}
