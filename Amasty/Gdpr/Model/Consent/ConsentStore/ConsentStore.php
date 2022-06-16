<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


declare(strict_types=1);

namespace Amasty\Gdpr\Model\Consent\ConsentStore;

use Magento\Framework\Model\AbstractModel;

class ConsentStore extends AbstractModel
{
    const ID = 'id';

    const CONSENT_STORE_ID = 'store_id';

    const CONSENT_ENTITY_ID = 'consent_entity_id';

    const IS_ENABLED = 'is_enabled';

    const IS_REQUIRED = 'is_required';

    const LOG_THE_CONSENT = 'log_the_consent';

    const HIDE_CONSENT_AFTER_USER_LEFT_THE_CONSENT = 'hide_the_consent_after_user_left_the_consent';

    const CONSENT_LOCATION = 'consent_location';

    const CONSENT_TEXT = 'consent_text';

    const VISIBILITY = 'visibility';

    const COUNTRIES = 'countries';

    const LINK_TYPE = 'link_type';

    const CMS_PAGE_ID = 'cms_page_id';

    const SORT_ORDER = 'sort_order';

    public function _construct()
    {
        parent::_construct();

        $this->_init(ResourceModel\ConsentStore::class);
        $this->setIdFieldName(self::ID);
    }
}
