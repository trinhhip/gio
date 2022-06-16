<?php
namespace Omnyfy\Order\Plugin;
use Magento\Framework\Exception\AuthorizationException;

class OrderItemRepositoryPlugin
{
    /**
     * @var \Magento\Framework\Api\Filter
     */
    protected $filter;

    /**
     * @var \Magento\Framework\Api\Search\FilterGroupBuilder
     */
    protected $filterGroupBuilder;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Omnyfy\VendorAuth\Helper\VendorApi $vendorApiHelper
     *
     */
    protected $vendorApiHelper;

    /**
     * OrderItemRepositoryPlugin constructor
     * @param \Magento\Framework\Api\Filter $filter
     * @param \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Omnyfy\VendorAuth\Helper\VendorApi $vendorApiHelper
    */
    public function __construct(
        \Magento\Framework\Api\Filter $filter,
        \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\ResourceConnection $resource,
        \Omnyfy\VendorAuth\Helper\VendorApi $vendorApiHelper
    ){
        $this->filter = $filter;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->request = $request;
        $this->connection = $resource->getConnection();
        $this->vendorApiHelper = $vendorApiHelper;
    }

    public function beforeGet(
        \Magento\Sales\Api\OrderItemRepositoryInterface $subject,
        $id
    ){
        $vendorIdFromToken = $this->vendorApiHelper->getVendorIdFromToken();

        if ($vendorIdFromToken > 0) {
            $orderItemIds = $this->getOrderItemIdsByVendorId($vendorIdFromToken);

            if (array_search($id, $orderItemIds) === false) {
                throw new AuthorizationException(__('Consumer is not authorized to access %resources'));
            }
        }
    }

    public function beforeGetList(
        \Magento\Sales\Api\OrderItemRepositoryInterface $subject,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ){
        $vendorIdFromToken = $this->vendorApiHelper->getVendorIdFromToken();

        if ($vendorIdFromToken === 0) { //integration token
            $param = $this->request->getParams();
            if (isset($param['vendor_id'])) {
                $vendorIdFromToken = $param['vendor_id'];
            }
        }

        if ($vendorIdFromToken > 0) {
            $orderItemIds = $this->getOrderItemIdsByVendorId($vendorIdFromToken);

            $filters[] = $this->filter->setField("item_id")
                ->setValue($orderItemIds)
                ->setConditionType("in");

            if($searchCriteria->getFilterGroups()){
                foreach ($searchCriteria->getFilterGroups() as $key => $filterGroup){
                    $filters[] = $filterGroup->getFilters()[$key];
                }
            }

            $filterGroup = [];
            if(count($filters) > 0){
                foreach ($filters as $data){
                    $filterGroup[] = $this->filterGroupBuilder->addFilter($data)->create();
                }
            }
            $searchCriteria->setFilterGroups($filterGroup);

        }

        return [$searchCriteria];
    }

    private function getOrderItemIdsByVendorId($vendorId){
        $select = $this->connection->select()
            ->from($this->connection->getTableName('sales_order_item'), 'item_id')
            ->where('vendor_id = ?', $vendorId);
        $orderItemIds = $this->connection->fetchCol($select);

        return $orderItemIds;
    }
}
