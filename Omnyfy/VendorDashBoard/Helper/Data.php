<?php
/**
 * Created by PhpStorm.
 * User: Sanjaya-offline
 * Date: 5/09/2019
 * Time: 9:49 AM
 */

namespace Omnyfy\VendorDashBoard\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\Dir;
use Mirasvit\Dashboard\Api\Data\BoardInterface;
use Mirasvit\Dashboard\Api\Repository\BoardRepositoryInterface as BoardRepository;

class Data extends AbstractHelper
{
    /**
     * @var \Omnyfy\Vendor\Model\Resource\Vendor\CollectionFactory
     */
    protected $_vendorCollectionFactory;
 
    /**
     * @var \Mirasvit\Dashboard\Model\ResourceModel\Board\CollectionFactory
     */
    protected $_dashboardCollectionFactory;

    /**
     * @var \Mirasvit\Dashboard\Model\Board
     */
    protected $_board;

    protected $_resources;
    /**
     * @var Dir
     */
    protected $moduleDir;

    protected BoardRepository $boardRepository;

    public function __construct(
        Context $context,
        \Omnyfy\Vendor\Model\Resource\Vendor\CollectionFactory $vendorCollectionFactory,
        \Mirasvit\Dashboard\Model\ResourceModel\Board\CollectionFactory $dashboardCollectionFactory,
        \Mirasvit\Dashboard\Model\Board $board,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        Dir $moduleDir,
        BoardRepository $boardRepository
    )
    {
        $this->_vendorCollectionFactory = $vendorCollectionFactory;
        $this->_dashboardCollectionFactory = $dashboardCollectionFactory;
        $this->_board = $board;
        $this->_resources = $resourceConnection;
        parent::__construct($context);
        $this->moduleDir = $moduleDir;
        $this->boardRepository = $boardRepository;
    }

    /**
     * Return all the vendors
     * @return \Omnyfy\Vendor\Model\Resource\Vendor\Collection
     */
    protected function getVendors(){
        /** @var \Omnyfy\Vendor\Model\Resource\Vendor\Collection $collection */
        $collection = $this->_vendorCollectionFactory->create();
        $collection->addFieldToFilter('status', ['eq' => 1]);
        $collection->getSelect()->join(
            ['au' => 'admin_user'],
            'au.email = e.email',
            ['user_id' => 'au.user_id']
        );
        return $collection;
    }

    /**
     * @return \Magento\Framework\DataObject
     * @throws \Exception
     */
    protected function getDefaultDashboard(){
        /** @var \Mirasvit\Dashboard\Model\ResourceModel\Board\Collection $collection */
        $collection = $this->_dashboardCollectionFactory->create();
        $collection->addFieldToFilter('title',['eq' => 'vendor_dashboard_default']);
        if ($collection->count() == 1)
            return $collection->getFirstItem();

        $defaultBoard = $this->generateDashBoard();

        if ($defaultBoard->getId()) {
            return $defaultBoard;
        }

        throw new \Exception('Default Vendor Dashboard is not created. Please create one before generate.');
    }

