<?php
namespace Omnyfy\ProductImport\Model;
use BigBridge\ProductImport\Api\ImportConfig;
use BigBridge\ProductImport\Api\Data\Product;

class ProductImportWebApi implements \Omnyfy\ProductImport\Api\ProductImportWebApiInterface
{
    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    protected $request;

    /**
     * @var \BigBridge\ProductImport\Api\ImporterFactory
     */
    protected $importerFactory;

    /**
     * @var \Omnyfy\ProductImport\Helper\ProductImport
     */
    protected $importHelper;

    /**
     * @var \Omnyfy\ProductImport\Api\ResponseInterfaceFactory
     */
    protected $responseFactory;

    /**
     * @var \Omnyfy\ProductImport\Api\ResponseDataInterfaceFactory
     */
    protected $responseDataFactory;

    /**
     * @var \Omnyfy\ProductImport\Api\ResponseDataProductInterfaceFactory
     */
    protected $responseDataProductFactory;

    /**
     * @var \Omnyfy\ProductImport\Model\ProductService\SimpleProductService
     */
    protected $simpleProductService;

    /**
     * @var \Omnyfy\ProductImport\Model\ProductService\ConfigurableProductService
     */
    protected $configurableProductService;

    private $inventorySource = [];

    /**
     * constructor
     *
     * @param \Magento\Framework\Webapi\Rest\Request $request
     * @param \BigBridge\ProductImport\Api\ImporterFactory $importerFactory
     * @param \Omnyfy\ProductImport\Helper\ProductImport $importHelper
     * @param \Omnyfy\ProductImport\Api\ResponseInterfaceFactory $responseFactory
     * @param \Omnyfy\ProductImport\Api\ResponseDataInterfaceFactory $responseDataFactory
     * @param \Omnyfy\ProductImport\Api\ResponseDataProductInterfaceFactory $responseDataProductFactory
     * @param \Omnyfy\ProductImport\Model\ProductService\SimpleProductService $simpleProductService
     * @param \Omnyfy\ProductImport\Model\ProductService\ConfigurableProductService $configurableProductService
     */
    public function __construct(
        \Magento\Framework\Webapi\Rest\Request $request,
        \BigBridge\ProductImport\Api\ImporterFactory $importerFactory,
        \Omnyfy\ProductImport\Helper\ProductImport $importHelper,
        \Omnyfy\ProductImport\Api\ResponseInterfaceFactory $responseFactory,
        \Omnyfy\ProductImport\Api\ResponseDataInterfaceFactory $responseDataFactory,
        \Omnyfy\ProductImport\Api\ResponseDataProductInterfaceFactory $responseDataProductFactory,
        \Omnyfy\ProductImport\Model\ProductService\SimpleProductService $simpleProductService,
        \Omnyfy\ProductImport\Model\ProductService\ConfigurableProductService $configurableProductService
    ) {
        $this->request = $request;
        $this->importerFactory = $importerFactory;
        $this->importHelper = $importHelper;
        $this->responseFactory = $responseFactory;
        $this->responseDataFactory = $responseDataFactory;
        $this->responseDataProductFactory = $responseDataProductFactory;
        $this->simpleProductService = $simpleProductService;
        $this->configurableProductService = $configurableProductService;
    }

    /**
     * Imports products
     *
     * @api
     * @return \Omnyfy\ProductImport\Api\ResponseInterface
     * @throws Exception
     */
    public function add()
    {
        $params = $this->request->getBodyParams();
        $response = $this->importProducts($params);
        return $response;
    }

    /**
     * Imports update products
     *
     * @api
     * @return \Omnyfy\ProductImport\Api\ResponseInterface
     * @throws Exception
     */
    public function update(){

        $params = $this->request->getBodyParams();
        $response = $this->importProducts($params);
        return $response;
    }

