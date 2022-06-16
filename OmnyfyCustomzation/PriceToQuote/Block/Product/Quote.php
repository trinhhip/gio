<?php
/**
 * Lucas
 * Copyright (C) 2019
 *
 * This file is part of OmnyfyCustomzation/PriceToQuote.
 *
 * OmnyfyCustomzation/PriceToQuote is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace OmnyfyCustomzation\PriceToQuote\Block\Product;

use Exception;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Omnyfy\Vendor\Model\VendorFactory;

class Quote extends Template
{
    public $initProduct = null;
    /**
     * @var Registry
     */
    public $registry;
    /**
     * @var ProductRepository
     */
    public $productRepository;
    /**
     * @var VendorFactory
     */
    public $vendorFactory;
    /**
     * @var \Magento\Customer\Model\Session
     */
    public $customerSession;

    public function __construct(
        Context $context,
        ProductRepository $productRepository,
        Registry $registry,
        VendorFactory $vendorFactory,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->productRepository = $productRepository;
        $this->vendorFactory = $vendorFactory;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    public function getFormAction()
    {
        return $this->getUrl('catalog/product/quotepost', ['_secure' => true]);
    }

    public function getProduct()
    {
        return $this->initProduct();
    }

    protected function initProduct()
    {
        $productId = $this->getRequest()->getParam('id');
        $this->initProduct = $this->registry->registry('product_quote' . $productId);
        if (!$this->initProduct) {
            try {
                $this->initProduct = $this->productRepository->getById($productId);
            } catch (Exception $e) {
                return null;
            }

        }
        return $this->initProduct;
    }

    public function getVendor()
    {
        $productId = $this->getRequest()->getParam('id');
        $vendor = $this->vendorFactory->create();
        $vendorId = $vendor->getResource()->getVendorIdByProductId($productId);
        if (!$vendorId) {
            return null;
        }
        return $vendor->load($vendorId);
    }

    public function getCurrentCustomer()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return null;
        }
        return $this->customerSession->getCustomer();
    }
}