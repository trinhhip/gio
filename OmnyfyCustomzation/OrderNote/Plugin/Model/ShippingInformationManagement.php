<?php

namespace OmnyfyCustomzation\OrderNote\Plugin\Model;

use OmnyfyCustomzation\OrderNote\Setup\UpgradeSchema;

class ShippingInformationManagement
{
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    private $quoteRepository;
    /**
     * @var \OmnyfyCustomzation\OrderNote\Helper\Data
     */
    private $helper;

    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \OmnyfyCustomzation\OrderNote\Helper\Data $helper
    )
    {
        $this->quoteRepository = $quoteRepository;
        $this->helper = $helper;
    }

    public function afterSaveAddressInformation(\Omnyfy\Vendor\Model\ShippingInformationManagement $subject,
                                                $result,
                                                $cartId,
                                                \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    )
    {
        if($this->helper->isEnabled()) {
            $noteItemId = $addressInformation->getExtensionAttributes()->getNoteItemIds();
            if($noteItemId) {
                $quote = $this->quoteRepository->getActive($cartId);
                foreach($quote->getAllVisibleItems() as $item){
                    $item->setData(UpgradeSchema::ORDER_NOTE_ATTRIBUTE,@$noteItemId[$item->getData('item_id')]);
                    $item->save();
                }
            }
        }

        return $result;

    }
}