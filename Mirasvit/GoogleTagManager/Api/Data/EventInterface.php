<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-gtm
 * @version   1.0.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\GoogleTagManager\Api\Data;

interface EventInterface
{
    const ATTR_GTM_EVENT     = 'data-gtm-event';
    const ATTR_GTM_LIST_ID   = 'data-gtm-list_id';
    const ATTR_GTM_LIST_NAME = 'data-gtm-list_name';
    const ATTR_GTM_ITEM_ID   = 'data-gtm-item_id';

    public function getData(array $data): array;
}
