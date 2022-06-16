<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

declare(strict_types=1);

namespace Amasty\Orderattr\Model\Value\Metadata\Form;

use Amasty\Orderattr\Model\Value\Metadata\Form\File\Uploader;
use Magento\Eav\Model\Attribute\Data\File as EavFile;
use Magento\Eav\Model\AttributeDataFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;

class File extends EavFile
{
    const RULES_PREPARED = 'rules_prepared';

    /**
     * @var array
     */
    private $filesSaved;

    /**
     * @var array
     */
    private $errors;

    /**
     * @var Uploader
     */
    private $fileUploader;

    /**
     * @param RequestInterface $request
     * @return array|bool|string|
     */
    public function extractValue(RequestInterface $request)
    {
        $value = parent::extractValue($request);

        if (is_array($value)) {
            $this->saveFileFromOldForm($value);
            $value = $this->getEntity()->getData($this->getAttribute()->getAttributeCode());
        }
        if (!$value) {
            $attrCode = $this->getAttribute()->getAttributeCode();
            $value = $request->getParam($attrCode);
        }

        return $value;
    }

    /**
     * @param array|string $value
     * @return $this
     */
    public function restoreValue($value)
    {
        if ($value) {
            $this->getEntity()->setData($this->getAttribute()->getAttributeCode(), $value);
        }
        return $this;
    }

    /**
     * @param string $format
     * @return array|string
     */
    public function outputValue($format = AttributeDataFactory::OUTPUT_FORMAT_TEXT)
    {
        $value = parent::outputValue($format);
        if (!$value) {
            $value = $this->getEntity()->getData($this->getAttribute()->getAttributeCode());
            if ($format == AttributeDataFactory::OUTPUT_FORMAT_HTML && $value) {
                $value = $this->getFileUploader()->getFileInfo($value);
            }

        }
        return $value;
    }

    /**
     * @param array|string $value
     * @return array|bool
     */
    public function validateValue($value)
    {
        $this->prepareValidationRules();
        $result = parent::validateValue($value);

        if ($result !== true) {
            return $result;
        }

        if ($this->errors) {
            return $this->errors;
        }

        return $result;
    }

    /**
     * Validate temporary file value
     *
     * @param string[] $value
     * @return string[]
     */
    public function validateTmpValue(array $value): array
    {
        $this->prepareValidationRules();

        return $this->_validateByRules($value);
    }

    /**
     * @return Uploader
     */
    private function getFileUploader()
    {
        if ($this->fileUploader === null) {
            $this->fileUploader = ObjectManager::getInstance()->get(Uploader::class);
        }

        return $this->fileUploader;
    }

    /**
     * Prepare validation rules
     */
    private function prepareValidationRules()
    {
        if (!$this->getAttribute()->getData(self::RULES_PREPARED)) {
            $validateRules = $this->getAttribute()->getValidateRules();
            if (!empty($validateRules['max_file_size'])) {
                $validateRules['max_file_size'] = (int)$validateRules['max_file_size'] * 1024 * 1024;
            }
            $this->getAttribute()
                ->setValidateRules($validateRules)
                ->setData(self::RULES_PREPARED, true);
        }
    }

    /**
     * Save file from old form
     *
     * @param string[] $value
     */
    private function saveFileFromOldForm($value)
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();

        if (!isset($this->filesSaved[$attributeCode])) {
            $validationResult = $this->validateValue($value);

            if ($validationResult === true) {
                $this->compactValue($value);
                $this->filesSaved[$attributeCode] = true;
            } else {
                $this->errors = (array)$validationResult;
            }
        }
    }
}
