<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use OmnyfyCustomzation\CmsBlog\Model\Url;

/**
 * Cms author model
 */
class Author extends AbstractModel
{
    /**
     * Initialize dependencies.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Url $url
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Url $url,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_url = $url;
    }

    /**
     * Retrieve author name (used in identifier generation)
     * @return string | null
     */
    public function getTitle()
    {
        return $this->getName();
    }

    /**
     * Retrieve author name
     *
     * @param string $separator
     * @return string
     */
    public function getName($separator = ' ')
    {
        return $this->getFirstname() . $separator . $this->getLastname();
    }

    /**
     * Retrieve author identifier
     * @return string | null
     */
    public function getIdentifier()
    {
        return preg_replace(
            "/[^A-Za-z0-9\-]/",
            '',
            strtolower($this->getName('-'))
        );
    }

    /**
     * Check if author identifier exist
     * return author id if author exists
     *
     * @param string $identifier
     * @return int
     */
    public function checkIdentifier($identifier)
    {
        $authors = $this->getCollection();
        foreach ($authors as $author) {
            if ($author->getIdentifier() == $identifier) {
                return $author->getId();
            }
        }

        return 0;
    }

    /**
     * Retrieve author url route path
     * @return string
     */
    public function getUrl()
    {
        return $this->_url->getUrlPath($this, URL::CONTROLLER_AUTHOR);
    }

    /**
     * Retrieve author url
     * @return string
     */
    public function getAuthorUrl()
    {
        return $this->_url->getUrl($this, URL::CONTROLLER_AUTHOR);
    }

    /**
     * Initialize user model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Author');
    }

}
