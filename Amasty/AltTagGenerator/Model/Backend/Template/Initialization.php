<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Backend\Template;

use Amasty\AltTagGenerator\Api\Data\TemplateInterface;
use Amasty\AltTagGenerator\Model\Backend\Template\Initialization\ProcessorInterface;
use Amasty\AltTagGenerator\Model\Template\Query\GetByIdInterface;
use Amasty\AltTagGenerator\Model\Template\Query\GetNewInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Initialization
{
    /**
     * @var GetByIdInterface
     */
    private $getById;

    /**
     * @var GetNewInterface
     */
    private $getNew;

    /**
     * @var ProcessorInterface[]
     */
    private $processors;

    public function __construct(
        GetByIdInterface $getById,
        GetNewInterface $getNew,
        array $processors = []
    ) {
        $this->getById = $getById;
        $this->getNew = $getNew;
        $this->processors = $processors;
    }

    /**
     * @param array $inputTemplateData
     *
     * @return TemplateInterface
     * @throws NoSuchEntityException
     * @throws InputException
     * @throws LocalizedException
     */
    public function execute(array $inputTemplateData): TemplateInterface
    {
        $templateId = isset($inputTemplateData[TemplateInterface::ID])
            ? (int) $inputTemplateData[TemplateInterface::ID]
            : null;
        $template = $templateId ? $this->getById->execute($templateId) : $this->getNew->execute();
        foreach ($this->processors as $processor) {
            $processor->execute($template, $inputTemplateData);
        }

        return $template;
    }
}
