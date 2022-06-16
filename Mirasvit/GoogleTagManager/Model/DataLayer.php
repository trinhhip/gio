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

class DataLayer
{
    const KEY_GTM_DATA     = 'mst_gtm_data';

    private $catalogSession;

    private $checkoutSession;

    private $customerSession;

    public function __construct(
        CatalogSession $catalogSession,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession
    ) {
        $this->catalogSession  = $catalogSession;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
    }

    public function resetCatalogData()
    {
        $this->catalogSession->setData(self::KEY_GTM_DATA, null);
    }

    public function setCatalogData(array $data)
    {
        $storedData = (array)$this->catalogSession->getData(self::KEY_GTM_DATA);

        $storedData[] = $data;

        $this->catalogSession->setData(self::KEY_GTM_DATA, $storedData);
    }

    public function getCatalogData(): array
    {
        return (array)$this->catalogSession->getData(self::KEY_GTM_DATA);
    }

    public function resetCheckoutData()
    {
        $this->checkoutSession->setData(self::KEY_GTM_DATA, null);
    }

    public function setCheckoutData(array $data)
    {
        $storedData = (array)$this->checkoutSession->getData(self::KEY_GTM_DATA);

        $storedData[] = $data;

        $this->checkoutSession->setData(self::KEY_GTM_DATA, $storedData);
    }

    public function getCheckoutData(): array
    {
        return (array)$this->checkoutSession->getData(self::KEY_GTM_DATA);
    }

    public function resetCustomerData()
    {
        $this->customerSession->setData(self::KEY_GTM_DATA, null);
    }

    public function setCustomerData(array $data)
    {
        $storedData = (array)$this->customerSession->getData(self::KEY_GTM_DATA);

        $storedData[] = $data;

        $this->customerSession->setData(self::KEY_GTM_DATA, $storedData);
    }

    public function getCustomerData(): array
    {
        return (array)$this->customerSession->getData(self::KEY_GTM_DATA);
    }

    public function getData()
    {
        return array_merge(
            (array)$this->catalogSession->getData(self::KEY_GTM_DATA),
            (array)$this->checkoutSession->getData(self::KEY_GTM_DATA),
            (array)$this->customerSession->getData(self::KEY_GTM_DATA)
        );
    }
}
