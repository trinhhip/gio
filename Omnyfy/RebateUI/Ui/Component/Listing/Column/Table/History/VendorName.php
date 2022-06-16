<?php
namespace Omnyfy\RebateUI\Ui\Component\Listing\Column\Table\History;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\App\RequestInterface;
use Omnyfy\Vendor\Api\VendorRepositoryInterface;

/**
 * Class Actions
 * @package Omnyfy\RebateUI\Ui\Component\Listing\Column\Table\History
 */
class VendorName extends Column
{
    /**
     * @var calculation
     */
    protected $calculation;

    /**
     * @var RequestInterface
     */
    protected $request;

    protected $helper;

    /**
     * @var VendorRepositoryInterface
     */
    protected $vendorRepository;


    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        RequestInterface $request,
        UiComponentFactory $uiComponentFactory,
        VendorRepositoryInterface $vendorRepository,
        array $components = [],
        array $data = []
    )
    {
        $this->request = $request;
        $this->vendorRepository = $vendorRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource)
    {
        $vendorId = $this->request->getParam('vendor_id');
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['entity_id'])) {
                    $vendor = $this->vendorRepository->getById($vendorId);
                    $item['vendor_name'] = $vendor->getName();
                }
            }
        }
        return $dataSource;
    }

}
