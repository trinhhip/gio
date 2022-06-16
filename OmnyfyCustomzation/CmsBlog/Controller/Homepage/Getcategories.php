<?php
/**
 * Project: Get Categories
 * Author: seth
 * Date: 22/5/20
 * Time: 12:32 pm
 **/

namespace OmnyfyCustomzation\CmsBlog\Controller\Homepage;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Getcategories extends Action
{
    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * Getcategories constructor.
     * @param Context $context
     * @param CategoryFactory $categoryFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        CategoryFactory $categoryFactory,
        JsonFactory $resultJsonFactory
    )
    {
        parent::__construct($context);
        $this->_categoryFactory = $categoryFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $resultJson = $this->_resultJsonFactory->create();
        $response = ['success' => false, 'categories' => null];
        if ($this->getRequest()->isAjax()) {
            $categories = $this->_categoryFactory->create()->load($this->getRequest()->getParam('category_id'));
            $childrenCategories = $categories->getChildrenCategories($this->getRequest()->getParam('category_id'));
            if (!empty($childrenCategories)) {
                $subcategories[] = ['url' => '', 'name' => 'Select Sub Category'];
                foreach ($childrenCategories as $category) {
                    $subcategories[] = ['url' => $category->getUrl(), 'name' => $category->getName()];
                }
                $response = [
                    'success' => true,
                    'categories' => $subcategories
                ];
            }
        }
        return $resultJson->setData($response);
    }
}
