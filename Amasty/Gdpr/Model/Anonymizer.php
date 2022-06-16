<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model;

use Amasty\Gdpr\Api\Data\DeleteRequestInterface;
use Amasty\Gdpr\Api\RequestInterface;
use Amasty\Gdpr\Model\DeleteRequest\Notifier;
use Amasty\Gdpr\Model\ResourceModel\DeleteRequest\CollectionFactory;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerNameGenerationInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface as CustomerAddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\ResourceModel\Quote\Collection as QuoteCollection;
use Magento\Sales\Api\Data\OrderAddressInterface as OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Address as OrderAddress;
use Magento\Sales\Model\ResourceModel\GridPool;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;

class Anonymizer implements RequestInterface
{
    const ANONYMOUS_SYMBOL = '-';

    const RANDOM_LENGTH = 5;

    const ANONYMOUS_DATE = '1970-01-01';

    const ANONYMIZE_IP = '0.0.0.0';

    const ANONYMIZE_REGION_ID = 0;

    const ANONYMIZE_COUNTRY_ID = "0";

    const EMAIL_TEMPLATE_CODE = 'amgdpr_anonimization';

    const CONFIG_PATH_KEY_ANONYMISATION = 'anonymisation';

    const CONFIG_PATH_KEY_DELETION = 'deletion';

    /**
     * @var \Magento\Framework\Event\Manager
     */
    private $eventManager;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @var CustomerData
     */
    private $customerData;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var \Magento\Newsletter\Model\ResourceModel\Subscriber
     */
    private $subscriberResource;

    /**
     * @var \Magento\Newsletter\Model\Subscriber
     */
    private $subscriber;

    private $isDeleting = false;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var CollectionFactory
     */
    private $deleteRequestCollectionFactory;

    /**
     * @var ActionLogger
     */
    private $logger;

    /**
     * @var SenderResolverInterface
     */
    protected $senderResolver;

    /**
     * @var CustomerNameGenerationInterface
     */
    private $nameGeneration;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var \Magento\Customer\Model\Customer\Mapper
     */
    private $customerMapper;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerDataFactory;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var GridPool
     */
    private $gridPool;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var GiftRegistryProvider
     */
    private $giftRegistryProvider;

    /**
     * @var CleaningDate
     */
    private $cleaningDate;

    /**
     * @var Notifier
     */
    private $notifier;

    /**
     * @var FlagRegistry
     */
    private $flagRegistry;

    public function __construct(
        \Magento\Framework\Event\Manager $eventManager,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        CustomerData $customerData,
        OrderRepositoryInterface $orderRepository,
        CartRepositoryInterface $quoteRepository,
        \Magento\Newsletter\Model\Subscriber $subscriber,
        AddressRepositoryInterface $addressRepository,
        \Magento\Newsletter\Model\ResourceModel\Subscriber $subscriberResource,
        TransportBuilder $transportBuilder,
        CollectionFactory $deleteRequestCollectionFactory,
        ActionLogger $logger,
        SenderResolverInterface $senderResolver,
        CustomerNameGenerationInterface $nameGeneration,
        DataObjectHelper $dataObjectHelper,
        \Magento\Customer\Model\Customer\Mapper $customerMapper,
        CustomerInterfaceFactory $customerDataFactory,
        Config $configProvider,
        GridPool $gridPool,
        ProductMetadataInterface $productMetadata,
        DataObjectFactory $dataObjectFactory,
        GiftRegistryProvider $giftRegistryProvider,
        CleaningDate $cleaningDate,
        Notifier $notifier,
        FlagRegistry $flagRegistry
    ) {
        $this->eventManager = $eventManager;
        $this->customerRepository = $customerRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->customerData = $customerData;
        $this->orderRepository = $orderRepository;
        $this->quoteRepository = $quoteRepository;
        $this->subscriber = $subscriber;
        $this->addressRepository = $addressRepository;
        $this->subscriberResource = $subscriberResource;
        $this->transportBuilder = $transportBuilder;
        $this->deleteRequestCollectionFactory = $deleteRequestCollectionFactory;
        $this->logger = $logger;
        $this->senderResolver = $senderResolver;
        $this->nameGeneration = $nameGeneration;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customerMapper = $customerMapper;
        $this->customerDataFactory = $customerDataFactory;
        $this->configProvider = $configProvider;
        $this->gridPool = $gridPool;
        $this->productMetadata = $productMetadata;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->giftRegistryProvider = $giftRegistryProvider;
        $this->cleaningDate = $cleaningDate;
        $this->notifier = $notifier;
        $this->flagRegistry = $flagRegistry;
    }

