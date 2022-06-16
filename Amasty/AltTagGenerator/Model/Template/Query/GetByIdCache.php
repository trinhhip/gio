<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Template\Query;

use Amasty\AltTagGenerator\Api\Data\TemplateInterface;
use Amasty\AltTagGenerator\Model\Template\Registry;
use Magento\Framework\Exception\NoSuchEntityException;

class GetByIdCache implements GetByIdInterface
{
    /**
     * @var GetById
     */
    private $getById;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        GetById $getById,
        Registry $registry
    ) {
        $this->getById = $getById;
        $this->registry = $registry;
    }

    /**
     * @param int $id
     * @return TemplateInterface
     * @throws NoSuchEntityException
     */
    public function execute(int $id): TemplateInterface
    {
        $template = $this->registry->get($id);
        if ($template === null) {
            $template = $this->getById->execute($id);
            $this->registry->save($template);
        }

        return $template;
    }
}
