<?php
/**
 * Project: CMS Industry M2.
 * User: abhay
 * Date: 01/05/17
 * Time: 2:30 PM
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
 * Country model
 *
 * @method \OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Country _getResource()
 * @method \OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Country getResource()
 * @method string getTitle()
 * @method $this setTitle(string $value)
 * @method string getIdentifier()
 * @method $this setIdentifier(string $value)
 */
class Industry extends AbstractModel
{

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'omnyfyCustomzation_cmsblog_industry';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'cms_industry';

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
        Context $context, Registry $registry, Url $url, AbstractResource $resource = null, AbstractDb $resourceCollection = null, array $data = []
    )
    {
        $this->_url = $url;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Check if country identifier exist for specific store
     * return country id if country exists
     *
     * @param string $identifier
     * @return int
     */
    public function checkIdentifier($identifier)
    {
        return $this->load($identifier)->getId();
    }

    /**
     * Retrieve catgegory url route path
     * @return string
     */
    public function getUrl()
    {
        return $this->_url->getUrlPath($this, URL::CONTROLLER_INDUSTRY);
    }

    /**
     * Retrieve industry url
     * @return string
     */
    public function getIndustryUrl()
    {
        return $this->_url->getUrl($this, URL::CONTROLLER_INDUSTRY);
    }

    /**
     * Retrieve model title
     * @param boolean $plural
     * @return string
     */
    public function getOwnTitle($plural = false)
    {
        return $plural ? 'Industries' : 'Industry';
    }

    public function getTitle($plural = false)
    {
        return $this->getIndustryName();
    }

    /**
     * Retrieve all industry image url
     * @return string
     */
    public function getIndustryImage($field)
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
        $this->_init('OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Industry');
    }

}