    public function importProducts($params){
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/omnyfy_product_import.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('========================================');

        $response = $this->responseFactory->create();

        if (isset($params['items']) && count($params['items']) > 0) {
            $config = new ImportConfig();

            $config->duplicateUrlKeyStrategy = ImportConfig::DUPLICATE_KEY_STRATEGY_ADD_SERIAL;

            if ($this->importHelper->getConfigData('omnyfy_product_import/general/category_strategy') == 'remove') {
                $config->categoryStrategy = ImportConfig::CATEGORY_STRATEGY_SET;
            }
            if ($this->importHelper->getConfigData('omnyfy_product_import/general/image_strategy') == 'remove') {
                $config->imageStrategy = ImportConfig::IMAGE_STRATEGY_SET;
            }

            # a callback function to postprocess imported products
            $config->resultCallback = function(Product $product) use ($response, $logger) {
                $responseDataProduct = $this->responseDataProductFactory->create();
                $responseData = $this->responseDataFactory->create();

                if ($product->isOk()) {
                    try {
                        #save vendor_id and inventory
                        $message = $this->importHelper->updateVendorSourceInventory($product, $this->inventorySource);
                        if ($message) {
                            $response = $this->createResponseItems(
                                $response, $responseData, $responseDataProduct,
                                $product->id, $product->getSku(), false, $message);

                            $logger->info(sprintf("%s: failed! error = %s", $product->lineNumber, $message));
                        }else{
                            $response = $this->createResponseItems(
                                $response, $responseData, $responseDataProduct,
                                $product->id, $product->getSku(), true, "");

                            $logger->info(sprintf("%s: success! sku = %s, id = %s", $product->lineNumber, $product->getSku(), $product->id));
                        }
                    } catch (\Exception $e) {
                        $logger->info($e->getmessage());
                    }
                } else {
                    $response = $this->createResponseItems(
                        $response, $responseData, $responseDataProduct,
                        $product->id, $product->getSku(), false, $product->getErrors());

                    $logger->info(sprintf("%s: failed! error = %s", $product->lineNumber, implode('; ', $product->getErrors())));
                }
            };

            try {
                $importer = $this->importerFactory->createImporter($config);

                foreach ($params['items'] as $i => $item) {
                    $productData = $item['product_data'];
                    $totalQty = 0;

                    $arrVendorIds = [];
                    $arrInventoryItems = [];
                    if (isset($item['inventory'])) {
                        if (isset($item['inventory']['vendor_ids'])) {
                            $arrVendorIds = $item['inventory']['vendor_ids'];
                        }
                        if (isset($item['inventory']['items'])) {
                            $arrInventoryItems = $item['inventory']['items'];
                            $totalQty = $this->getProductQuantity($arrInventoryItems);
                        }
                    }
                    $this->inventorySource[] = [
                        'sku' => $productData['sku'],
                        'vendor_ids' => $arrVendorIds,
                        'inventory' => $arrInventoryItems
                    ];

                    if ($totalQty > 0) {
                        $item['product_data']['extension_attributes']['stock_item']['qty'] = $totalQty;
                    }

                    # Import Simple Product
                    if ($productData['type_id'] == 'simple') {
                        $product = $this->simpleProductService->getProduct($item);
                        $product->lineNumber = $i + 1;
                        $importer->importSimpleProduct($product);

                    # Import Configurable Product
                    }elseif ($productData['type_id'] == 'configurable') {
                        $product = $this->configurableProductService->getProduct($item);
                        $product->lineNumber = $i + 1;
                        $importer->importConfigurableProduct($product);
                    }
                }
                $importer->flush();

            } catch (\Exception $e) {
                $logger->info($e->getMessage());
            }
        }
        return $response;
    }

    private function createResponseItems($response, $responseData, $responseDataProduct, $id, $sku, $success, $errorMessage){
        $responseDataProduct->setId($id);
        $responseDataProduct->setSku($sku);

        $responseData->setSuccess($success);
        $responseData->setError($errorMessage);
        $responseData->setProductData($responseDataProduct);

        $response->setItems($responseData);

        return $response;
    }

    private function getProductQuantity($items){
        $totalQty = 0;
        foreach ($items as $inv) {
            if (isset($inv['qty'])) {
                $totalQty += $inv['qty'];
            }
        }
        return $totalQty;
    }
}
