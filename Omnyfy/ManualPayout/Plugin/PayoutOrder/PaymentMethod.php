<?php
namespace Omnyfy\ManualPayout\Plugin\PayoutOrder;

class PaymentMethod
{
    public function afterGetSearchResult(
        \Omnyfy\Mcm\Ui\DataProvider\PendingPayout\Grid\PendingPayoutOrderDataProvider $subject,
        \Magento\Framework\Api\Search\SearchResultInterface $result)
    {
        $conn = $result->getResource()->getConnection();
        $result->getSelect()->join(
            ['sop' => $conn->getTableName('sales_order_payment')],
            'sop.parent_id = so.entity_id',
            ['method']
        );
        return $result;
    }
}
