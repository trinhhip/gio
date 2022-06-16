<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Template\Query;

use Amasty\AltTagGenerator\Api\Data\TemplateInterface;
use Magento\Framework\Exception\NoSuchEntityException;

interface GetByIdInterface
{
    /**
     * @param int $id
     * @return TemplateInterface
     * @throws NoSuchEntityException
     */
    public function execute(int $id): TemplateInterface;
}
