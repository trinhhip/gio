<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Ui\DataProvider\Template\Form\Modifier;

use Amasty\AltTagGenerator\Api\Data\TemplateInterface;
use Amasty\AltTagGenerator\Model\Template\Store\Query\GetByTemplateIdInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class AddStores implements ModifierInterface
{
    const STORES_FIELD = 'stores';

    /**
     * @var GetByTemplateIdInterface
     */
    private $getByTemplateId;

    public function __construct(GetByTemplateIdInterface $getByTemplateId)
    {
        $this->getByTemplateId = $getByTemplateId;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        if (!empty($data[TemplateInterface::ID])) {
            $data[self::STORES_FIELD] = $this->getByTemplateId->execute((int) $data[TemplateInterface::ID]);
        }

        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
