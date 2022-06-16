<?php


namespace OmnyfyCustomzation\B2C\Ui\DataProvider\Account;


use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Magento\Customer\Model\CustomerFactory;

class GridDataProvider extends DataProvider
{
    const DEFAULT_WEBSITE_ID = 1;
    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        CustomerFactory $customerFactory,
        array $meta = [],
        array $data = []
    )
    {
        $this->customerFactory = $customerFactory;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
    }

    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $arrItems = [];
        $arrItems['totalRecords'] = $searchResult->getTotalCount();

        $arrItems['items'] = [];
        foreach ($searchResult->getItems() as $item) {
            $customer = $this->customerFactory->create()->setWebsiteId(self::DEFAULT_WEBSITE_ID)->loadByEmail($item->getEmail());
            if ($customer->getId()) {
                $arrItems['items'][] = $this->prepareItem($customer, $item);
            } else {
                $arrItems['items'][] = $item->getData();
            }
        }

        return $arrItems;
    }

    private function prepareItem($customer, $item)
    {
        $countryCode = $customer->getCountryCode() ? '(+' . $customer->getCountryCode() . ') ' : '';
        return [
            'entity_id' => $item->getId(),
            'email' => $customer->getEmail(),
            'status' => $customer->getIsApproved(),
            'business_name' => $customer->getBusinessName(),
            'business_url' => $customer->getBusinessUrl(),
            'designation' => $customer->getDesignation(),
            'business_location' => $customer->getBusinessLocation(),
            'business_type' => $customer->getBusinessType(),
            'phone_number' => $countryCode . $customer->getPhoneNumber(),
            'created_at' => $item->getCreatedAt(),
            'updated_at' => $item->getCreatedAt(),
        ];
    }
}