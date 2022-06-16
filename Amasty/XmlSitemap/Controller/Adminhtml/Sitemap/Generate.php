<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Controller\Adminhtml\Sitemap;

use Amasty\XmlSitemap\Api\SitemapInterface;
use Amasty\XmlSitemap\Api\SitemapRepositoryInterface;
use Amasty\XmlSitemap\Model\XmlGenerator;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Registry;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Generate extends Action implements HttpGetActionInterface
{
    const ADMIN_RESOURCE = 'Amasty_XmlSitemap::sitemap';

    /**
     * @var SitemapRepositoryInterface $sitemapRepository
     */
    private $sitemapRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Emulation
     */
    private $appEmulation;

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var XmlGenerator
     */
    private $xmlGenerator;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        Context $context,
        SitemapRepositoryInterface $sitemapRepository,
        XmlGenerator $xmlGenerator,
        StoreManagerInterface $storeManager,
        Emulation $appEmulation,
        DateTime $dateTime,
        Registry $registry
    ) {
        parent::__construct($context);

        $this->sitemapRepository = $sitemapRepository;
        $this->xmlGenerator = $xmlGenerator;
        $this->storeManager = $storeManager;
        $this->appEmulation = $appEmulation;
        $this->_registry = $registry;
        $this->dateTime = $dateTime;
    }

    public function execute()
    {
        $id = (int) $this->getRequest()->getParam(SitemapInterface::SITEMAP_ID);
        $currentStore = $this->storeManager->getStore();

        try {
            $sitemap = $this->sitemapRepository->getById($id);
            if (!$sitemap->getId()) {
                $this->messageManager->addErrorMessage(__('Sitemap does not exist'));
                $this->_redirect('*/*/');
            }

            $this->_registry->register(SitemapInterface::SITEMAP_GENERATION, true);
            $this->configureEnvironment($sitemap);

            $this->xmlGenerator->generate($sitemap);

            $this->resetEnvironment($currentStore);
            $sitemap->setLastGeneration($this->dateTime->gmtDate());
            $this->sitemapRepository->save($sitemap);

            $this->messageManager->addSuccessMessage(__('Sitemap has been generated'));
        } catch (\Exception $e) {
            $this->resetEnvironment($currentStore);
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->_redirect('*/*/');
    }

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Amasty_XmlSitemap::sitemap');
    }

    private function configureEnvironment(SitemapInterface $sitemap): void
    {
        $storeId = $sitemap->getStoreId();

        $this->appEmulation->startEnvironmentEmulation($storeId);
        $this->storeManager->setCurrentStore($sitemap->getStoreId());
    }

    private function resetEnvironment(StoreInterface $store)
    {
        $this->appEmulation->stopEnvironmentEmulation();
        $this->storeManager->setCurrentStore($store);
    }
}
