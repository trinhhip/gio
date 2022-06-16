<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search-ultimate
 * @version   2.0.22
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\SearchAutocomplete\InstantProvider;

use Mirasvit\Search\Api\Data\QueryConfigProviderInterface;

class ConfigProvider implements QueryConfigProviderInterface
{
    private $configData;
    private $storeId = 0;

    public function __construct (array $configData) {
        $this->configData = $configData;
    }

    public function getEngine(): string
    {
        return $this->configData["$this->storeId/engine"];
    }

    public function getIndexes(): array
    {
        return $this->configData["$this->storeId/indexes"];   
    }

    public function getIndexFields(string $indexIdentifier): array
    {
        return $this->configData["$this->storeId/index/$indexIdentifier/fields"];
    }

    public function getIndexAttributes(string $indexIdentifier): array
    {
        return $this->configData["$this->storeId/index/$indexIdentifier/attributes"];
    }

    public function getLimit(string $indexIdentifier): int
    {
        return $this->configData["$this->storeId/index/$indexIdentifier/limit"];
    }

    public function getIndexName(string $indexIdentifier): string
    {
        $searchEngine = $this->getEngine();

        return $this->configData["$this->storeId/$searchEngine"][$indexIdentifier];
    }

    public function getEngineConnection(): array
    {
        $searchEngine = $this->getEngine();

        return $this->configData["$this->storeId/$searchEngine"]['connection'];
    }

    public function getIndexPosition(string $indexIdentifier): int
    {
        return $this->configData["$this->storeId/index/$indexIdentifier/position"];
    }

    public function getIndexTitle(string $indexIdentifier): string
    {
        return $this->configData["$this->storeId/index/$indexIdentifier/title"];
    }

    public function getTextAll (): string
    {
        return $this->configData["$this->storeId/textAll"];
    }

    public function getTextEmpty (): string
    {
        return $this->configData["$this->storeId/textEmpty"];
    }

    public function getUrlAll (): string
    {
        return $this->configData["$this->storeId/urlAll"];
    }

    public function getLongTailExpressions(): array
    {
        return $this->configData["$this->storeId/configuration/long_tail_expressions"];
    }

    public function getReplaceWords(): array
    {
        return $this->configData["$this->storeId/configuration/replace_words"];
    }

    public function getNotWords(): array
    {
        return $this->configData["$this->storeId/configuration/not_words"];
    }


    public function getWildcardMode(): string
    {
        return $this->configData["$this->storeId/configuration/wildcard"];
    }

    public function getMatchMode(): string
    {
        return $this->configData["$this->storeId/configuration/match_mode"];
    }

    public function getWildcardExceptions(): array
    {
        return $this->configData["$this->storeId/configuration/wildcard_exceptions"];
    }

    public function getSynonyms(array $terms, int $storeId): array
    {
        $synonyms = [];
        foreach ($this->configData["$this->storeId/synonyms"] as $synonymsGroup) {
            if (preg_match('/\b'. implode('|', $terms) .'\b/', $synonymsGroup, $match)) {
                if (isset($synonyms[$match[0]])) {
                    $synonyms[$match[0]] = array_merge($synonyms[$match[0]], preg_split('/,/', $synonymsGroup));
                } else {
                    $synonyms[$match[0]] = preg_split('/,/', $synonymsGroup);
                }
            }
        }

        return $synonyms;
    }

    public function isStopword(string $term, int $storeId): bool
    {
        return in_array($term, $this->configData["$this->storeId/stopwords"]);
    }

    public function applyStemming(string $term): string
    {
        if (substr($term, -2) == 'es') {
            $term = mb_substr($term, 0, -2);
        } elseif (substr($term, -1) == 's') {
            $term = mb_substr($term, 0, -1);
        }

        return $term;
    }

    public function getStoreId(): int
    {
        return $this->storeId;
    }

    public function getTypeaheadSuggestions(string $query): array
    {
        $suggestions = [];
        foreach ($this->configData["$this->storeId/typeahead"] as $groupKey => $suggestionsGroup) {
            if (substr($query, 0, 2) == $groupKey) {
                $suggestions = $suggestionsGroup;
                break;
            }            
        }

        return $suggestions;
    }

    public function setStoreId(int $storeId): void
    {
        $this->storeId = $storeId;
    }
}
