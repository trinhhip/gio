<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\Cron;

use Amasty\XmlSitemap\Model\ResourceModel\Sitemap\CollectionFactory as SitemapCollectionFactory;
use Amasty\XmlSitemap\Model\XmlGenerator;

class GenerateSitemap
{
    /**
     * @var XmlGenerator
     */
    private $xmlGenerator;

    /**
     * @var SitemapCollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        XmlGenerator $xmlGenerator,
        SitemapCollectionFactory $collectionFactory
    ) {
        $this->xmlGenerator = $xmlGenerator;
        $this->collectionFactory = $collectionFactory;
    }

    public function execute(): void
    {
        $collection = $this->collectionFactory->create();

        foreach ($collection as $sitemap) {
            $this->xmlGenerator->generate($sitemap);
        }
    }
}
