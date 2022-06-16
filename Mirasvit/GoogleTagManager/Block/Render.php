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

namespace Mirasvit\GoogleTagManager\Block;

use Magento\Framework\View\Element\Template;
use Mirasvit\GoogleTagManager\Model\Config;
use Mirasvit\GoogleTagManager\Model\DataLayer;
use Mirasvit\GoogleTagManager\Model\DataProvider;
use Mirasvit\GoogleTagManager\Service\UaService;

class Render extends Template
{
    private $config;

    private $dataLayer;

    private $dataProvider;

    private $uaService;

    public function __construct(
        DataLayer $dataLayer,
        DataProvider $dataProvider,
        Config $config,
        UaService $uaService,
        Template\Context $context
    ) {
        $this->config       = $config;
        $this->dataLayer    = $dataLayer;
        $this->dataProvider = $dataProvider;
        $this->uaService    = $uaService;

        parent::__construct($context);
    }

    public function getDataLayerData(): array
    {
        $data = [];

        $catalogData  = (array)$this->dataLayer->getCatalogData();
        $checkoutData = (array)$this->dataLayer->getCheckoutData();

        $data = array_merge($data, $catalogData, $this->uaService->convert($catalogData));
        $data = array_merge($data, $checkoutData, $this->uaService->convert($checkoutData));

        $this->dataLayer->resetCatalogData();
        $this->dataLayer->resetCheckoutData();

        return $data;
    }

    public function getDataLayerProducts(): array
    {
        return $this->dataProvider->getProducts();
    }

    protected function _toHtml(): string
    {
        if (!$this->config->getGeneralIsEnable()) {
            return '';
        }

        return parent::_toHtml();
    }

    public function getItemInfoUrl(): string
    {
        return $this->getUrl('mst_gtm/item/info');
    }
}
