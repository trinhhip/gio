<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-gtm
 * @version   1.0.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\GoogleTagManager\Controller\Item;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\GoogleTagManager\Model\DataLayer;
use Mirasvit\GoogleTagManager\Service\DataService;

class Info extends Action
{
    private $dataLayer;

    private $dataService;

    private $jsonFactory;

    private $productRepository;

    private $storeManager;

    private $context;

    public function __construct(
        DataLayer $dataLayer,
        DataService $dataService,
        JsonFactory $jsonFactory,
        ProductRepository $productRepository,
        StoreManagerInterface $storeManager,
        Context $context
    ) {
        $this->dataLayer         = $dataLayer;
        $this->dataService       = $dataService;
        $this->jsonFactory       = $jsonFactory;
        $this->productRepository = $productRepository;
        $this->storeManager      = $storeManager;
        $this->context           = $context;

        parent::__construct($context);
    }

    public function execute(): Json
    {
        $store = $this->storeManager->getStore();

        $info = [];

        $ids = (array)$this->getRequest()->getParam('product_ids', []);
        if ($ids) {
            foreach ($ids as $id) {
                try {
                    $product = $this->productRepository->getById((int)$id);

                    $info[] = $this->dataService->getProductData($product, $store->getCurrentCurrency()->getCode());
                } catch (\Exception $e) {
                }
            }
        }

        return $this->jsonFactory->create()->setData([
            'success' => true,
            'data'    => $info,
        ]);
    }

    protected function _isAllowed(): bool
    {
        return true;
    }
}
