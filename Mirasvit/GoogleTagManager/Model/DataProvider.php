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

namespace Mirasvit\GoogleTagManager\Model;

use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;

class DataProvider
{
    const KEY_GTM_PRODUCTS = 'mst_gtm_products';

    const KEY_PRODUCT_ID = 'product_id';

    private $customerSession;

    public function __construct(
        CustomerSession $customerSession
    ) {
        $this->customerSession = $customerSession;
    }

    public function resetProducts()
    {
        $this->customerSession->setData(self::KEY_GTM_PRODUCTS, null);
    }

    public function setProducts(array $data)
    {
        $this->customerSession->setData(self::KEY_GTM_PRODUCTS, $data);
    }

    public function addProduct(array $product)
    {
        $products = $this->getProducts();

        $products[$product[self::KEY_PRODUCT_ID]] = $product;

        $this->customerSession->setData(self::KEY_GTM_PRODUCTS, $products);
    }

    public function getProducts(): array
    {
        return (array)$this->customerSession->getData(self::KEY_GTM_PRODUCTS);
    }
}
