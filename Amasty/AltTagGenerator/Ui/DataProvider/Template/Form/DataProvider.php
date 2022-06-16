<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Ui\DataProvider\Template\Form;

use Amasty\AltTagGenerator\Api\Data\TemplateInterface;
use Amasty\AltTagGenerator\Controller\Adminhtml\Template\Save;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @var array|null
     */
    private $loadedData;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var PoolInterface
     */
    private $pool;

    public function __construct(
        DataPersistorInterface $dataPersistor,
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        PoolInterface $pool,
        array $meta = [],
        array $data = []
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
        $this->dataPersistor = $dataPersistor;
        $this->pool = $pool;
    }

    /**
     * @return array|null
     * @throws LocalizedException
     */
    public function getData()
    {
        if ($this->loadedData === null) {
            $templateData = $this->dataPersistor->get(Save::RULE_PERSISTENT_NAME);
            if ($templateData) {
                $this->dataPersistor->clear(Save::RULE_PERSISTENT_NAME);
                $id = $templateData[TemplateInterface::ID] ?? null;
                $this->loadedData[$id]['template'] = $this->modifyData($templateData);
            } else {
                foreach ($this->getSearchResult()->getItems() as $rule) {
                    $data = $rule->getData();
                    $this->loadedData[$rule->getId()]['template'] = $this->modifyData($data);
                }
                if ($this->loadedData === null) {
                    $this->loadedData[null]['template'] = $this->getEmptyItem();
                }
            }
        }

        return $this->loadedData;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getMeta()
    {
        $meta = parent::getMeta();

        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }

    private function modifyData(array $data): array
    {
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $data = $modifier->modifyData($data);
        }

        return $data;
    }

    private function getEmptyItem(): array
    {
        return  $this->modifyData([]);
    }
}
