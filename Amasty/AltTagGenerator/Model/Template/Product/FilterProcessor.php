<?php

declare(strict_types=1);

namespace Amasty\AltTagGenerator\Model\Template\Product;

use Amasty\AltTagGenerator\Model\Template\Product\Filter\AttributeResolverInterface;
use Amasty\AltTagGenerator\Model\Template\Product\Filter\CustomAttributeResolverInterface;
use Amasty\AltTagGenerator\Model\Template\Product\Filter\GlobalResolverInterface;
use Magento\Catalog\Api\Data\ProductInterface;

class FilterProcessor
{
    const VARIABLE_PATTERN = '@\{[A-Za-z_|]+\}@s';
    const OR_SEPARATOR = '|';
    const OPTIONAL_PARTS_PATTERN = '@\[[^\[\]]*?\]@';

    /**
     * @var AttributeResolverInterface
     */
    private $defaultAttributeResolver;

    /**
     * @var GlobalResolverInterface[]
     */
    private $globalResolvers;

    /**
     * @var CustomAttributeResolverInterface[]
     */
    private $customResolvers;

    public function __construct(
        AttributeResolverInterface $defaultAttributeResolver,
        array $globalResolvers = [],
        array $customResolvers = []
    ) {
        $this->defaultAttributeResolver = $defaultAttributeResolver;
        $this->globalResolvers = $globalResolvers;
        $this->customResolvers = $customResolvers;
    }

    public function execute(string $template, ProductInterface $product): string
    {
        $template = $this->handleVariables($template, $product);
        $template = $this->handleOptionalParts($template);
        $template = $this->handleNonProcessedVariables($template);

        return $template;
    }

    private function handleVariables(string $template, ProductInterface $product): string
    {
        if (preg_match_all(self::VARIABLE_PATTERN, $template, $matches)) {
            foreach ($matches[0] as $construction) {
                $variables = strtolower(trim($construction, '{}'));

                $variableValue = null;
                foreach (explode(self::OR_SEPARATOR, $variables) as $variable) {
                    if (isset($this->globalResolvers[$variable])) {
                        $variableValue = $this->globalResolvers[$variable]->execute();
                    } elseif (isset($this->customResolvers[$variable])) {
                        $variableValue = $this->customResolvers[$variable]->execute($product);
                    } else {
                        $variableValue = $this->defaultAttributeResolver->execute($product, $variable);
                    }

                    if ($variableValue !== null) {
                        break;
                    }
                }

                if ($variableValue !== null) {
                    $template = str_replace($construction, $variableValue, $template);
                }
            }
        }

        return $template;
    }

    private function handleOptionalParts(string $template): string
    {
        do {
            $template = preg_replace_callback(
                self::OPTIONAL_PARTS_PATTERN,
                function ($part) {
                    if (strpos($part[0], '}') !== false) {
                        return '';
                    }

                    return trim(substr($part[0], 1, -1));
                },
                $template,
                -1,
                $count
            );
        } while ($count);

        return trim($template);
    }

    private function handleNonProcessedVariables(string $template): string
    {
        $template = preg_replace(self::VARIABLE_PATTERN, '', $template);
        $template = preg_replace('@\s+@', ' ', $template);

        return trim($template);
    }
}
