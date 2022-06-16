<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Setup;

use Amasty\Gdpr\Model\Source\CountriesRestrictment;
use Amasty\Gdpr\Model\Consent\Consent as ConsentModel;
use Amasty\Gdpr\Model\Consent\ConsentStore\ConsentStore;
use Amasty\Gdpr\Model\Consent\Repository;
use Amasty\Gdpr\Model\Source\ConsentLinkType;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\Store;

class InstallData implements InstallDataInterface
{
    const DEFAULT_CHECKBOX_TEXT = 'I have read and accept the <a href="{link}">privacy policy</a>';
    /**
     * @var Repository
     */
    private $repository;

    public function __construct(
        Repository $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * Install data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     * @throws CouldNotSaveException
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->createConsent();
    }

    /**
     * @throws CouldNotSaveException
     */
    private function createConsent()
    {
        /** @var ConsentModel $consent * */
        $consent = $this->repository->getEmptyConsentModel();
        $consent->setConsentName(__('Privacy Checkbox')->getText());
        $consent->setConsentCode('privacy_checkbox');

        $consentStore = $this->repository->getEmptyConsentStoreModel();
        $consentStore->addData(
            [
                ConsentStore::CONSENT_STORE_ID => Store::DEFAULT_STORE_ID,
                ConsentStore::IS_ENABLED => false,
                ConsentStore::IS_REQUIRED => true,
                ConsentStore::LOG_THE_CONSENT => true,
                ConsentStore::SORT_ORDER => 0,
                ConsentStore::HIDE_CONSENT_AFTER_USER_LEFT_THE_CONSENT => false,
                ConsentStore::CONSENT_LOCATION => 'registration,checkout,contactus,subscription',
                ConsentStore::CONSENT_TEXT => self::DEFAULT_CHECKBOX_TEXT,
                ConsentStore::LINK_TYPE => ConsentLinkType::PRIVACY_POLICY,
                ConsentStore::VISIBILITY => CountriesRestrictment::EEA_COUNTRIES
            ]
        );
        $consent->setStoreModel($consentStore);

        try {
            $this->repository->save($consent);
        } catch (CouldNotSaveException $e) {
            null;
        }
    }
}
