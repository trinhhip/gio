<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

abstract class AbstractLink extends Column
{
    const URL = '';

    const ID_FIELD_NAME = '';

    const ID_PARAM_NAME = 'id';

    /**
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $url = $this->context->getUrl(
                    static::URL,
                    [static::ID_PARAM_NAME => $item[static::ID_FIELD_NAME]]
                );
                $item[$this->_data['config']['link']] = $url;
            }
        }

        return $dataSource;
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();

        $this->_data['config']['link'] = $this->_data['name'] . '_link';
    }
}
