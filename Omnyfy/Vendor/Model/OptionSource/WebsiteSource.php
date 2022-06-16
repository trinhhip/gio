<?php
namespace Omnyfy\Vendor\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;

/**
 * @api
 */
class WebsiteSource implements OptionSourceInterface
{
    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    protected $store;

    /**
     * @param WebsiteRepositoryInterface $websiteRepository
     */
    public function __construct(
        WebsiteRepositoryInterface $websiteRepository,
        \Magento\Store\Model\System\Store $store
    ) {
        $this->websiteRepository = $websiteRepository;
        $this->store = $store;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        $stores = $this->store->getStoreValuesForForm(false, true);

        return $stores;
    }
}
