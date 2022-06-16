<?php

namespace Omnyfy\Rma\Plugin\Service\Rma;


class RmaManagementPlugin
{
    /**
     * @var array
     */
    private $orderRmas;

    public function __construct(
        \Mirasvit\Rma\Api\Repository\ItemRepositoryInterface $rmaItemRepository,
        \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface $rmaRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->rmaItemRepository      = $rmaItemRepository;
        $this->rmaRepository          = $rmaRepository;
        $this->searchCriteriaBuilder  = $searchCriteriaBuilder;
    }


    public function aroundGetRmasByOrder(\Mirasvit\Rma\Service\Rma\RmaManagement $subject, callable $proceed, $order)
    {
        if (isset($this->orderRmas[$order->getId()])) {
            return $this->orderRmas[$order->getId()];
        }
        /** @var \Magento\Sales\Model\Order $order */
        $orderItemIds = $order->getItemsCollection()->getAllIds();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_item_id', $orderItemIds, 'in')
            ->addFilter('qty_requested', 0, 'gt')
        ;
        $rmaItems = $this->rmaItemRepository->getList($searchCriteria->create())->getItems();
        $rmaIds = [];
        foreach ($rmaItems as $rmaItem) {
            $rmaIds[] = $rmaItem->getRmaId();
        }
        if (!$rmaIds) {
            return [];
        }
        $rmaIds = array_unique($rmaIds);

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('rma_id', $rmaIds, 'in')
        ;

        $this->orderRmas[$order->getId()] = $this->rmaRepository->getList($searchCriteria->create())->getItems();

        return $this->orderRmas[$order->getId()];
    }

}
