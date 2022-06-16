<?php
/**
 * Project: Multi Vendor
 * User: jing
 * Date: 2019-06-14
 * Time: 14:02
 */
namespace Omnyfy\Vendor\Model\Indexer\Location\Flat;

use Magento\Store\Model\ScopeInterface;

class State
{
    const INDEXER_ID = 'omnyfy_vendor_location_flat';

    protected $_locationFlatIndexerHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var bool
     */
    protected $isAvailable;

    /** @var \Magento\Framework\Indexer\IndexerRegistry */
    protected $indexerRegistry;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Omnyfy\Vendor\Helper\Location\Flat\Indexer $flatIndexerHelper,
        $isAvailable = false
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->indexerRegistry = $indexerRegistry;
        $this->isAvailable = $isAvailable;
        $this->_locationFlatIndexerHelper = $flatIndexerHelper;
    }

    public function getFlatIndexerHelper()
    {
        return $this->_locationFlatIndexerHelper;
    }

    /**
     * Check if Flat Index is available for use
     *
     * @return bool
     */
    public function isAvailable()
    {
        return $this->isAvailable && $this->indexerRegistry->get(static::INDEXER_ID)->isValid();
    }
}
 