<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


declare(strict_types=1);

namespace Amasty\Gdpr\Model\ResourceModel\Consent\Grid;

use Amasty\Gdpr\Model\Consent\Consent;
use Amasty\Gdpr\Model\Consent\ConsentStore\ConsentStore;
use Amasty\Gdpr\Model\ResourceModel\Grid\AbstractSearchResult;
use Amasty\Gdpr\Setup\Operation\CreateConsentScopeTable;
use Magento\Store\Model\Store;

class Collection extends AbstractSearchResult
{
    private $multipleFields = [
        ConsentStore::CONSENT_LOCATION
    ];

    /**
     * @var int
     */
    private $storeId = Store::DEFAULT_STORE_ID;

    /**
     * @return $this|AbstractSearchResult|void
     */
    public function _initSelect()
    {
        parent::_initSelect();
        $this->joinStoreViewSettings();

        return $this;
    }

    /**
     * @param int $storeId
     *
     * @return $this
     */
    public function addStoreFilter(int $storeId)
    {
        $this->storeId = $storeId;

        return $this;
    }

    public function joinStoreViewSettings()
    {
        $consentStoreIdField = ConsentStore::CONSENT_ENTITY_ID;
        $consentIdField = Consent::ID;
        $this->join(
            ['consent_store_config' => $this->getTable(CreateConsentScopeTable::TABLE_NAME)],
            "main_table.{$consentIdField}=consent_store_config.{$consentStoreIdField}"
        );
        $this->addFieldToFilter(ConsentStore::CONSENT_STORE_ID, $this->storeId);
    }

    /**
     * @param array|string $field
     * @param null $condition
     *
     * @return $this|AbstractSearchResult
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if (in_array($field, $this->multipleFields)) {
            $options = $condition['in'] ?? [];
            $where = [];
            foreach ($options as $option) {
                $where[] = $this->_getConditionSql($field, ['finset' => $option]);
            }
            $this->getSelect()->where(implode(' OR ', $where));

            return $this;
        }

        return parent::addFieldToFilter($field, $condition);
    }
}
