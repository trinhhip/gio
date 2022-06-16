<?php
/**
 * Project: CMS M2.
 * User: abhay
 * Date: 09/08/18
 * Time: 11:30 AM
 */

namespace OmnyfyCustomzation\CmsBlog\Block\Country;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use OmnyfyCustomzation\CmsBlog\Model\ResourceModel\Country\CollectionFactory;

class Popular extends Template implements BlockInterface
{
    protected $_countryCollectionFactory;

    public function __construct(
        Context $context,
        CollectionFactory $countryCollectionFactory,
        array $data = []
    )
    {
        $this->_countryCollectionFactory = $countryCollectionFactory;

        parent::__construct($context, $data);
    }

    public function getCollection()
    {
        $collection = $this->_countryCollectionFactory->create()->addFieldToSelect('*');
        $collection->addFieldToFilter('status', '1');
        $collection->setOrder('visitiors', 'desc');
        $collection->setPageSize(5);

        return $collection;
    }

    public function getCountryUrl($countryId)
    {
        return $this->getUrl('cms/country/view', ['id' => $countryId]);
    }
}
