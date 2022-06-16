<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OmnyfyCustomzation\Order\Component;

class ExportButton extends \Magento\Ui\Component\ExportButton
{
    const CSV_EXPORT_URL = 'sales/order/export';

    /**
     * @return void
     */
    public function prepare()
    {
        parent::prepare();
        $context = $this->getContext();
        $config = $this->getData('config');
        $additionalParams = $this->getAdditionalParams($config, $context);
        $options = [
            [
                'url' => $this->urlBuilder->getUrl(self::CSV_EXPORT_URL, $additionalParams),
                'value' => 'custom_csv',
                'label' => "Custom CSV"

            ]
        ];
        $config['options'] = $options;
        $this->setData('config', $config);
        $config['options'] = $options;
        $this->setData('config', $config);
    }
}