    /**
     * @return \Mirasvit\Dashboard\Model\Board
     */
    protected function generateDashBoard(): \Mirasvit\Dashboard\Model\Board
    {
        /* @var \Mirasvit\Dashboard\Model\Board $board  */
        $board = $this->boardRepository->create();

        // TODO: the default blocks serialized data should be configurable
        // TODO: What is the value of Identifier?
        // TODO: the value "vendor_dashboard_default" should be a constant
        // TODO: title is NOT in any indexes, shouldn't use it as a keyword to search(see self::getDefaultDashboard())
        $board->setIdentifier('debd8d11574f856a')
            ->setTitle('vendor_dashboard_default')
            ->setIsDefault(1)
            ->setType(BoardInterface::TYPE_PRIVATE)
            ->setData(BoardInterface::BLOCKS_SERIALIZED, '[{\"identifier\":\"2b5f76e05a25d85f\",\"title\":\"Total Sales Excluding Fees\",\"size\":[10,11],\"pos\":[29,0],\"description\":\"\",\"config\":{\"renderer\":\"single\",\"filters\":[{\"column\":\"sales_order_item|vendor_id\",\"condition_type\":\"eq\",\"value\":\"0000\"}],\"single\":{\"column\":\"sales_order_item|row_total__sum\",\"spark_line\":true,\"compare\":\"\"},\"table\":{\"columns\":[],\"dimensions\":[],\"sort_orders\":[],\"page_size\":30},\"chart\":{\"dimension\":\"\",\"columns\":[],\"compare\":\"\"},\"date_range\":{\"override\":false,\"range\":\"\"}}},{\"identifier\":\"f8c0adaec9a453d3\",\"title\":\"Number of Orders\",\"size\":[10,11],\"pos\":[29,10],\"description\":\"\",\"config\":{\"renderer\":\"single\",\"filters\":[{\"column\":\"sales_order_item|vendor_id\",\"condition_type\":\"eq\",\"value\":\"0000\"}],\"single\":{\"column\":\"sales_order|entity_id__cnt\",\"spark_line\":true,\"compare\":\"\"},\"table\":{\"columns\":[],\"dimensions\":[],\"sort_orders\":[],\"page_size\":30},\"chart\":{\"dimension\":\"\",\"columns\":[],\"compare\":\"\"},\"date_range\":{\"override\":false,\"range\":\"\"}}},{\"identifier\":\"1ff846cc9b925fd1\",\"title\":\"Sales - Recent Orders\",\"size\":[20,30],\"pos\":[40,0],\"description\":\"\",\"config\":{\"renderer\":\"table\",\"filters\":[{\"column\":\"sales_order_item|vendor_id\",\"condition_type\":\"eq\",\"value\":\"0000\"}],\"single\":{\"column\":\"\",\"spark_line\":false,\"compare\":\"\"},\"table\":{\"columns\":[\"sales_order|increment_id\",\"sales_order_item|updated_at\",\"sales_order_item|sku\",\"sales_order_item|name\",\"sales_order_item|qty_ordered\",\"sales_order_item|discount_amount\",\"sales_order_item|row_total_incl_tax\",\"sales_order|customer_name\",\"sales_order|customer_is_guest\",\"sales_order_item|qty_invoiced\",\"sales_order_item|qty_refunded\",\"sales_order_item|amount_refunded\",\"sales_order_item|qty_canceled\"],\"dimensions\":[\"sales_order_item|order_id\"],\"sort_orders\":[],\"page_size\":30},\"chart\":{\"dimension\":\"\",\"columns\":[],\"compare\":\"\"},\"date_range\":{\"override\":false,\"range\":\"\"}}},{\"identifier\":\"5ca60e400e35436d\",\"title\":\"Fees and Earnings Per Order\",\"size\":[20,29],\"pos\":[70,0],\"description\":\"\",\"config\":{\"renderer\":\"table\",\"filters\":[{\"column\":\"omnyfy_mcm_vendor_order|vendor_id\",\"condition_type\":\"eq\",\"value\":\"0000\"}],\"single\":{\"column\":\"\",\"spark_line\":true,\"compare\":\"\"},\"table\":{\"columns\":[\"omnyfy_mcm_vendor_order|order_increment_id\",\"omnyfy_mcm_vendor_order|grand_total\",\"omnyfy_mcm_vendor_order|total_category_fee\",\"omnyfy_mcm_vendor_order|disbursement_fee\",\"omnyfy_mcm_vendor_order|total_seller_fee\",\"omnyfy_mcm_vendor_order|payout_amount\",\"omnyfy_mcm_vendor_order|payout_status\"],\"dimensions\":[\"omnyfy_mcm_vendor_order|order_id\"],\"sort_orders\":[],\"page_size\":30},\"chart\":{\"dimension\":\"\",\"columns\":[],\"compare\":\"\"},\"date_range\":{\"override\":false,\"range\":\"\"}}},{\"identifier\":\"ff6128fb9623c5ee\",\"title\":\"Total Seller Fee\",\"size\":[7,37],\"pos\":[99,0],\"description\":\"\",\"config\":{\"renderer\":\"chart\",\"filters\":[{\"column\":\"omnyfy_mcm_vendor_order|vendor_id\",\"condition_type\":\"eq\",\"value\":\"0000\"}],\"single\":{\"column\":\"\",\"spark_line\":false,\"compare\":\"\"},\"table\":{\"columns\":[],\"dimensions\":[],\"sort_orders\":[],\"page_size\":30},\"chart\":{\"dimension\":\"sales_order|updated_at\",\"columns\":[\"omnyfy_mcm_vendor_order|total_seller_fee\"],\"compare\":\"\"},\"date_range\":{\"override\":false,\"range\":\"\"}}},{\"identifier\":\"502db313685f6e98\",\"title\":\"Total Disbursement Fee\",\"size\":[6,37],\"pos\":[99,7],\"description\":\"\",\"config\":{\"renderer\":\"chart\",\"filters\":[{\"column\":\"sales_order_item|vendor_id\",\"condition_type\":\"eq\",\"value\":\"0000\"}],\"single\":{\"column\":\"\",\"spark_line\":false,\"compare\":\"\"},\"table\":{\"columns\":[],\"dimensions\":[],\"sort_orders\":[],\"page_size\":30},\"chart\":{\"dimension\":\"sales_order|updated_at\",\"columns\":[\"omnyfy_mcm_vendor_order|disbursement_fee\"],\"compare\":\"\"},\"date_range\":{\"override\":false,\"range\":\"\"}}},{\"identifier\":\"e34b228e0ecf871e\",\"title\":\"Total Category Fee\",\"size\":[7,37],\"pos\":[99,13],\"description\":\"\",\"config\":{\"renderer\":\"chart\",\"filters\":[{\"column\":\"sales_order_item|vendor_id\",\"condition_type\":\"eq\",\"value\":\"0000\"}],\"single\":{\"column\":\"\",\"spark_line\":false,\"compare\":\"\"},\"table\":{\"columns\":[],\"dimensions\":[],\"sort_orders\":[],\"page_size\":30},\"chart\":{\"dimension\":\"sales_order|updated_at\",\"columns\":[\"omnyfy_mcm_vendor_order|total_category_fee\"],\"compare\":\"\"},\"date_range\":{\"override\":false,\"range\":\"\"}}},{\"identifier\":\"81900009f6116141\",\"title\":\"Sales Over Time\",\"size\":[20,29],\"pos\":[0,0],\"description\":\"\",\"config\":{\"renderer\":\"chart\",\"filters\":[{\"column\":\"sales_order_item|vendor_id\",\"condition_type\":\"eq\",\"value\":\"0000\"}],\"single\":{\"column\":\"sales_order_item|row_total__sum\",\"spark_line\":false,\"compare\":\"\"},\"table\":{\"columns\":[],\"dimensions\":[],\"sort_orders\":[],\"page_size\":30},\"chart\":{\"dimension\":\"sales_order|created_at__day\",\"columns\":[\"sales_order_item|row_total__sum\",\"sales_order_item|order_id__cnt\"],\"compare\":\"\"},\"date_range\":{\"override\":false,\"range\":\"\"}}}]');
            $board->save();
        return $board;
    }

