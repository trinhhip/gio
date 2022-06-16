<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


declare(strict_types=1);

namespace Amasty\Gdpr\Model\Consent\ConsentStore\ResourceModel;

use Amasty\Gdpr\Model\Consent\ConsentStore\ConsentStore as ConsentStoreModel;
use Amasty\Gdpr\Setup\Operation\CreateConsentScopeTable;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ConsentStore extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(
            CreateConsentScopeTable::TABLE_NAME,
            ConsentStoreModel::ID
        );
    }
}
