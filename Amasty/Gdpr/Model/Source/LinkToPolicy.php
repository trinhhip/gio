<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


declare(strict_types=1);

namespace Amasty\Gdpr\Model\Source;

use Magento\Cms\Model\Page;
use Magento\Cms\Model\ResourceModel\Page\Collection;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class LinkToPolicy implements OptionSourceInterface
{
    const PRIVACY_POLICY = '#';

    /**
     * @var array
     */
    private $renderedOptions = [];

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        if (!$this->renderedOptions) {
            /** @var Collection $collection**/
            $collection = $this->collectionFactory->create();

            /** @var Page $page**/
            foreach ($collection as $page) {
                $this->renderedOptions[] = [
                    'label' => $page->getTitle(),
                    'value' => $page->getId()
                ];
            }

        }

        return $this->renderedOptions;
    }
}
