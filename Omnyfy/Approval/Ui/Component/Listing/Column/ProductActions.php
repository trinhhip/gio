<?php
/**
 * Project: Approval
 * User: jing
 * Date: 2019-08-20
 * Time: 17:40
 */
namespace Omnyfy\Approval\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class ProductActions extends Column
{
    const URL_PATH_APPROVE = 'omnyfy_approval/record/approve';
    const URL_PATH_DECLINE = 'omnyfy_approval/record/decline';

    protected $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [])
    {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                if (isset($item['id'])) {
                    $item[$name]['approve'] = [
                        'href' => $this->urlBuilder->getUrl(self::URL_PATH_APPROVE, ['id' => $item['id']]),
                        'label' => __('Review passed')
                    ];
                    $item[$name]['decline'] = [
                        'href' => $this->urlBuilder->getUrl(self::URL_PATH_DECLINE, ['id' => $item['id']]),
                        'label' => __('Review failed')
                    ];
                }
            }
        }
        return $dataSource;
    }
}
 