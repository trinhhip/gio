<?php
/**
 * Project: Multi vendor.
 * User: jing
 * Date: 10/11/17
 * Time: 11:29 AM
 */
namespace Omnyfy\Vendor\Ui\Component;

class SubvendorGridDataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    protected $session;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Backend\Model\Session $session,
        \Magento\Framework\Api\Search\ReportingInterface $reporting,
        \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        array $meta = [],
        array $data = [])
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $reporting, $searchCriteriaBuilder, $request, $filterBuilder, $meta, $data);
        $this->session = $session;
    }

    protected function searchResultToOutput(\Magento\Framework\Api\Search\SearchResultInterface $searchResult) {
        $arrItems = [];
        $arrItems['totalRecords'] = $searchResult->getTotalCount();

        $arrItems['items'] = [];
        foreach ($searchResult->getItems() as $item) {
            $arrItems['items'][] = $item->getData();
        }

        return $arrItems;
    }

    public function getData()
    {
        $collection = $this->getSearchResult();

        $collection->addFieldToFilter('main_table.is_subvendor', 1);

        // Make sure vendors can only see their own subvendors
        $vendorInfo = $this->session->getVendorInfo();
        if (!empty($vendorInfo) || isset($vendorInfo['vendor_id'])) {
            $collection->addFieldToFilter('parent_vendor_id', $vendorInfo['vendor_id']);
        }

        /*
        $vendorInfo = $this->_backendSession->getVendorInfo();

        if (!empty($vendorInfo)) {
            $collection->addFieldToFilter('vendor_id', $vendorInfo['vendor_id']);
        }
        */

        return $this->searchResultToOutput($collection);
    }
}