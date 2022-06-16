<?php
namespace OmnyfyCustomzation\VendorGallery\Ui\Component\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Omnyfy\Vendor\Model\Resource\Location\CollectionFactory as LocationCollectionFactory;

/**
 * Class Location
 *
 * @package OmnyfyCustomzation\VendorGallery\Ui\Component\Listing\Column
 */
class Location extends \Omnyfy\VendorGallery\Ui\Component\Listing\Column\Location
{

    /**
     * Get data
     *
     * @param array $item
     * @return string
     */
    protected function prepareItem(array $item)
    {
        $content = '';

		if (count($item['locations']) > 0 ){
			$locationCollection = $this->locationCollectionFactory->create();
			$locationCollection->addFieldToFilter('entity_id', ['in' => $item['locations']]);
			foreach ($locationCollection->getItems() as $location) {
				$content .= $this->escaper->escapeHtml($location->getData('location_name')) . "<br/>";
			}
		}

        return $content;
    }
}
