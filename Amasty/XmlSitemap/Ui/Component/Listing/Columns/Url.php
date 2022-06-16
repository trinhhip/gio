<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Ui\Component\Listing\Columns;

use Amasty\XmlSitemap\Model\Sitemap\UrlProvider;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Url extends Column
{
    const URL_FORMAT = '<a href="%1$s" target="_blank">%1$s</a>';

    /**
     * @var UrlProvider
     */
    private $urlProvider;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlProvider $urlProvider,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->urlProvider = $urlProvider;
    }

    public function prepareDataSource(array $dataSource): array
    {
        foreach ($dataSource['data']['items'] as $key => $item) {
            $url = $this->urlProvider->getSitemapUrl($item['path'], (int)$item['store_id']);

            if ($url != null) {
                $dataSource['data']['items'][$key]['result_link'] = sprintf(self::URL_FORMAT, $url);
            } else {
                $storeId = (int)$item['store_id'];
                $indexFilePath = $this->getIndexFilePath($item['path']);
                $indexFileUrl = $this->urlProvider->getSitemapUrl($indexFilePath, $storeId);

                if ($indexFileUrl != null) {
                    $html = sprintf(self::URL_FORMAT, $indexFileUrl) . '</br>';
                    $urls = $this->getNumeratedFilesUrls($item['path'], $storeId);

                    foreach ($urls as $url) {
                        $html .= '-&nbsp;' . sprintf(self::URL_FORMAT, $url) . '</br>';
                    }
                    $dataSource['data']['items'][$key]['result_link'] = $html;
                } else {
                    $dataSource['data']['items'][$key]['result_link'] = __('Not Generated Yet');
                }
            }
        }

        return $dataSource;
    }

    private function getNumeratedFilesUrls(string $filePath, int $storeId): array
    {
        $num = 1;
        $result = [];

        while (true) {
            $numeratedFilename = $this->getNumeratedFileName($filePath, $num++);
            $url = $this->urlProvider->getSitemapUrl($numeratedFilename, $storeId);

            if ($url) {
                $result[] = $url;
            } else {

                break;
            }
        }

        return $result;
    }

    private function getIndexFilePath(string $filePath): string
    {
        return str_replace('.xml', '_index.xml', $filePath);
    }

    private function getNumeratedFileName(string $fileName, int $num): string
    {
        return str_replace('.xml', sprintf('_%d.xml', $num), $fileName);
    }
}
