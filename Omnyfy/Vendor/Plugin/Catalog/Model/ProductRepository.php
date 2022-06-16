<?php


namespace Omnyfy\Vendor\Plugin\Catalog\Model;


use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\RequestInterface;

class ProductRepository
{
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var \Magento\Framework\Api\Filter
     */
    private $filter;
    /**
     * @var \Magento\Framework\Api\Search\FilterGroupBuilder
     */
    private $filterGroup;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * ProductRepository constructor.
     * @param RequestInterface $request
     * @param \Magento\Framework\Api\Filter $filter
     * @param \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroup
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        RequestInterface $request,
        \Magento\Framework\Api\Filter $filter,
        \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroup,
        \Magento\Framework\App\ResourceConnection $resource
    )
    {
        $this->request = $request;
        $this->filter = $filter;
        $this->filterGroup = $filterGroup;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
    }

    public function beforeGetList(\Magento\Catalog\Model\ProductRepository $subject, SearchCriteriaInterface $searchCriteria)
    {
        $vendorId = (int)$this->request->getParam('vendor_id');
        if($vendorId){
            $select = $this->connection->select()
                ->from($this->connection->getTableName('omnyfy_vendor_vendor_product'),'product_id')
                ->where('vendor_id = ?', $vendorId);
            $productIds = $this->connection->fetchCol($select);
            $filters[] = $this->filter->setConditionType('in')
            ->setField('entity_id')
            ->setValue($productIds);

            if($searchCriteria->getFilterGroups()){
                foreach ($searchCriteria->getFilterGroups() as $key => $FilterGroup){
                    $filters[] = $FilterGroup->getFilters()[$key];
                }
            }
            if(count($filters) > 0){
                foreach ($filters as $data){
                    $filterGroup[] = $this->filterGroup->addFilter($data)->create();
                }
            }

            $searchCriteria->setFilterGroups($filterGroup);

        }
        return [$searchCriteria];
    }

    public function afterGetList(\Magento\Catalog\Model\ProductRepository $subject, $result, SearchCriteriaInterface $searchCriteria)
    {
        $product = [];
        $vendorId = (int)$this->request->getParam('vendor_id');
        if($vendorId){
            if(!empty($result->getItems())) {
                foreach ($result->getItems() as $entity) {
                    $extensionAttributes = $entity->getExtensionAttributes();
                    $extensionAttributes->setVendorId($vendorId);
                    $entity->setExtensionAttributes($extensionAttributes);
                    $product[] = $entity;
                }
                $result->setItems($product);
            }
        }
        return $result;
    }
}
