<?php

namespace OmnyfyCustomzation\CmsBlog\Block;

use Magento\Framework\View\Element\Template\Context;
use OmnyfyCustomzation\CmsBlog\Model\Url;

/**
 * Class Link
 */
class Link extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @var Url
     */
    protected $_url;

    /**
     * @param Context $context
     * @param Url $url
     * @param array $data
     */
    public function __construct(
        Context $context,
        Url $url,
        array $data = []
    )
    {
        $this->_url = $url;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->_url->getBaseUrl();
    }
}
