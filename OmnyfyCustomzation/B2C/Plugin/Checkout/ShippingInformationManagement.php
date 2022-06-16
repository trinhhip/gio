<?php


namespace OmnyfyCustomzation\B2C\Plugin\Checkout;

use Closure;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Api\CartRepositoryInterface;
use OmnyfyCustomzation\B2C\Helper\Data;

class ShippingInformationManagement
{
    const COUNTRY_PARAM = '{country}';
    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;
    /**
     * @var Data
     */
    protected $helperData;
    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;
    /**
     * @var CountryFactory
     */
    protected $countryFactory;

    public function __construct(
        CartRepositoryInterface $quoteRepository,
        CollectionFactory $productCollectionFactory,
        CountryFactory $countryFactory,
        Data $helperData
    )
    {
        $this->quoteRepository = $quoteRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->countryFactory = $countryFactory;
        $this->helperData = $helperData;
    }

    public function aroundSaveAddressInformation(
        \Omnyfy\Vendor\Model\ShippingInformationManagement $subject,
        Closure $proceed,
        $cartId,
        ShippingInformationInterface $addressInformation
    )
    {
        $allowCountries = $this->helperData->getAllowCountries();
        if ($allowCountries) {
            $countryCode = $addressInformation->getShippingAddress()->getCountryId();
            $countryName = $this->countryFactory->create()->loadByCode($countryCode)->getName();
            $allowCountries = explode(',', $allowCountries);
            $quote = $this->quoteRepository->get($cartId);
            $productIds = [];
            foreach ($quote->getItems() as $item) {
                $productIds = $item->getProductId();
            }
            $productCollection = $this->productCollectionFactory->create();
            $productCollection->addFieldToSelect('for_retail');
            $productCollection->addFieldToFilter('entity_id', ['IN' => $productIds]);
            foreach ($productCollection as $product) {
                if ($product->getForRetail() && !in_array($countryCode, $allowCountries)) {
                    $notification = $this->helperData->getNotificationMessage();
                    $notification = str_replace(self::COUNTRY_PARAM, $countryName, $notification);
                    throw new StateException(__($notification));
                    break;
                }
            }

        }
        return $proceed($cartId, $addressInformation);
    }
}
