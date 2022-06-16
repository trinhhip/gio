<?php

namespace Omnyfy\RebateUI\Ui\Component\Listing\Column\Table\History;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Actions
 * @package Omnyfy\RebateUI\Ui\Component\Listing\Column\Table\History
 */
class Download extends Column
{
    /**
     * Url path
     */
    const URL_PATH_DOWNLOAD = 'rebate/invoice/download';
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    )
    {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$items) {  
                $url = $this->urlBuilder->getUrl(static::URL_PATH_DOWNLOAD,['invoice_id' => $items['entity_id']]);
                $items['download_invoice'] = html_entity_decode($this->getHtmlDownload($url));
            }
        }

        return $dataSource;
    }

    public function getHtmlDownload($url){
        return '<a target="_blank" href="'.$url.'">Download</a>';
    }

    /**
     * Get instance of escaper
     *
     * @return Escaper
     * @deprecated 101.0.7
     */
    private function getEscaper()
    {
        if (!$this->escaper) {
            $this->escaper = ObjectManager::getInstance()->get(Escaper::class);
        }
        return $this->escaper;
    }
}
