<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Omnyfy\Vendor\Ui\DataProvider;

use Magento\Backend\Model\Session;
use Magento\Backend\Model\UrlInterface;
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
use Magento\Framework\App\Response\RedirectInterface;

/**
 * Data provider for admin source grid.
 *
 * @api
 */
class SourceDataProvider extends \Magento\InventoryAdminUi\Ui\DataProvider\SourceDataProvider
{

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;

    /**
     * @var Session
     */
    private $session;

    /**
     * Total source count.
     *
     * @var int
     */
    private $sourceCount;

    /**
     * @var PoolInterface
     */
    private $pool;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param SourceRepositoryInterface $sourceRepository
     * @param SearchResultFactory $searchResultFactory
     * @param Session $session
     * @param array $meta
     * @param array $data
     * @param PoolInterface|null $pool
     * @SuppressWarnings(PHPMD.ExcessiveParameterList) All parameters are needed for backward compatibility
     */
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
        RedirectInterface $redirect,
        array $meta = [],
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->sourceRepository = $sourceRepository;
        $this->searchResultFactory = $searchResultFactory;
        $this->session = $session;
        $this->pool = $pool ?: ObjectManager::getInstance()->get(PoolInterface::class);

        $selectVendorId = $request->getParam('vendor_id');
        if ($selectVendorId) {
            $data['config']['filter_url_params']['vendor_id'] = $selectVendorId;
        }

        $vendorInfo = $session->getVendorInfo();
        if($vendorInfo) {
            $vendorId = $vendorInfo['vendor_id'];
            $data['config']['filter_url_params']['vendor_id'] = $vendorId;
        }
        parent::__construct($name, $primaryFieldName, $requestFieldName, $reporting, $searchCriteriaBuilder, $request, $filterBuilder, $sourceRepository, $searchResultFactory, $session, $meta, $data, $pool);
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
            $vendorId = $this->collectionFactory->create()->addFieldToFilter('source_code', $sourceCode)->getFirstItem()->getVendorId();
            $itemData['vendor_id'] = $vendorId;
            $arrItems['items'][] = $itemData;
        }

        $arrItems['totalRecords'] = $searchResult->getTotalCount();

        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $arrItems = $modifier->modifyData($arrItems);
        }

        return $arrItems;
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta()
    {
        $meta = parent::getMeta();

        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }


    /**
     * Get total sources count, without filter be source name.
     *
     * Get total sources count, without filter in order to ui/grid/columns/multiselect::updateState()
     * works correctly with sources selection.
     *
     * @return int
     */
    private function getSourcesCount(): int
    {
        if (!$this->sourceCount) {
            $this->sourceCount = $this->sourceRepository->getList()->getTotalCount();
        }

        return $this->sourceCount;
    }
}
