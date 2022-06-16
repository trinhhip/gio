<?php
namespace Omnyfy\Webhook\Observer;
use Magento\Framework\Event\ObserverInterface;
use Omnyfy\Webhook\Model\WebhookEventSchedule;

class WebhookProductImportUpdated implements ObserverInterface
{
    protected $storeManager;
    protected $productFactory;
    protected $configurableModel;
    protected $helper;
    protected $webhookHelper;
    protected $scheduleFactory;
    protected $logger;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableModel,
        \Omnyfy\Webhook\Helper\Data $helper,
        \Omnyfy\Webhook\Helper\WebhookHelper $webhookHelper,
        \Omnyfy\Webhook\Model\WebhookEventScheduleFactory $scheduleFactory,
        \Psr\Log\LoggerInterface $logger
    ){
        $this->storeManager = $storeManager;
        $this->productFactory = $productFactory;
        $this->configurableModel = $configurableModel;
        $this->helper = $helper;
        $this->webhookHelper = $webhookHelper;
        $this->scheduleFactory = $scheduleFactory;
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $storeId = $this->storeManager->getStore()->getId();

        # Only send webhook data if Scheduled Delivery is enabled
        if ($this->helper->isEnable($storeId) && $this->helper->isEnableSchedule($storeId)) {
            $productIds = $observer->getProductIds();
            $type = $this->helper->getWebhookTypeIdByType('product.updated');
            $webhooks = $this->helper->getWebhookByTypeId($type->getId(), $storeId);

            foreach ($productIds as $productId) {
                $webhookdata = $this->getWebhookProductData($productId);
                $body = json_encode($webhookdata);

                if (count($webhooks)) {
                    try {
                        foreach ($webhooks as $webhook) {
                            $scheduledata = [
                                'webhook_id' => $webhook->getId(),
                                'body' => $body,
                                'store_id' => $storeId,
                                'status' => WebhookEventSchedule::STATUS_PENDING
                            ];

                            $schedule = $this->scheduleFactory->create();
                            $schedule->setData($scheduledata);
                            $schedule->save();
                        }
                    } catch (\Exception $e) {
                        $this->logger->critical(__("Error on product.updated schedule: %1", $e->getMessage()));
                    }
                }
            }
        }
    }

    private function getWebhookProductData($productId)
    {
        $product = $this->productFactory->create()->load($productId);

        $webhookdata = [
            'event_id' => uniqid(),
            'event_type' => 'product.updated',
            'data' => [
                'product' => [
                    "id" => $product->getId(),
                    "sku" => $product->getSku(),
                    "name" => $product->getName(),
                    "attribute_set_id" => $product->getAttributeSetId(),
                    "price" => number_format($product->getPrice(), 2, '.', ''),
                    "status" => $product->getStatus(),
                    "visibility" => $product->getVisibility(),
                    "type_id" => $product->getTypeId(),
                    "created_at" => $product->getCreatedAt(),
                    "updated_at" => $product->getUpdatedAt(),
                    "weight" => $product->getWeight(),
                    "extension_attributes" => [
                        "website_ids" => $product->getWebsiteIds()
                    ],
                    "product_links" => $product->getProductUrl(),
                    "options" => $this->getProductOptions($product),
                    "media_gallery_entries" => $this->getProductImages($product->getMediaGalleryImages()),
                    "tier_prices" => $this->getTierPrices($product->getTierPrices()),
                    "custom_attributes" => [
                        [
                            "attribute_code" => "url_key",
                            "value" => $product->getUrlKey()
                        ],
                        [
                            "attribute_code" => "tax_class_id",
                            "value" => $product->getTaxClassId()
                        ]
                    ]
                ]
            ]
        ];
        return $webhookdata;
    }

    private function getProductOptions($product){
        $options = [];
        if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $options = $this->configurableModel->getConfigurableAttributesAsArray($product);
        }
        return $options;
    }

    private function getProductImages($productImages){
        $dataImages = [];
        if (count($productImages) > 0) {
            foreach ($productImages as $image) {
                $dataImages[] = $image->getUrl();
            }
        }
        return $dataImages;
    }

    private function getTierPrices($tierPrices){
        $dataPrices = [];
        if(count($tierPrices) > 0){
            foreach($tierPrices as $price){
                $dataPrices[] = [
                    'price' => number_format($price->getValue(), 2, '.', ''),
                    'customer_group_id' => $price->getCustomerGroupId(),
                    'qty' => $price->getQty()
                ];
            }
        }
        return $dataPrices;
    }
}