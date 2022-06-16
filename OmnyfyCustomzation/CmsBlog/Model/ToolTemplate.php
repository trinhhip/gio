<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use OmnyfyCustomzation\CmsBlog\Model\Url;

/**
 * Tag model
 *
 * @method \OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Tag _getResource()
 * @method \OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Tag getResource()
 * @method string getTitle()
 * @method $this setTitle(string $value)
 * @method string getIdentifier()
 * @method $this setIdentifier(string $value)
 */
class ToolTemplate extends AbstractModel
{

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'omnyfyCustomzation_cmsblog_tool_template';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'cms_tool_template';

    /**
     * @var UrlInterface
     */
    protected $_url;

    /**
     * Initialize dependencies.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Url $url
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Url $url,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->_url = $url;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve model title
     * @param boolean $plural
     * @return string
     */
    public function getOwnTitle($plural = false)
    {
        return $plural ? 'Tools/Templates' : 'Tool/Template';
    }

    /**
     * Retrieve all industry image url
     * @return string
     */
    public function getImage($field)
    {
        if ($file = $this->getData($field)) {
            $image = $this->_url->getMediaUrl($file);
        } else {
            $image = false;
        }
        return $image;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('OmnyfyCustomzation\CmsBlog\Model\ResourceModel\ToolTemplate');
    }
}
