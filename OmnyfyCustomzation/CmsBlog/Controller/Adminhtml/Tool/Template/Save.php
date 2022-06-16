<?php

namespace OmnyfyCustomzation\CmsBlog\Controller\Adminhtml\Tool\Template;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use OmnyfyCustomzation\CmsBlog\Controller\Adminhtml\Tool\Template;
use OmnyfyCustomzation\CmsBlog\Model\Article;
use OmnyfyCustomzation\CmsBlog\Model\ToolTemplate;

/**
 * Cms user type save controller
 */
class Save extends Template
{

    /**
     * Before model save
     * @param ToolTemplate $model
     * @param Http $request
     * @return void
     */
    protected function _beforeSave($model, $request)
    {
        $data = $model->getData();

        $tools = $model->getCollection()
            ->addFieldToFilter('title', $model->getTitle())
            ->addFieldToFilter('id', ['neq' => $model->getId()])
            ->getFirstItem();
        if ($tools->getId()) {
            throw new LocalizedException(
                __('The tool & template name is already exist.')
            );
        }

        /* $filterRules = [];

        $inputFilter = new \Zend_Filter_Input(
                $filterRules, [], $data
        );
        $data = $inputFilter->getUnescaped(); */
        /* Prepare images */
        $data = $model->getData();
        foreach (['icon', 'upload_template'] as $key) {
            if (isset($data[$key]) && is_array($data[$key])) {
                if (!empty($data[$key]['delete'])) {
                    $model->setData($key, null);
                } else {
                    if (isset($data[$key][0]['name']) && isset($data[$key][0]['tmp_name'])) {
                        $image = $data[$key][0]['name'];

                        $model->setData($key, Article::BASE_MEDIA_PATH . DIRECTORY_SEPARATOR . $image);

                        $imageUploader = $this->_objectManager->get(
                            'OmnyfyCustomzation\CmsBlog\ImageUpload'
                        );
                        $imageUploader->moveFileFromTmp($image);
                    } else {
                        if (isset($data[$key][0]['name'])) {
                            $model->setData($key, $data[$key][0]['name']);
                        }
                    }
                }
            } else {
                $model->setData($key, null);
            }
        }
        #$model->setData($data);
    }

}
