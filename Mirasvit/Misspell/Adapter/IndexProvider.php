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

namespace Mirasvit\Misspell\Adapter;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Mirasvit\Misspell\Service\TextService;

class IndexProvider
{
    private $allowedTables = [
        'catalogsearch_fulltext',
        'mst_searchindex_',
        'catalog_product_entity_text',
        'catalog_product_entity_varchar',
        'catalog_category_entity_text',
        'catalog_category_entity_varchar',
    ];

    private $disallowedTables = [
        'mst_searchindex_mage_catalogsearch_query',
    ];

    private $resource;

    private $connection;

    private $textService;

    public function __construct(
        ResourceConnection $resource,
        TextService $textService
    ) {
        $this->resource = $resource;
        $this->connection = $this->resource->getConnection();
        $this->textService = $textService;
    }

    public function getPreparedTextData(int $storeId): iterable
    {
        foreach ($this->getTables($storeId) as $table => $columns) {
            if (!count($columns)) {
                continue;
            }

            foreach ($columns as $idx => $col) {
                $columns[$idx] = '`' . $col . '`';
            }

            $fromColumns = new \Zend_Db_Expr("CONCAT_WS(' '," . implode(',', $columns) . ") as data_index");
            $select = $this->connection->select()->from($table, $fromColumns);
            $results = $this->getTablesData($select);
            $rows = [];

            foreach ($results as $word => $freq) {
                $rows[] = [
                    'keyword'   => $word,
                    'trigram'   => $this->textService->getTrigram($word),
                    'frequency' => $freq / count($results),
                ];

                if (count($rows) > 1000) {
                    yield $rows;
                    $rows = [];
                }
            }

            if (count($rows) > 0) {
                yield $rows;
            }
        }
    }

    public function dropSuggestionTable(): void
    {
        $this->connection->delete($this->resource->getTableName('mst_misspell_suggest'));
    }

    private function getTablesData(Select $select, array $results = [], int $offset = 0): array
    {
        while (true) {
            $select->limit(10000, $offset);
            $result = $this->connection->query($select);
            $rows = $result->fetchAll();

            if (!$rows) {
                return $results;
            }

            foreach ($rows as $row) {
                $data = $row['data_index'];
                if (!empty($data)) {
                    $this->split($data, $results);
                }
            }

            $offset += 10000;
        }
    }

    private function split(string $string, array &$results, int $increment = 1): void
    {
        $string = $this->textService->cleanString($string);
        $words = $this->textService->splitWords($string);

        foreach ($words as $word) {
            if ($this->textService->strlen($word) >= $this->textService->getGram()
                && !is_numeric($word)
            ) {
                $word = $this->textService->strtolower($word);
                if (!isset($results[$word])) {
                    $results[$word] = $increment;
                } else {
                    $results[$word] += $increment;
                }
            }
        }
    }

    private function getTables(int $storeId): array
    {
        $result = [];
        $tables = $this->connection->getTables();

        foreach ($tables as $table) {
            $isAllowed = false;

            foreach ($this->allowedTables as $allowedTable) {
                if (mb_strpos($table, $allowedTable) !== false) {
                    $isAllowed = true;
                }
            }

            foreach ($this->disallowedTables as $disallowedTable) {
                if (mb_strpos($table, $disallowedTable) !== false) {
                    $isAllowed = false;
                }
            }

            if (!$isAllowed) {
                continue;
            }

            $result[$table] = $this->getTextColumns($table);
        }

        return $result;
    }

    protected function getTextColumns(string $table): array
    {
        $result = [];
        $allowedTypes = ['text', 'varchar', 'mediumtext', 'longtext'];
        $columns = $this->connection->describeTable($table);

        foreach ($columns as $column => $info) {
            if (in_array($info['DATA_TYPE'], $allowedTypes)) {
                $result[] = $column;
            }
        }

        return $result;
    }
}
