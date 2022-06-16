<?php
namespace Omnyfy\ProductImport\Model\ProductService;
use BigBridge\ProductImport\Api\Data\ConfigurableProduct;

class ConfigurableProductService extends \Omnyfy\ProductImport\Model\ProductService\AbstractBaseProductService
{
    public function getProduct($productRequestItem)
    {
        $productData = $productRequestItem['product_data'];

        $configurable = new ConfigurableProduct($productData['sku']);

        if (isset($productData['attribute_set_id'])) {
            $configurable->setAttributeSetId($productData['attribute_set_id']);
        }

        if (isset($productData['website_codes'])) {
            $configurable->setWebsitesByCode($productData['website_codes']);
        }

        if (isset($productData['super_attribute_codes'])){
            $configurable->setSuperAttributeCodes($productData['super_attribute_codes']);
        }
        if (isset($productData['variant_skus'])){
            $configurable->setVariantSkus($productData['variant_skus']);
        }

        $global = $configurable->global();
        if (isset($productData['name'])) {
            $global->setName($productData['name']);
        }
        if (isset($productRequestItem['price']) && isset($productRequestItem['price']['value'])) {
            $global->setPrice($productRequestItem['price']['value']);
        }

        if (isset($productData['visibility'])) {
            $global->setVisibility($productData['visibility']);
        }

        if (isset($productData['status'])) {
            $global->setStatus($productData['status']);
        }

        if (isset($productData['weight'])) {
            $global->setWeight($productData['weight']);
        }

        $stockItem = $configurable->defaultStockItem();
        if (isset($productData['extension_attributes']) && isset($productData['extension_attributes']['stock_item'])) {
            if (isset($productData['extension_attributes']['stock_item']['qty'])) {
                $stockItem->setQty($productData['extension_attributes']['stock_item']['qty']);
            }
            if (isset($productData['extension_attributes']['stock_item']['is_in_stock'])) {
                $stockItem->setIsInStock($productData['extension_attributes']['stock_item']['is_in_stock']);
            }
            if (isset($productData['extension_attributes']['stock_item']['manage_stock'])) {
                $stockItem->setManageStock($productData['extension_attributes']['stock_item']['manage_stock']);
            }
        }

        if (isset($productData['custom_attributes'])) {
            foreach ($productData['custom_attributes'] as $attr) {
                if ($attr['attribute_code'] == 'category_ids') {
                    $configurable->addCategoryIds($attr['value']);

                }elseif ($attr['attribute_code'] == 'description') {
                    $global->setDescription($attr['value']);

                }elseif ($attr['attribute_code'] == 'url_key') {
                    // $global->setUrlKey($attr['value']);

                }elseif ($attr['attribute_code'] == 'name') {
                    # do nothing as the name attribute has been set

                }else{
                    $global->setCustomAttribute($attr['attribute_code'], $attr['value']);
                }
            }
        }
        if (isset($productData['name'])) {
            # Becase generate url key is based on name
            $global->generateUrlKey();
        }

        if (isset($productData['media_gallery_entries'])) {
            parent::setProductImages($configurable, $productData['media_gallery_entries']);
        }

        return $configurable;
    }
}
