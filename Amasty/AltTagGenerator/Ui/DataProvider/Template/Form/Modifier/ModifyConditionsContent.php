<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Ui\DataProvider\Template\Form\Modifier;

use Amasty\AltTagGenerator\Api\Data\TemplateInterface;
use Amasty\AltTagGenerator\Model\Template\Condition;
use Amasty\AltTagGenerator\Model\Template\ConditionFactory;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\UrlInterface as UrlBuilder;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class ModifyConditionsContent implements ModifierInterface
{
    const FIELD_NAME = 'conditions';
    const CONDITIONS_ID = 'rule_conditions_fieldset';
    const FORM_NAME = 'amasty_alt_conditions';

    /**
     * @var ConditionFactory
     */
    private $conditionFactory;

    /**
     * @var UrlBuilder
     */
    private $urlBuilder;

    /**
     * @var JsonSerializer
     */
    private $jsonSerializer;

    public function __construct(
        ConditionFactory $conditionFactory,
        UrlBuilder $urlBuilder,
        JsonSerializer $jsonSerializer
    ) {
        $this->conditionFactory = $conditionFactory;
        $this->urlBuilder = $urlBuilder;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        if (isset($data[self::FIELD_NAME]['rule'])) {
            /** @var Condition $condition */
            $condition = $this->conditionFactory->create()->loadPost($data[self::FIELD_NAME]['rule']);
            $data[TemplateInterface::CONDITIONS_SERIALIZED] = $this->jsonSerializer->serialize(
                $condition->getConditions()->asArray()
            );
        }
        $data[self::FIELD_NAME] = $this->prepareConditionModel($data[TemplateInterface::CONDITIONS_SERIALIZED] ?? '')
            ->getConditions()
            ->asHtmlRecursive();

        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $meta['product_selection']['children'][self::FIELD_NAME]['arguments']['data']['config'] =  [
            'newFormChildUrl' => $this->getNewFormChildUrl(),
            'conditionsFormId' => self::CONDITIONS_ID,
        ];

        return $meta;
    }

    private function getNewFormChildUrl(): string
    {
        return $this->urlBuilder->getUrl(
            'amasty_alt/template_edit/newConditionHtml',
            ['form_namespace' => self::FORM_NAME, 'form' => self::CONDITIONS_ID]
        );
    }

    private function prepareConditionModel(string $conditionsSerialized): Condition
    {
        /** @var Condition $condition */
        $condition = $this->conditionFactory->create();
        $condition->setConditions([]);
        $condition->setConditionsSerialized($conditionsSerialized);
        $condition->getConditions()->setJsFormObject(self::CONDITIONS_ID);
        $condition->getConditions()->setFormName(self::FORM_NAME);
        foreach ($condition->getConditions()->getConditions() as $simpleCondition) {
            $simpleCondition->setFormName(self::FORM_NAME);
        }

        return $condition;
    }
}
