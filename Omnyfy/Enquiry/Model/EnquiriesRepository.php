<?php


namespace Omnyfy\Enquiry\Model;

use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\DataObjectHelper;
use Omnyfy\Enquiry\Model\ResourceModel\Enquiries\CollectionFactory as EnquiriesCollectionFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Omnyfy\Enquiry\Model\ResourceModel\Enquiries as ResourceEnquiries;
use Omnyfy\Enquiry\Api\Data\EnquiriesInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;
use Omnyfy\Enquiry\Api\EnquiriesRepositoryInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Exception\CouldNotDeleteException;
use Omnyfy\Enquiry\Api\Data\EnquiriesSearchResultsInterfaceFactory;
use Omnyfy\Enquiry\Model\Enquiries;
use Omnyfy\Enquiry\Helper\Data;
use Omnyfy\Enquiry\Model\EnquiryMessages;
use Magento\Catalog\Model\ProductFactory;

class EnquiriesRepository implements enquiriesRepositoryInterface
{

    protected $dataObjectProcessor;

    protected $storeManager;

    protected $dataObjectHelper;

    protected $dataEnquiriesFactory;

    protected $resource;

    protected $enquiriesFactory;

    protected $searchResultsFactory;

    protected $enquiriesCollectionFactory;

    protected $_enquiries;

    protected $_enquiryData;

    protected $_enquiryMessages;

    protected $_productloader;

    /**
     * @param ResourceEnquiries $resource
     * @param EnquiriesFactory $enquiriesFactory
     * @param EnquiriesInterfaceFactory $dataEnquiriesFactory
     * @param EnquiriesCollectionFactory $enquiriesCollectionFactory
     * @param EnquiriesSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceEnquiries $resource,
        EnquiriesFactory $enquiriesFactory,
        EnquiriesInterfaceFactory $dataEnquiriesFactory,
        EnquiriesCollectionFactory $enquiriesCollectionFactory,
        EnquiriesSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        Enquiries $enquiries,
        Data $enquiryData,
        EnquiryMessages $enquiryMessages,
        ProductFactory $_productloader
    ) {
        $this->resource = $resource;
        $this->enquiriesFactory = $enquiriesFactory;
        $this->enquiriesCollectionFactory = $enquiriesCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataEnquiriesFactory = $dataEnquiriesFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->_enquiries = $enquiries;
        $this->_enquiryData = $enquiryData;
        $this->_enquiryMessages = $enquiryMessages;
        $this->_productloader = $_productloader;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Omnyfy\Enquiry\Api\Data\EnquiriesInterface $enquiries, $enquiryMessages = array()
    ) {


        $currentTime = $this->_enquiryData->getCurrentDateTime();
        $productId   = ($enquiries->getProductId())?$enquiries->getProductId():NULL;
        $customerId  = ($enquiries->getCustomerId())?$enquiries->getCustomerId():NULL;
        $vendorId    = $enquiries->getVendorId();

        try {
            $data = array(
                "vendor_id" => $enquiries->getVendorId(),
                "product_id" => $productId,
                "customer_id" => $customerId,
                "customer_first_name" => $enquiries->getCustomerFirstName(),
                "customer_last_name" => $enquiries->getCustomerLastName(),
                "customer_email" => $enquiries->getCustomerEmail(),
                "customer_mobile" => $enquiries->getCustomerMobile(),
                "customer_company" => $enquiries->getCustomerCompany(),
                "created_date" => $currentTime,
                "updated_date" => $currentTime,
		        "status" => \Omnyfy\Enquiry\Model\Enquiries\Source\Status::NEW_MESSAGE,
                "store_id" => $enquiries->getStoreId()
            );

            $this->_enquiries->setData($data);
            $enquiry = $this->_enquiries->save();

            $__enquiryId = $enquiry->getEnquiriesId();

            if ($__enquiryId) {
                $messageData = array(
                    "enquiry_id" => $__enquiryId,
                    "from_id" => $enquiries->getCustomerId(),
                    "from_type" => $enquiryMessages["from_type"],
                    "to_id" => $vendorId,
                    "to_type" => $enquiryMessages["to_type"],
                    "message" => $enquiryMessages["message"],
                    "send_time" => $currentTime,
                    "is_notify_customer" => $enquiryMessages["is_notify_customer"],
                    "is_visible_frontend" => $enquiryMessages["is_visible_frontend"],
                    "status" => 1
                );
                $this->_enquiryMessages->setData($messageData);
                $this->_enquiryMessages->save();
            }

            /* Send the Enquiry Message to Customer */
            $userDashboard_link = "";//$this->_enquiryData->getDashboardUrl();
            $toEmail = array(
                "email" => $enquiries->getCustomerEmail(),
                "name" => $enquiries->getCustomerFirstName()." ".$enquiries->getCustomerLastName()
            );

            $vars = array(
                "customer" => $enquiries->getCustomerFirstName()." ".$enquiries->getCustomerLastName(),
                "customer_first_name" => $enquiries->getCustomerFirstName(),
                "customer_email" => $enquiries->getCustomerEmail(),
                "customer_mobile" => $enquiries->getCustomerMobile(),
                "customer_company" => $enquiries->getCustomerCompany(),
                "customer_message" => $enquiryMessages["message"],
                "enquiry_link" => $userDashboard_link,
                "service_name" => $this->getProductName($productId)
            );
            $this->_enquiryData->sendEnquiryToCustomer($vars, $toEmail, $vendorId);
            /* Send the Enquiry Message to Customer */

            /*Send the Enquiry Message to Vendor*/
            $this->_enquiryData->sendEnquiryToVendor($vars, $vendorId);
            /*Send the Enquiry Message to Vendor*/


        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the enquiries:',
                $exception->getMessage()
            ));
        }
        return $enquiry;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($enquiriesId)
    {
        $enquiries = $this->enquiriesFactory->create();
        $enquiries->getResource()->load($enquiries, $enquiriesId);
        if (!$enquiries->getId()) {
            throw new NoSuchEntityException(__('enquiries with id "%1" does not exist.', $enquiriesId));
        }
        return $enquiries;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->enquiriesCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Omnyfy\Enquiry\Api\Data\EnquiriesInterface $enquiries
    ) {
        try {
            $enquiries->getResource()->delete($enquiries);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the enquiries: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($enquiriesId)
    {
        return $this->delete($this->getById($enquiriesId));
    }


    public function getProductName($id){

        $product = $this->_productloader->create()->load($id);
        if ($product) {
            return $product->getName();
        }
        return "";
    }
}
