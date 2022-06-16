<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Indexer\Template;

class CacheContext extends \Magento\Framework\Indexer\CacheContext
{
    /**
     * @var int
     */
    private $countElements = 0;

    /**
     * Register entity Ids
     *
     * @param string $cacheTag
     * @param array $ids
     *
     * @return $this
     */
    public function registerEntities($cacheTag, $ids)
    {
        parent::registerEntities($cacheTag, $ids);
        $this->countElements += count($ids);

        return $this;
    }

    public function getSize(): int
    {
        return $this->countElements;
    }

    public function flush(): void
    {
        $this->countElements = 0;
        $this->entities = [];
    }
}
