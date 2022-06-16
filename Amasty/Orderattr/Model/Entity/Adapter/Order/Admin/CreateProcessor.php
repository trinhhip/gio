<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

declare(strict_types=1);

namespace Amasty\Orderattr\Model\Entity\Adapter\Order\Admin;

use Amasty\Orderattr\Model\Entity\Adapter\Quote\Adapter;
use Amasty\Orderattr\Model\Entity\EntityData;
use Amasty\Orderattr\Model\Entity\EntityResolver;
use Amasty\Orderattr\Model\Value\Metadata\Form;
use Amasty\Orderattr\Model\Value\Metadata\FormFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartExtensionInterfaceFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Store\Api\Data\StoreInterface;

class CreateProcessor
{
    /**
     * @var CartExtensionInterfaceFactory
     */
    private $cartExtensionFactory;

    /**
     * @var FormFactory
     */
    private $metadataFormFactory;

    /**
     * @var EntityResolver
     */
    private $entityResolver;

    /**
     * @var Adapter
     */
    private $quoteAdapter;

    /**
     * @var RequestInterface|Http
     */
    private $request;

    public function __construct(
        CartExtensionInterfaceFactory $cartExtensionFactory,
        FormFactory $metadataFormFactory,
        EntityResolver $entityResolver,
        Adapter $quoteAdapter,
        RequestInterface $request
    ) {
        $this->cartExtensionFactory = $cartExtensionFactory;
        $this->metadataFormFactory = $metadataFormFactory;
        $this->entityResolver = $entityResolver;
        $this->quoteAdapter = $quoteAdapter;
        $this->request = $request;
    }

    /**
     * Process attributes data from admin order creating form
     *
     * @param CartInterface $quote
     * @param StoreInterface $store
     * @param array $data
     * @return void
     * @throws LocalizedException
     */
    public function processAttributesDataFromAdminForm(
        CartInterface $quote,
        StoreInterface $store,
        array $data
    ): void {
        $attributesData = $data['extension_attributes']['amasty_order_attributes'] ?? [];

        if (empty($attributesData) && empty($this->request->getFiles()->toArray())) {
            return;
        }
        $entity = $this->entityResolver->getEntityByQuoteId($quote->getId());

        $form = $this->createEntityForm($entity, $store, (int)$quote->getCustomerGroupId());
        // emulate request
        $request = $form->prepareRequest($attributesData);
        $attributesData = $form->extractData($request);

        if (empty($attributesData)) {
            return;
        }

        $form->restoreData($attributesData);
        $errors = $form->validateData($attributesData);
        if (is_array($errors)) {
            throw new LocalizedException(__(implode($errors)));
        }

        $this->quoteAdapter->addExtensionAttributesToQuote($quote, true);
    }

    /**
     * Return Checkout Form instance
     *
     * @param EntityData $entity
     * @param StoreInterface $store
     * @param int $customerGroup
     *
     * @return Form
     */
    private function createEntityForm(EntityData $entity, StoreInterface $store, int $customerGroup): Form
    {
        /** @var Form $formProcessor */
        $formProcessor = $this->metadataFormFactory->create();
        $formProcessor->setFormCode('adminhtml_checkout')
            ->setEntity($entity)
            ->setStore($store)
            ->setCustomerGroupId($customerGroup);

        return $formProcessor;
    }
}
