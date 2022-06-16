<?php

namespace OmnyfyCustomzation\CmsBlog\Controller\Adminhtml\User\Type;

use Magento\Framework\App\Request\Http;
use OmnyfyCustomzation\CmsBlog\Controller\Adminhtml\User\Type;
use OmnyfyCustomzation\CmsBlog\Model\UserType;
use Zend_Filter_Input;

/**
 * Cms user type save controller
 */
class Save extends Type
{

    /**
     * Before model save
     * @param UserType $model
     * @param Http $request
     * @return void
     */
    protected function _beforeSave($model, $request)
    {
        $data = $model->getData();

        $filterRules = [];

        $inputFilter = new Zend_Filter_Input(
            $filterRules, [], $data
        );
        $data = $inputFilter->getUnescaped();
        $model->setData($data);
    }

}
