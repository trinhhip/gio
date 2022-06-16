<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Backend\Template\Initialization;

use Amasty\AltTagGenerator\Api\Data\TemplateInterface;
use Amasty\AltTagGenerator\Model\Template;
use Amasty\AltTagGenerator\Model\Template\Condition;
use Amasty\AltTagGenerator\Model\Template\ConditionFactory;
use Amasty\AltTagGenerator\Ui\DataProvider\Template\Form\Modifier\ModifyConditionsContent;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

class TemplateProcessor implements ProcessorInterface
{
    /**
     * @var JsonSerializer
     */
    private $jsonSerializer;

    /**
     * @var ConditionFactory
     */
    private $conditionFactory;

    public function __construct(JsonSerializer $jsonSerializer, ConditionFactory $conditionFactory)
    {
        $this->jsonSerializer = $jsonSerializer;
        $this->conditionFactory = $conditionFactory;
    }

    /**
     * @param TemplateInterface|Template $template
     * @param array $inputTemplateData
     * @return void
     * @throws InputException
     */
    public function execute(TemplateInterface $template, array $inputTemplateData): void
    {
        $template->addData($this->prepareData($inputTemplateData));
    }

    /**
     * @param array $inputTemplateData
     * @return array
     * @throws InputException
     */
    private function prepareData(array $inputTemplateData): array
    {
        $this->validateExisting($inputTemplateData, TemplateInterface::TITLE);
        $this->validateExisting($inputTemplateData, TemplateInterface::PRIORITY);
        $this->validateExisting($inputTemplateData, TemplateInterface::REPLACEMENT_LOGIC);
        $this->validateExisting($inputTemplateData, TemplateInterface::TEMPLATE);
        $this->validateExisting($inputTemplateData, TemplateInterface::ENABLED);

        $data[TemplateInterface::TITLE] = $inputTemplateData[TemplateInterface::TITLE];
        $data[TemplateInterface::TEMPLATE] = $inputTemplateData[TemplateInterface::TEMPLATE];
        $data[TemplateInterface::PRIORITY] = (int) $inputTemplateData[TemplateInterface::PRIORITY];
        $data[TemplateInterface::REPLACEMENT_LOGIC] = (int) $inputTemplateData[TemplateInterface::REPLACEMENT_LOGIC];
        $data[TemplateInterface::ENABLED] = (int) $inputTemplateData[TemplateInterface::ENABLED];
        $conditionsArray = $inputTemplateData[ModifyConditionsContent::FIELD_NAME]['rule'] ?? null;
        if ($conditionsArray) {
            /** @var Condition $condition */
            $condition = $this->conditionFactory->create()->loadPost($conditionsArray);
            $data[TemplateInterface::CONDITIONS_SERIALIZED] = $this->jsonSerializer->serialize(
                $condition->getConditions()->asArray()
            );
        }

        return $data;
    }

    /**
     * @param array $inputData
     * @param string $key
     * @throws InputException
     */
    private function validateExisting(array $inputData, string $key): void
    {
        if (!isset($inputData[$key])) {
            throw new InputException(__('The "%1" value doesn\'t exist. Enter the value and try again.', $key));
        }
    }
}
