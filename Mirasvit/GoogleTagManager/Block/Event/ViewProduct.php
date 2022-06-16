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

namespace Mirasvit\GoogleTagManager\Block\Event;

use Magento\Framework\View\Element\Template;
use Mirasvit\GoogleTagManager\Model\DataLayer;
use Mirasvit\GoogleTagManager\Registry;
use Mirasvit\GoogleTagManager\Service\DataService;

class ViewProduct extends Template
{
    protected $dataLayer;

    protected $dataService;

    protected $registry;

    public function __construct(
        DataLayer $dataLayer,
        DataService $dataService,
        Registry $registry,
        Template\Context $context
    ) {
        $this->dataLayer   = $dataLayer;
        $this->dataService = $dataService;
        $this->registry    = $registry;

        parent::__construct($context);
    }

    public function toHtml(): string
    {
        $product = $this->registry->getProduct();
        if (!$product) {
            return '';
        }

        $productData = $this->dataService->getProductData($product, $this->_storeManager->getStore()->getCurrentCurrency()->getCode());

        $data = [
            0 => 'event',
            1 => 'view_item',
            2 => [
                'items' => [$productData],
            ],
        ];

        $this->dataLayer->setCatalogData($data);

        return '';
    }
}
