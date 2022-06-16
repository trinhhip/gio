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

namespace OmnyfyCustomzation\PriceToQuote\Controller\Product;

use Exception;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use OmnyfyCustomzation\PriceToQuote\Helper\Data;
use Psr\Log\LoggerInterface;

class Quote extends Action
{

    protected $resultPageFactory;
    /**
     * @var ProductRepository
     */
    protected $productRepository;
    /**
     * @var Data
     */
    public $helperData;
    /**
     * @var Registry
     */
    public $registry;
    /**
     * @var LoggerInterface
     */
    public $logger;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ProductRepository $productRepository,
        Data $helperData,
        Registry $registry,
        LoggerInterface $logger

    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->productRepository = $productRepository;
        $this->helperData = $helperData;
        $this->registry = $registry;
        $this->logger = $logger;
        parent::__construct($context);
    }


    public function execute()
    {
        $productId = $this->getRequest()->getParam('id');
        $isPriceToQuote = false;
        try {
            $product = $this->productRepository->getById($productId);
            if ($this->helperData->isPriceToQuote($product)) {
                $this->registry->register('product_quote' . $productId, $product);
                $isPriceToQuote = true;
            }
            return $this->resultPageFactory->create();
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }
        if (!$isPriceToQuote && !$this->getRequest()->getParam('submitted')) {
            $this->messageManager->addWarningMessage(__('The product you have selected can\'t request for a price.'));
        }
        return $this->goBack();
    }

    private function goBack()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
