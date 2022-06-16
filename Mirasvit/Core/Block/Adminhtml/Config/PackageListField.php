<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-core
 * @version   1.2.118
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Core\Block\Adminhtml\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Mirasvit\Core\Model\Package;
use Mirasvit\Core\Service\PackageService;
use Mirasvit\Core\Service\ValidationService;

class PackageListField extends Field
{
    private $validationService;

    private $packageService;

    public function __construct(
        ValidationService $validationService,
        PackageService $moduleService,
        Context $context,
        array $data = []
    ) {
        $this->validationService = $validationService;
        $this->packageService    = $moduleService;

        parent::__construct($context, $data);
    }

    public function isMarketplace()
    {
        $flag = true;

        /** mp comment start */

        $flag = false;

        /** mp comment end */

        return $flag;
    }

    /**
     * {@inheritdoc}
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * @return Package[]
     */
    public function getPackageList()
    {
        $packages = [];

        foreach ($this->packageService->getPackageList() as $package) {
            $packages[] = $package;
        }

        usort($packages, function ($a, $b) {
            return strcmp($a->getLabel(), $b->getLabel());
        });

        usort($packages, function ($a, $b) {
            return $b->isOutdated() ? 1 : -1;
        });

        return $packages;
    }

    /**
     * Check whether validator available for that module or not.
     */
    public function isValidationAvailable(Package $package)
    {
        foreach ($this->validationService->getValidators() as $validator) {
            if (in_array($validator->getModuleName(), $package->getModuleList())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get validation URL for given module.
     */
    public function getValidationUrl()
    {
        return $this->getUrl('mstcore/validator/');
    }

    public function getChangelogUrl(Package $package)
    {
        return $package->getChangelogUrl() ? $package->getChangelogUrl() : $package->getUrl();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('Mirasvit_Core::config/package-list-field.phtml');
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
}
