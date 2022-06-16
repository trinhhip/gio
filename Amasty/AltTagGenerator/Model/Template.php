<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model;

use Amasty\AltTagGenerator\Api\Data\TemplateExtensionInterface;
use Amasty\AltTagGenerator\Api\Data\TemplateInterface;
use Amasty\AltTagGenerator\Model\ResourceModel\Template as TemplateResource;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class Template extends AbstractExtensibleModel implements TemplateInterface, IdentityInterface
{
    const CACHE_TAG = 'amasty_alt_template';

    /**
     * @var string
     */
    protected $_eventPrefix = 'amasty_alt_template';

    /**
     * @var string
     */
    protected $_eventObject = 'template';

    public function _construct()
    {
        $this->_init(TemplateResource::class);
    }

    public function isEnabled(): bool
    {
        return (bool) $this->_getData(TemplateInterface::ENABLED);
    }

    public function setEnabled(bool $enabled): void
    {
        $this->setData(TemplateInterface::ENABLED, $enabled);
    }

    public function getTitle(): string
    {
        return $this->_getData(TemplateInterface::TITLE);
    }

    public function setTitle(string $title): void
    {
        $this->setData(TemplateInterface::TITLE, $title);
    }

    public function getReplacementLogic(): int
    {
        return (int) $this->_getData(TemplateInterface::REPLACEMENT_LOGIC);
    }

    public function setReplacementLogic(int $replacementLogic): void
    {
        $this->setData(TemplateInterface::REPLACEMENT_LOGIC, $replacementLogic);
    }

    public function getTemplate(): string
    {
        return $this->_getData(TemplateInterface::TEMPLATE);
    }

    public function setTemplate(string $template): void
    {
        $this->setData(TemplateInterface::TEMPLATE, $template);
    }

    public function getConditionsSerialized(): ?string
    {
        return $this->_getData(TemplateInterface::CONDITIONS_SERIALIZED);
    }

    public function setConditionsSerialized(string $conditionsSerialized): void
    {
        $this->setData(TemplateInterface::CONDITIONS_SERIALIZED, $conditionsSerialized);
    }

    private function initExtensionAttributes(): void
    {
        $extensionAttributes = $this->extensionAttributesFactory->create(TemplateInterface::class, []);
        $this->_setExtensionAttributes($extensionAttributes);
    }

    public function getExtensionAttributes(): ?TemplateExtensionInterface
    {
        if (!$this->hasData(self::EXTENSION_ATTRIBUTES_KEY)) {
            $this->initExtensionAttributes();
        }

        return $this->_getExtensionAttributes();
    }

    public function setExtensionAttributes(TemplateExtensionInterface $extensionAttributes): void
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
