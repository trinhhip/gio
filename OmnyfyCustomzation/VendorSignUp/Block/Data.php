<?php
namespace OmnyfyCustomzation\VendorSignUp\Block;

/**
 * Class Data
 *
 * @package OmnyfyCustomzation\VendorSignUp\Block
 */
class Data extends \Magento\Directory\Block\Data
{
    private $serializer;

    /**
     * @return \Magento\Framework\Serialize\SerializerInterface|mixed
     */
    private function getSerializer()
    {
        if ($this->serializer === null) {
            $this->serializer = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Serialize\SerializerInterface::class);
        }
        return $this->serializer;
    }

    /**
     * @return \Magento\Directory\Model\ResourceModel\Country\Collection|mixed
     */
    public function getCountryCollection()
    {
        $collection = $this->getData('country_collection_all');
        if ($collection === null) {
            $collection = $this->_countryCollectionFactory->create();
            $this->setData('country_collection_all', $collection);
        }

        return $collection;
    }

    /**
     * @param null $defValue
     * @param string $name
     * @param string $id
     * @param string $title
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCountryHtmlSelect($defValue = null, $name = 'country_id', $id = 'country', $title = 'Country')
    {
        if ($defValue === null) {
            $defValue = $this->getCountryId();
        }
        $cacheKey = 'DIRECTORY_COUNTRY_SELECT_' . $this->_storeManager->getStore()->getCode();
        $cache = $this->_configCacheType->load($cacheKey);
        if ($cache) {
            $options = $this->getSerializer()->unserialize($cache);
        } else {
            $options = $this->getCountryCollection()
                ->setForegroundCountries($this->getTopDestinations())
                ->toOptionArray();
            $this->_configCacheType->save($this->getSerializer()->serialize($options), $cacheKey);
        }
        $html = $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Html\Select::class
        )->setName(
            $name
        )->setId(
            $id
        )->setTitle(
            __($title)
        )->setValue(
            $defValue
        )->setOptions(
            $options
        )->setExtraParams(
            'data-validate="{\'validate-select\':true}"'
        )->getHtml();

        return $html;
    }

}