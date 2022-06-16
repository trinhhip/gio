<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Model\Import;

use Exception;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use OmnyfyCustomzation\CmsBlog\Model\ArticleFactory;
use OmnyfyCustomzation\CmsBlog\Model\CategoryFactory;

/**
 * Abstract import model
 */
abstract class AbstractImport extends AbstractModel
{
    /**
     * Connect to bd
     */
    protected $_connect;

    /**
     * @var array
     */
    protected $_requiredFields = [];

    /**
     * @var ArticleFactory
     */
    protected $_articleFactory;

    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var integer
     */
    protected $_importedArticlesCount = 0;

    /**
     * @var integer
     */
    protected $_importedCategoriesCount = 0;

    /**
     * @var array
     */
    protected $_skippedArticles = [];

    /**
     * @var array
     */
    protected $_skippedCategories = [];

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Initialize dependencies.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ArticleFactory $articleFactory ,
     * @param CategoryFactory $categoryFactory ,
     * @param StoreManagerInterface $storeManager ,
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ArticleFactory $articleFactory,
        CategoryFactory $categoryFactory,
        StoreManagerInterface $storeManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->_articleFactory = $articleFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_storeManager = $storeManager;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve import statistic
     * @return DataObject
     */
    public function getImportStatistic()
    {
        return new DataObject([
            'imported_articles_count' => $this->_importedArticlesCount,
            'imported_categories_count' => $this->_importedCategoriesCount,
            'skipped_articles' => $this->_skippedArticles,
            'skipped_categories' => $this->_skippedCategories,
            'imported_count' => $this->_importedArticlesCount + $this->_importedCategoriesCount,
            'skipped_count' => count($this->_skippedArticles) + count($this->_skippedCategories),
        ]);
    }

    /**
     * Prepare import data
     * @param array $data
     * @return $this
     */
    public function prepareData($data)
    {
        if (!is_array($data)) {
            $data = (array)$data;
        }

        foreach ($this->_requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception(__('Parameter %1 is required', $field), 1);
            }
        }

        foreach ($data as $field => $value) {
            if (!in_array($field, $this->_requiredFields)) {
                unset($data[$field]);
            }
        }

        $this->setData($data);

        return $this;
    }

    /**
     * Execute mysql query
     */
    protected function _mysqliQuery($sql)
    {
        $result = mysqli_query($this->_connect, $sql);
        if (!$result) {
            throw new Exception(
                __('Mysql error: %1.', mysqli_error($this->_connect))
            );
        }

        return $result;
    }
}
