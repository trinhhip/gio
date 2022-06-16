<?php
namespace Omnyfy\Vendor\Plugin\InventorySalesAdminUi;

class SalableQuantityPlugin
{
    public function beforePrepareDataSource(\Magento\InventorySalesAdminUi\Ui\Component\Listing\Column\SalableQuantity $subject, array $dataSource) {
        if ($dataSource['data']['totalRecords'] > 0) {
            foreach ($dataSource['data']['items'] as $key => $row) {
                $sku = str_replace('&#039;', '\'', $row['sku']);
                $dataSource['data']['items'][$key]['sku'] = $sku;
            }
        }
        return [$dataSource];
    }
}
