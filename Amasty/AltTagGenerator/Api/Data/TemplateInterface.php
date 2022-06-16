<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface TemplateInterface extends ExtensibleDataInterface
{
    const MAIN_TABLE = 'amasty_alt_template';

    const ID = 'id';
    const ENABLED = 'enabled';
    const TITLE = 'title';
    const PRIORITY = 'priority';
    const REPLACEMENT_LOGIC = 'replacement_logic';
    const TEMPLATE = 'template';
    const CONDITIONS_SERIALIZED = 'conditions_serialized';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int $id
     * @return \Amasty\AltTagGenerator\Api\Data\TemplateInterface
     */
    public function setId($id);

    public function isEnabled(): bool;

    public function setEnabled(bool $enabled): void;

    public function getTitle(): string;

    public function setTitle(string $title): void;

    public function getReplacementLogic(): int;

    public function setReplacementLogic(int $replacementLogic): void;

    public function getTemplate(): string;

    public function setTemplate(string $template): void;

    public function getConditionsSerialized(): ?string;

    public function setConditionsSerialized(string $conditionsSerialized): void;

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Amasty\AltTagGenerator\Api\Data\TemplateExtensionInterface|null
     */
    public function getExtensionAttributes(): ?TemplateExtensionInterface;

    /**
     * Set an extension attributes object.
     *
     * @param \Amasty\AltTagGenerator\Api\Data\TemplateExtensionInterface $extensionAttributes
     *
     * @return void
     */
    public function setExtensionAttributes(TemplateExtensionInterface $extensionAttributes): void;
}