    /**
     * @param \Mirasvit\Dashboard\Model\Board $defaultBoard
     * @param $id
     * @param $name
     * @param $userId
     * @throws \Exception
     */
    protected function getVendorBoard($defaultBoard, $id, $name, $userId){
        try {
            $defaultBoard->setData('board_id', $this->isVendorBoard($userId));
            $defaultBoard->setData('title', 'Vendor Dashboard for ' . $name);
            $defaultBoard->setData('user_id', $userId);
            $defaultBoard->setData('is_default', 1);

            $json = $defaultBoard->getData('blocks_serialized');

            $defaultBoard->setData('blocks_serialized', $this->updateJson($json, $id));
            $defaultBoard->save();
        } catch (\Exception $exception){
            throw new \Exception('Error saving board for: '.$name);
        }
    }

    /**
     * @param $json
     * @param $userId
     */
    protected function updateJson($json, $userId){
        return str_replace("0000",$userId, $json);
    }

    /**
     * @param $userId
     * @return null
     */
    protected function isVendorBoard($userId){
        /** @var \Mirasvit\Dashboard\Model\ResourceModel\Board\Collection $collection */
        $collection = $this->_dashboardCollectionFactory->create();
        $collection->addFieldToFilter('user_id',['eq' => $userId]);
        if ($collection->count() > 0){
            return $collection->getFirstItem()->getId();
        }
        return null;
    }


    /**
     * @throws \Exception
     */
    public function generateDashBoards(){
        $this->_logger->debug(">> Start generating boards");
        $vendors = $this->getVendors();

        foreach ($vendors as $vendor){
            /** @var \Mirasvit\Dashboard\Model\Board $defaultBoard */
            $defaultBoard = $this->getDefaultDashboard();

            $name = $vendor->getData('name');
            $id = $vendor->getData('entity_id');
            $userId = $vendor->getData('user_id');
            $this->getVendorBoard($defaultBoard, $id, $name, $userId);
        }
    }

    public function addDefaultDashBoards($userId1, $userId2){
        try {
            $moduleSetupPath = $this->moduleDir->getDir('Omnyfy_VendorDashBoard', Dir::MODULE_SETUP_DIR);
            if($market_script = fopen($moduleSetupPath."/market_place_dashboard_script.txt", "r")){
                $data1 = fread($market_script,filesize($moduleSetupPath."/market_place_dashboard_script.txt"));
                $sql_market_delete = "Delete from `mst_dashboard_board` WHERE title = 'Marketplace Dashboard'";
                $sql_market_insert = str_replace('XXX', $userId1, $data1);
                $connection= $this->_resources->getConnection();
                $connection->query($sql_market_delete);
                $connection->query($sql_market_insert);
                fclose($market_script);
            } else {
               echo ('Not found file');
            }

            if($default_script = fopen($moduleSetupPath."/vendor_dashboard_default_script.txt", "r")){
                $data2 = fread($default_script,filesize($moduleSetupPath."/vendor_dashboard_default_script.txt"));

                $sql_default_delete = "Delete from `mst_dashboard_board` WHERE title = 'vendor_dashboard_default'";
                $sql_default_insert = str_replace('XXX', $userId2, $data2);
                $connection2= $this->_resources->getConnection();
                $connection2->query($sql_default_delete);
                $connection2->query($sql_default_insert);
                fclose($market_script);
            } else {
                echo ('Not found file');
            }

        } catch (\Exception $exception){
            echo ($exception->getMessage());
        }

    }
}
