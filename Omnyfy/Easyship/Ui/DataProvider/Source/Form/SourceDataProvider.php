<?php
namespace Omnyfy\Easyship\Ui\DataProvider\Source\Form;

use Magento\Backend\Model\Session;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Ui\DataProvider\SearchResultFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Inventory\Model\ResourceModel\Source\CollectionFactory;
use Magento\Framework\App\ResourceConnection;


class SourceDataProvider extends \Magento\InventoryAdminUi\Ui\DataProvider\SourceDataProvider
{
    private $sourceRepository;
    private $searchResultFactory;
    private $session;
    private $sourceCount;
    private $pool;
    private $collectionFactory;
    private $resourceConnection;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        SourceRepositoryInterface $sourceRepository,
        SearchResultFactory $searchResultFactory,
        Session $session,
        PoolInterface $pool = null,
        CollectionFactory $collectionFactory,
        ResourceConnection $resourceConnection,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $reporting, $searchCriteriaBuilder, $request, $filterBuilder, $sourceRepository, $searchResultFactory, $session, $meta, $data, $pool);
        $this->collectionFactory = $collectionFactory;
        $this->sourceRepository = $sourceRepository;
        $this->searchResultFactory = $searchResultFactory;
        $this->session = $session;
        $this->pool = $pool ?: ObjectManager::getInstance()->get(PoolInterface::class);
        $this->resourceConnection = $resourceConnection;
    }

    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $arrItems = [];

        $arrItems['items'] = [];
        foreach ($searchResult->getItems() as $item) {
            $itemData = [];
            foreach ($item->getCustomAttributes() as $attribute) {
                $itemData[$attribute->getAttributeCode()] = $attribute->getValue();
            }
            $sourceCode = $itemData['source_code'];
            $source = $this->collectionFactory->create()->getItemByColumnValue('source_code', $sourceCode);
            $vendorId = $source->getVendorId();
            $easyshipAccountId = $source->getEasyshipAccountId();
            $easyshipAddressId = $source->getEasyshipAddressId();
            $itemData['vendor_id'] = $vendorId;
            $itemData['easyship_account_id'] = $easyshipAccountId;
            $itemData['easyship_address_id'] = $easyshipAddressId;
            $itemData['stock'] = $this->getStockIdsBySource($sourceCode);
            $itemData['company_name'] = $source->getCompanyName();
            
            $arrItems['items'][] = $itemData;
        }

        $arrItems['totalRecords'] = $searchResult->getTotalCount();

        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $arrItems = $modifier->modifyData($arrItems);
        }

        return $arrItems;
    }

    public function getMeta()
    {
        $meta = parent::getMeta();

        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }

    public function getStockIdsBySource($sourceCode) {
        $conn = $this->resourceConnection->getConnection();
        $query = $conn->select()->from('inventory_source_stock_link', 'stock_id')->where('source_code = ?', $sourceCode);
        $result = $conn->fetchCol($query);
        return $result;
    }
}
