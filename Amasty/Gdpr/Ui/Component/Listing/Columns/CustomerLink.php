<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */

declare(strict_types=1);

namespace Amasty\Gdpr\Ui\Component\Listing\Columns;

class CustomerLink extends AbstractLink
{
    const URL = 'customer/index/edit';
    const ID_FIELD_NAME = 'customer_id';
    const ID_PARAM_NAME = 'id';

    protected function getIdFieldName(): string
    {
        return self::ID_FIELD_NAME;
    }

    protected function getIdParamName(): string
    {
        return self::ID_PARAM_NAME;
    }

    protected function getUrl(): string
    {
        return self::URL;
    }
}
