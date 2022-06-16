<?php
namespace Omnyfy\VendorAuth\Plugin;

class ProductImportPlugin
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Omnyfy\VendorAuth\Helper\VendorApi $vendorApiHelper
     */
    protected $vendorApiHelper;

    /**
     * ProductImportPlugin constructor
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Omnyfy\VendorAuth\Helper\VendorApi $vendorApiHelper
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Omnyfy\VendorAuth\Helper\VendorApi $vendorApiHelper
    ){
        $this->connection = $resource->getConnection();
        $this->vendorApiHelper = $vendorApiHelper;
    }

    public function beforeImportProducts(
        \Omnyfy\ProductImport\Model\ProductImportWebApi $subject,
        $params
    ){
        if (isset($_SERVER['REQUEST_METHOD']) && ($_SERVER['REQUEST_METHOD'] == 'POST' || $_SERVER['REQUEST_METHOD'] == 'PUT')) {
            $tokenVendorId = $this->vendorApiHelper->getVendorIdFromToken();
            if ($tokenVendorId > 0) {
                $productSkus = $this->getProductSkusByVendorId($tokenVendorId);
                if (isset($params['items']) && count($params['items']) > 0) {
                    foreach ($params['items'] as $key => $param) {
                        if (isset($param['product_data']) && isset($param['product_data']['sku'])) {
                            $params['items'][$key]['inventory']['vendor_ids'] = [$tokenVendorId];
                            # Ignore (unset) another vendor's product if:
                            # 1. payload sku is not vendor's sku
                            # 2. the sku is already exists on table
                            # Note: if sku is not exists on db, vendor can add it as their new product - don't ignore this
                            if (array_search($param['product_data']['sku'], $productSkus) === false) {
                                $isProductExists = $this->isProductSkuExists($param['product_data']['sku']);
                                if ($isProductExists) {
                                    unset($params['items'][$key]);
                                }
                            }
                        }
                    }
                }
            }
        }
        return [$params];
    }

    private function getProductSkusByVendorId($vendorId){
        $select = $this->connection->select()
            ->from(
                ['vp' => 'omnyfy_vendor_vendor_product'],
                'cpe.sku'
            )
            ->join(
                ['cpe' => 'catalog_product_entity'],
                'vp.product_id = cpe.entity_id'
            )
            ->where('vp.vendor_id = ?', $vendorId);
        $productSkus = $this->connection->fetchCol($select);

        return $productSkus;
    }

    private function isProductSkuExists($sku){
        $select = $this->connection->select()
            ->from(
                ['cpe' => 'catalog_product_entity'],
                'cpe.sku'
            )
            ->where('cpe.sku = ?', $sku);
        $product = $this->connection->fetchOne($select);

        if (isset($product) && $product != "") {
            return true;
        }else{
            return false;
        }
    }
}
