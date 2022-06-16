<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Template\Product;

use Amasty\AltTagGenerator\Model\Source\ReplacementLogic;
use Amasty\AltTagGenerator\Model\Template\Query\GetByIdInterface;
use Magento\Catalog\Api\Data\ProductInterface;

class ModifyAltTag
{
    /**
     * @var array
     */
    private $altTagCache = [];

    /**
     * @var GetAppliedTemplate
     */
    private $getAppliedTemplate;

    /**
     * @var GetByIdInterface
     */
    private $getById;

    /**
     * @var GetAltTag
     */
    private $getAltTag;

    public function __construct(
        GetAppliedTemplate $getAppliedTemplate,
        GetByIdInterface $getById,
        GetAltTag $getAltTag
    ) {
        $this->getAppliedTemplate = $getAppliedTemplate;
        $this->getById = $getById;
        $this->getAltTag = $getAltTag;
    }

    public function execute(ProductInterface $product, string $oldTag, bool $useCache = false): string
    {
        $appliedTemplateId = $this->getAppliedTemplate->execute(
            (int) $product->getStoreId(),
            (int) $product->getId()
        );

        if ($appliedTemplateId) {
            $appliedTemplate = $this->getById->execute($appliedTemplateId);
            if ($appliedTemplate->getReplacementLogic() === ReplacementLogic::REPLACE) {
                $result = $this->retrieveAltTag($product, $useCache);
            } elseif ($appliedTemplate->getReplacementLogic() === ReplacementLogic::REPLACE_EMPTY) {
                $result = $oldTag ?: $this->retrieveAltTag($product, $useCache);
            } else {
                $altTag = $this->retrieveAltTag($product, $useCache);
                $result = $oldTag ? sprintf('%s %s', $oldTag, $altTag) : $altTag;
            }
        }

        return $result ?? $oldTag;
    }

    private function retrieveAltTag(ProductInterface $product, bool $useCache): string
    {
        if ($useCache && !isset($this->altTagCache[$product->getId()])) {
            $this->altTagCache[$product->getId()] = $this->getAltTag($product);
        }

        return $useCache ? $this->altTagCache[$product->getId()] : $this->getAltTag($product);
    }

    private function getAltTag(ProductInterface $product): string
    {
        return (string) $this->getAltTag->execute($product);
    }
}
