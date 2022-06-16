<?php
/**
 * Created by PhpStorm.
 * User: Sanjaya-offline
 * Date: 5/16/2018
 * Time: 5:49 PM
 */

namespace Omnyfy\Enquiry\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Catalog\Model\ProductFactory;

class ProductName extends Column
{
    protected $_productFactory;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     * @param UserFactory $userFactory
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = [],
        ProductFactory $productFactory
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_productFactory = $productFactory;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if ($item[$fieldName] != '') {
                    $productName = $this->getProductName($item[$fieldName]);
                    $item[$fieldName] = $productName;
                } else {
                    $item[$fieldName] = 'N/A';
                }
            }
        }
        return $dataSource;
    }

    /**
     * @param $userId
     * @return string
     */
    private function getProductName($productId)
    {
        $product = $this->_productFactory->create()->load($productId);
        $name = $product->getName();
        return $name;
    }
}