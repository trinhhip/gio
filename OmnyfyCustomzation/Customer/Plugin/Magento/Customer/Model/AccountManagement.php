<?php

namespace OmnyfyCustomzation\Customer\Plugin\Magento\Customer\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AccountManagement as BaseAccountManagement;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Phrase;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Mail\Template\TransportBuilder;

/**
 * Class AccountManagement
 */
class AccountManagement
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CustomerViewHelper
     */
    protected $customerViewHelper;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * AccountManagement constructor.
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param ScopeConfigInterface        $scopeConfig
     * @param StoreManagerInterface       $storeManager
     * @param CustomerViewHelper          $customerViewHelper
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        CustomerViewHelper $customerViewHelper,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        TransportBuilder $transportBuilder
    ) {
        $this->customerRepository = $customerRepository;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->customerViewHelper = $customerViewHelper;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder
            ?: ObjectManager::getInstance()->get(SearchCriteriaBuilder::class);
        $this->transportBuilder = $transportBuilder;
    }

    public function aroundResetPassword(
        BaseAccountManagement $subject,
        $proceed,
        $email,
        $resetToken,
        $newPassword
    ) {
        $customer = $this->matchCustomerByRpToken($resetToken);
        $result = $proceed($email, $resetToken, $newPassword);
        if (true === $result) {
            if(!$customer) {
                /** @var \Magento\Customer\Model\Customer $customer */
                $customer = ObjectManager::getInstance()->get(\Magento\Customer\Model\CustomerFactory::class)
                    ->create()->loadByEmail($email);
            }
            if (!$email) {
                $email = $customer->getEmail();
            }

            $storeId = $this->storeManager->getStore()->getId();
            if (!$storeId) {
                $storeId = $this->getWebsiteStoreId($customer);
            }
            $template = Customer::XML_PATH_RESET_PASSWORD_TEMPLATE;
            $templateId = $this->scopeConfig->getValue($template, ScopeInterface::SCOPE_STORE, $storeId);
            $sender = BaseAccountManagement::XML_PATH_FORGOT_EMAIL_IDENTITY;
            $customerName = $this->customerViewHelper->getCustomerName($customer);
            $customerObject = new DataObject();
            $customerObject->setData('name', $customerName);
            $customerObject->setData('email', $email);
            $templateParams = [
                'customer' => $customerObject
            ];
            $transport = $this->transportBuilder->setTemplateIdentifier($templateId)->setTemplateOptions(
                ['area' => Area::AREA_FRONTEND, 'store' => $storeId]
            )->setTemplateVars($templateParams)->setFrom(
                $this->scopeConfig->getValue($sender, ScopeInterface::SCOPE_STORE, $storeId)
            )->addTo($email, $customerName)->getTransport();

            $transport->sendMessage();
        }
        return $result;
    }

    /**
     * @param      $customer
     * @param null $defaultStoreId
     *
     * @return mixed|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getWebsiteStoreId($customer, $defaultStoreId = null)
    {
        if ($customer->getWebsiteId() != 0 && empty($defaultStoreId)) {
            $storeIds = $this->storeManager->getWebsite($customer->getWebsiteId())->getStoreIds();
            reset($storeIds);
            $defaultStoreId = current($storeIds);
        }
        return $defaultStoreId;
    }

    /**
     * Match a customer by their RP token.
     *
     * @param string $rpToken
     *
     * @return CustomerInterface
     * @throws ExpiredException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function matchCustomerByRpToken(string $rpToken)
    {

        $this->searchCriteriaBuilder->addFilter(
            'rp_token',
            $rpToken
        );
        $this->searchCriteriaBuilder->setPageSize(1);
        $found = $this->customerRepository->getList(
            $this->searchCriteriaBuilder->create()
        );

        if ($found->getTotalCount() > 1) {
            //Failed to generated unique RP token
            throw new ExpiredException(
                new Phrase('Reset password token expired.')
            );
        }
        if ($found->getTotalCount() === 0) {
            //Customer with such token not found.
            throw NoSuchEntityException::singleField(
                'rp_token',
                $rpToken
            );
        }

        //Unique customer found.
        return $found->getItems()[0];
    }
}
