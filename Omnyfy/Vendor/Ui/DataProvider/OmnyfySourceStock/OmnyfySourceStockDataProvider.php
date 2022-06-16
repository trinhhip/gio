<?php
namespace Omnyfy\Vendor\Ui\DataProvider\OmnyfySourceStock;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Backend\Model\Session;

class OmnyfySourceStockDataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    protected $collection;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        Session $session,
        array $meta = [],
        array $data = []
    ) {
        $vendorInfo = $session->getVendorInfo();
        if($vendorInfo) {
            $data['config']['filter_url_params']['vendor_id'] = $vendorInfo['vendor_id'];
        }
        parent::__construct($name, $primaryFieldName, $requestFieldName, $reporting, $searchCriteriaBuilder, $request, $filterBuilder, $meta, $data);
    }
}
