<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


declare(strict_types=1);

namespace Amasty\Gdpr\Model\Consent\ResourceModel;

use Amasty\Gdpr\Setup\Operation\CreateConsentsTable;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Consent extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(
            CreateConsentsTable::TABLE_NAME,
            \Amasty\Gdpr\Model\Consent\Consent::ID
        );
    }
}