    /**
     * @param string|int $customerId
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function approveDeleteRequest($customerId)
    {
        if (!$this->canDeleteCustomer($customerId)) {
            return false;
        }

        $this->isDeleting = true;
        $this->anonymizeCustomer($customerId);

        $this->deleteRequestCollectionFactory->create()->approveRequest($customerId);

        $this->logger->logAction('delete_request_approved', $customerId);

        return true;
    }

    /**
     * @param string|int $customerId
     */
    public function deleteExpiredItems($customerId)
    {
        $this->isDeleting = true;
        $this->anonymizeOrders($customerId);
        $this->anonymizeQuotes($customerId);
    }

    /**
     * @param string|int $customerId
     *
     * @return bool
     */
    public function canDeleteCustomer($customerId)
    {
        $ordersData = $this->getCustomerActiveOrders($customerId);

        return empty($ordersData);
    }

    /**
     * @param $customerId
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function anonymizeCustomer($customerId)
    {
        $this->eventManager->dispatch(
            'before_amgdpr_customer_anonymisation',
            ['customerId' => $customerId, 'isDeleting' => $this->isDeleting]
        );
        if ($this->productMetadata->getEdition() === 'Enterprise') {
            $this->anonymizeGiftRegistry($customerId);
        }
        $this->anonymizeOrders($customerId);
        $this->anonymizeQuotes($customerId);
        $this->deleteSubscription($customerId);
        $this->anonymizeAccountInformation($customerId);

        if (!$this->isDeleting) {
            $this->logger->logAction('data_anonymised_by_customer', $customerId);
        }

        $this->eventManager->dispatch(
            'after_amgdpr_customer_anonymisation',
            ['customerId' => $customerId, 'isDeleting' => $this->isDeleting]
        );
    }

    /**
     * @param int $customerId
     */
    private function anonymizeGiftRegistry($customerId)
    {
        /** @var \Magento\GiftRegistry\Model\ResourceModel\Entity\Collection $giftRegistryEntityCollection */
        $giftRegistryEntityCollection = $this->giftRegistryProvider->getGiftRegistryEntityCollectionByCustomerId(
            (int)$customerId
        );
        $giftRegistryEntities = [];

        foreach ($giftRegistryEntityCollection->getItems() as $giftRegistry) {
            $this->anonymizeGiftRegistryEntity($giftRegistry);
            $giftRegistry->save();

            $giftRegistryEntities[] = $giftRegistry->getEntityId();
        }

        if (empty($giftRegistryEntities)) {
            return;
        }

        /** @var \Magento\GiftRegistry\Model\ResourceModel\Person\Collection $giftRegistryPersonCollection */
        $giftRegistryPersonCollection = $this->giftRegistryProvider->getGiftRegistryPersonCollectionByEntities(
            $giftRegistryEntities
        );

        foreach ($giftRegistryPersonCollection->getItems() as $giftRegistryPerson) {
            $this->anonymizeGiftRegistryPerson($giftRegistryPerson);
            $giftRegistryPerson->save();
        }
    }

    /**
     * @param \Magento\GiftRegistry\Model\Entity $giftRegistry
     */
    private function anonymizeGiftRegistryEntity($giftRegistry)
    {
        $giftRegistryAttributeCodes = $this->customerData->getAttributeCodes('gift_registry_entity');

        foreach ($giftRegistryAttributeCodes as $code) {
            switch ($code) {
                case 'shipping_address':
                    $addressArray = \Zend_Json_Decoder::decode($giftRegistry->getShippingAddress());

                    if (!$addressArray
                        || ($addressArray['country_id'] == self::ANONYMIZE_COUNTRY_ID
                        && $addressArray['region_id'] == self::ANONYMIZE_REGION_ID)
                    ) {
                        continue 2;
                    }
                    $address = $this->dataObjectFactory->create()
                        ->addData($addressArray);
                    $this->anonymizeAddress($address);
                    $randomString = \Zend_Json_Encoder::encode($address->getData());
                    break;
                case 'custom_values':
                    $randomString = null;
                    break;
                case 'event_country':
                    $randomString = "00";
                    break;
                case 'event_date':
                    $randomString = self::ANONYMOUS_DATE;
                    break;
                default:
                    $randomString = $this->generateFieldValue();
            }
            $giftRegistry->setData($code, $randomString);
        }
    }

