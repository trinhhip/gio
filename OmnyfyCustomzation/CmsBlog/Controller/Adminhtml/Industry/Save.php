<?php
/**
 * Project: CMS Industry M2.
 * User: abhay
 * Date: 01/05/17
 * Time: 2:30 PM
 */

namespace OmnyfyCustomzation\CmsBlog\Controller\Adminhtml\Industry;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use OmnyfyCustomzation\CmsBlog\Controller\Adminhtml\Industry;
use OmnyfyCustomzation\CmsBlog\Model\Article;
use OmnyfyCustomzation\CmsBlog\Model\Category;
use OmnyfyCustomzation\CmsBlog\Model\Country;

/**
 * Cms Industry save controller
 */
class Save extends Industry
{

    /**
     * Before model save
     * @param Category $model
     * @param Http $request
     * @return void
     */
    protected function _beforeSave($model, $request)
    {

        $industry = $model->getCollection()
            ->addFieldToFilter('industry_name', $model->getIndustryName())
            ->addFieldToFilter('id', ['neq' => $model->getId()])
            ->getFirstItem();
        if ($industry->getId()) {
            throw new LocalizedException(
                __('The industry name is already exist.')
            );
        }

        $identifierGenerator = ObjectManager::getInstance()
            ->create('OmnyfyCustomzation\CmsBlog\Model\ResourceModel\PageIdentifierGenerator');
        $identifierGenerator->generate($model);
        $countryResourceModel = ObjectManager::getInstance()
            ->create('OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Industry');
        if (!$countryResourceModel->isValidPageIdentifier($model)) {
            throw new LocalizedException(
                __('The industry URL key contains capital letters or disallowed symbols.')
            );
        }

        if ($countryResourceModel->isNumericPageIdentifier($model)) {
            throw new LocalizedException(
                __('The industry URL key cannot be made of only numbers.')
            );
        }

        /* Prepare images */
        $data = $model->getData();
        foreach (['background_image', 'industry_profile_image'] as $key) {
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
    }

    /**
     * After model save
     * @param Country $model
     * @param Http $request
     * @return void
     */
    protected function _afterSave($model, $request)
    {
//        $model->addData(
//            [
//                'parent_id' => $model->getParentId(),
//                'level' => $model->getLevel(),
//            ]
//        );
    }

}
