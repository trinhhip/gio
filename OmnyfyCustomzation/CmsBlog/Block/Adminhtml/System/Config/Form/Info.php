<?php
/**
 * Copyright © 2015 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Adminhtml\System\Config\Form;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\ModuleListInterface;

/**
 * Admin cms configurations information block
 */
class Info extends Field
{
    /**
     * @var ModuleListInterface
     */
    protected $moduleList;

    /**
     * @param ModuleListInterface $moduleList
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        ModuleListInterface $moduleList,
        Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->moduleList = $moduleList;
    }

    /**
     * Return info block html
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $m = $this->moduleList->getOne($this->getModuleName());
        return '<div style="padding:10px;background-color:#f8f8f8;border:1px solid #ddd;margin-bottom:7px;">
            Cms Extension v' . $m['setup_version'] . ' was developed by <a href="http://omnyfy.com/" target="_blank">Omnyfy</a>.
        </div>';
    }

}