    /**
     * @param \Magento\GiftRegistry\Model\Person $person
     */
    private function anonymizeGiftRegistryPerson($person)
    {
        $attributeCodes = $this->customerData->getAttributeCodes('gift_registry_person');

        foreach ($attributeCodes as $code) {
            switch ($code) {
                case 'email':
                    $randomString = $this->getRandomEmail();
                    break;
                case 'role':
                    $randomString = null;
                    break;
                case 'custom_values':
                    $randomString = 'null';
                    break;
                default:
                    $randomString = $this->generateFieldValue();
            }
            $person->setData($code, $randomString);
        }
    }

    /**
     * @param int|string $customerId
     */
    private function anonymizeOrders($customerId)
    {
        /** @var OrderCollection $entities */
        $orders = $this->orderCollectionFactory->create();

        $orders->addFieldToSelect('*')->addFieldToFilter('customer_id', $customerId);

        if ($this->isDeleting && $dateForRemove = $this->cleaningDate->getPersonalDataStoredDate()) {
            // check $this->isDeleting for anonymize all docs after anonymization request
            $orders->addFieldToFilter(OrderInterface::CREATED_AT, ['lt' => $dateForRemove]);
        }

        /** @var \Magento\Sales\Model\Order $item */
        foreach ($orders as $item) {
            if ($this->isAlreadyAnonymized($item)) {
                continue;
            }
            $this->prepareSalesData($item);
            //@codingStandardsIgnoreStart
            $this->orderRepository->save($item);
            //@codingStandardsIgnoreEnd
            $this->gridPool->refreshByOrderId($item->getId());
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote|\Magento\Sales\Model\Order $item
     *
     * @return bool
     */
    private function isAlreadyAnonymized($item)
    {
        return $item->getCustomerFirstname() == self::ANONYMOUS_SYMBOL
            && $item->getCustomerFirstname() == self::ANONYMOUS_SYMBOL
            && $item->getCustomerLastname() == self::ANONYMOUS_SYMBOL
            && $item->getCustomerEmail() == self::ANONYMOUS_SYMBOL
            && $item->getRemoteIp() == self::ANONYMIZE_IP;
    }

    /**
     * @param $incrementId
     * @return bool
     */
    public function anonymizeOrderByIncrementId($incrementId)
    {
        /** @var OrderCollection $orders */
        $orders = $this->orderCollectionFactory->create();

        $item = $orders->addFieldToSelect('*')
            ->addFieldToFilter(OrderInterface::CUSTOMER_IS_GUEST, 1)
            ->addFieldToFilter(OrderInterface::INCREMENT_ID, $incrementId)
            ->getFirstItem();

        if ($this->configProvider->isAvoidAnonymization()) {
            $orderStatuses = explode(',', $this->configProvider->getOrderStatuses());

            if (in_array($item->getStatus(), $orderStatuses)) {
                return false;
            }
        }

        $this->prepareSalesData($item);
        $this->orderRepository->save($item);
        $this->gridPool->refreshByOrderId($item->getId());

        return true;
    }

    /**
     * @param \Magento\Quote\Model\Quote|\Magento\Sales\Model\Order $object
     */
    private function prepareSalesData($object)
    {
        $object->setCustomerFirstname($this->generateFieldValue());
        $object->setCustomerMiddlename($this->generateFieldValue());
        $object->setCustomerLastname($this->generateFieldValue());
        $object->setCustomerEmail($this->getRandomEmail());
        $object->setRemoteIp(self::ANONYMIZE_IP);
        if ($object->getBillingAddress()) {
            $this->anonymizeAddress($object->getBillingAddress());
        }
        if ($object->getShippingAddress()) {
            $this->anonymizeAddress($object->getShippingAddress());
        }
    }

    /**
     * @return string
     */
    public function generateFieldValue()
    {
        $rand = self::ANONYMOUS_SYMBOL;
        if (!$this->isDeleting) {
            $rand = 'anonymous' . $this->getRandomString();
        }

        return $rand;
    }

    /**
     * @return string
     */
    private function getRandomString()
    {
        return bin2hex(openssl_random_pseudo_bytes(self::RANDOM_LENGTH));
    }

    /**
     * @return string
     */
    public function getRandomEmail()
    {
        $email = self::ANONYMOUS_SYMBOL;
        if (!$this->isDeleting) {
            $email = $this->generateFieldValue();
        }
        $email = $email . '@' . $this->getRandomString() . '.com';

        if ($this->isEmailExists($email)) {
            $email = $this->getRandomEmail();
        }

        return $email;
    }

    public function isEmailExists($email)
    {
        $collection = $this->customerCollectionFactory->create();

        return (bool)$collection->addFieldToFilter('email', $email)->getSize();
    }

    /**
     * @param DataObject|OrderAddress|QuoteAddress|OrderAddressInterface|CustomerAddressInterface|null $address
     */
    private function anonymizeAddress($address)
    {
        $attributeCodes = $this->customerData->getAttributeCodes('customer_address');

        foreach ($attributeCodes as $code) {
            switch ($code) {
                case 'telephone':
                case 'fax':
                    $randomString = '0000000';
                    break;
                case 'country_id':
                    $randomString = self::ANONYMIZE_COUNTRY_ID;
                    break;
                case 'region_id':
                    $randomString = self::ANONYMIZE_REGION_ID;
                    break;
                case 'region':
                    $region = $address->getRegion();

                    if (is_object($region)) {
                        $region->setRegion($this->generateFieldValue());
                        $region->setRegionCode($this->generateFieldValue());
                        $region->setRegionId(self::ANONYMIZE_REGION_ID);
                    } else {
                        $region = $this->generateFieldValue();
                    }
                    $randomString = $region;
                    break;
                default:
                    $randomString = $this->generateFieldValue();
            }
            $address->setData($code, $randomString);
        }
    }

    /**
     * @param int|string $customerId
     */
    private function anonymizeQuotes($customerId)
    {
        /** @var QuoteCollection $entities */
        $quotes = $this->quoteCollectionFactory->create();

        $quotes->addFieldToSelect('*')->addFieldToFilter('customer_id', $customerId);

        if ($this->isDeleting && $dateForRemove = $this->cleaningDate->getPersonalDataStoredDate()) {
            // check $this->isDeleting for anonymize all docs after anonymization request
            $quotes->addFieldToFilter(OrderInterface::CREATED_AT, ['lt' => $dateForRemove]);
        }

        /** @var \Magento\Quote\Model\Quote $item */
        foreach ($quotes as $item) {
            if ($this->isAlreadyAnonymized($item)) {
                continue;
            }
            $this->prepareSalesData($item);
            //@codingStandardsIgnoreStart
            $this->quoteRepository->save($item);
            //@codingStandardsIgnoreEnd
        }
    }

    /**
     * @param int|string $customerId
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteSubscription($customerId)
    {
        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $this->subscriber->loadByCustomerId($customerId);
        if ($subscriber->getId()) {
            $subscriber->unsubscribe();
            $this->subscriberResource->delete($subscriber);
        }
    }

    /**
     * @param int|string $customerId
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function anonymizeThirdPartyInformation($customerId)
    {
        $savedCustomerData = $this->customerRepository->getById($customerId);
        $customData = $this->customerMapper->toFlatArray($savedCustomerData);

        $attributeCodes = $this->customerData->getAttributeCodes('customer');
        $exclude = $this->customerData->getAttributeCodes('exclude');
        $exclude = array_merge($exclude, $attributeCodes);

        foreach ($customData as $attributeCode => $value) {
            if (!in_array($attributeCode, $exclude)) {
                $customData[$attributeCode] = $this->generateFieldValue();
            }
        }

        $customer = $this->customerDataFactory->create();
        $customData = array_merge(
            $this->customerMapper->toFlatArray($savedCustomerData),
            $customData
        );
        $customData['id'] = $customerId;
        $this->dataObjectHelper->populateWithArray(
            $customer,
            $customData,
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $this->customerRepository->save($customer);
    }

    /**
     * @param int|string $customerId
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function anonymizeAccountInformation($customerId)
    {
        $this->flagRegistry->setUpgradeOrderCustomerEmailDisabledFlag(true);

        /** @var \Magento\Customer\Model\Data\Customer $customer */
        $customer = $this->customerRepository->getById($customerId);
        $oldEmail = $customer->getEmail();
        $customerName = $this->nameGeneration->getCustomerName($customer);

        $attributeCodes = $this->customerData->getAttributeCodes('customer');

        foreach ($attributeCodes as $attributeCode) {
            switch ($attributeCode) {
                case 'email':
                    $randomString = $this->getRandomEmail();
                    break;
                case 'dob':
                    $randomString = self::ANONYMOUS_DATE;
                    break;
                case 'gender':
                    $randomString = 3; // Not Specified
                    break;
                default:
                    $randomString = $this->generateFieldValue();
            }
            $customer->setData($attributeCode, $randomString);
        }

        if (!$this->isDeleting) {
            $this->sendConfirmationEmail($this::CONFIG_PATH_KEY_ANONYMISATION, $oldEmail, $customerName, $customer);
            $this->deleteRequestCollectionFactory->create()->approveRequest($customer->getId());
        } else {
            $this->setIgnoreValidationFlag($customer);
            $this->sendConfirmationEmail($this::CONFIG_PATH_KEY_DELETION, $oldEmail, $customerName, $customer);
        }

        $this->customerRepository->save($customer);

        $addresses = $customer->getAddresses();
        /** @var \Magento\Customer\Api\Data\AddressInterface $address */
        foreach ($addresses as $address) {
            $this->anonymizeAddress($address);
            //@codingStandardsIgnoreStart
            $this->addressRepository->save($address);
            //@codingStandardsIgnoreEnd
        }

        $this->anonymizeThirdPartyInformation($customerId);
    }

    /**
     * @param string                                $configPath
     * @param string                                $realEmail
     * @param string                                $customerName
     * @param \Magento\Customer\Model\Data\Customer $customer
     *
     * @throws \Magento\Framework\Exception\MailException
     */
    public function sendConfirmationEmail($configPath, $realEmail, $customerName, $customer)
    {
        $template = $this->configProvider->getConfirmationEmailTemplate($configPath, $customer->getStoreId());

        $sender = $this->configProvider->getConfirmationEmailSender($configPath);

        $replyTo = $this->configProvider->getConfirmationEmailReplyTo($configPath);
        if (!trim($replyTo)) {
            $result = $this->senderResolver->resolve($sender);
            $replyTo = $result['email'];
        }

        $transport = $this->transportBuilder->setTemplateIdentifier(
            $template
        )->setTemplateOptions(
            [
                'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $customer->getStoreId()
            ]
        )->setTemplateVars(
            [
                'anonymousEmail' => $customer->getEmail(),
                'customerName' => $customerName
            ]
        )->setFrom(
            $sender
        )->addTo(
            $realEmail,
            $customerName
        )->setReplyTo(
            $replyTo
        )->getTransport();

        $transport->sendMessage();
    }

    /**
     * Get data of customer active orders
     *
     * @param int|string $customerId
     *
     * @return array
     */
    public function getCustomerActiveOrders($customerId)
    {
        $ordersData = [];

        if ($this->configProvider->isAvoidAnonymization()) {
            $orderStatuses = $this->configProvider->getOrderStatuses();

            if ($orderStatuses) {
                $orders = $this->orderCollectionFactory->create()
                    ->addFieldToFilter('customer_id', $customerId)
                    ->addFieldToFilter('status', ['in' => explode(',', $orderStatuses)]);

                $ordersData = $orders->getData();
            }
        }

        return $ordersData;
    }

    /**
     * @return array
     */
    public function getUnprocessedRequests()
    {
        $requestCollection = $this->deleteRequestCollectionFactory->create()->addFieldToFilter(
            DeleteRequestInterface::APPROVED,
            false
        );

        return $requestCollection->getData();
    }

    /**
     * @param int $customerId
     * @param string $comment
     */
    public function denyDeleteRequest($customerId, $comment)
    {
        $this->notifier->notify($customerId, $comment);
        $this->deleteRequestCollectionFactory->create()->deleteByCustomerId($customerId);
    }

    /**
     * Set ignore_validation_flag to skip unnecessary address and customer validation
     */
    private function setIgnoreValidationFlag(CustomerInterface $customer): void
    {
        $customer->setData('ignore_validation_flag', true);
    }
}
