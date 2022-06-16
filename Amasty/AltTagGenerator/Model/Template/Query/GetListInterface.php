<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Template\Query;

use Amasty\AltTagGenerator\Api\Data\TemplateInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface GetListInterface
{
    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return TemplateInterface[]
     */
    public function execute(SearchCriteriaInterface $searchCriteria): array;
}
