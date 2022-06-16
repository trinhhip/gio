<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Omnyfy\RebateUI\Ui\Component\VendorReport;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;

/**
 * DataProvider for cms ui.
 */
class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var AddFilterInterface[]
     */
    private $additionalFilterPool;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Reporting $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param array $meta
     * @param array $data
     * @param array $additionalFilterPool
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Reporting $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        array $meta = [],
        array $data = [],
        array $additionalFilterPool = []
    ) {
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
        $this->additionalFilterPool = $additionalFilterPool;
    }

    protected function prepareUpdateUrl()
    {
        if (!isset($this->data['config']['filter_url_params'])) {
            return;
        }
        foreach ($this->data['config']['filter_url_params'] as $paramName => $paramValue) {
            if ('*' == $paramValue) {
                $paramValue = $this->request->getParam($paramName);
            }
            if ($paramValue) {
                $this->data['config']['update_url'] = sprintf(
                    '%s%s/%s/',
                    $this->data['config']['update_url'],
                    $paramName,
                    $paramValue
                );
                $this->addFilter(
                    $this->filterBuilder->setField($paramName)->setValue($paramValue)->setConditionType('eq')->create()
                );
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function addFilter(Filter $filter)
    {
        if (!empty($this->additionalFilterPool[$filter->getField()])) {
            $this->additionalFilterPool[$filter->getField()]->addFilter($this->searchCriteriaBuilder, $filter);
        } else {
            if ($filter->getField() == "vendor_total_incl_tax") {
                $filter->setField("secondTable." . $filter->getField());
                parent::addFilter($filter);
                return;
            }
            $filter->setField("main_table." . $filter->getField());
            parent::addFilter($filter);
        }
    }
}
