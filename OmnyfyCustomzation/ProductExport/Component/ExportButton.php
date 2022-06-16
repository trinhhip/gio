<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace OmnyfyCustomzation\ProductExport\Component;

class ExportButton extends \Magento\Ui\Component\ExportButton
{
    const CSV_EXPORT_URL = 'export/product/csv';

    /**
     * @return void
     */
    public function prepare()
    {
        $context = $this->getContext();
        $config = $this->getData('config');
        $additionalParams = $this->getAdditionalParams($config, $context);
        $options = [
            [
                'url' => $this->urlBuilder->getUrl(self::CSV_EXPORT_URL, $additionalParams),
                'value' => 'csv',
                'label' => "CSV"

            ]
        ];
        $config['options'] = $options;
        $this->setData('config', $config);
        $config['options'] = $options;
        $this->setData('config', $config);
        parent::prepare();
    }
}
