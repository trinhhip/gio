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

namespace Mirasvit\Search\Plugin\Frontend;

use Magento\Search\Model\QueryFactory;
use Mirasvit\Search\Model\ConfigProvider;

/**
 * @see \Mirasvit\Search\Block\Result::toHtml()
 */
class HighlightPlugin
{
    private $configProvider;

    private $queryFactory;

    public function __construct(
        ConfigProvider $configProvider,
        QueryFactory $queryFactory
    ) {
        $this->configProvider = $configProvider;
        $this->queryFactory   = $queryFactory;
    }

    public function afterToHtml(object $subject, string $html): string
    {
        if (!$this->configProvider->isHighlightingEnabled()) {
            return $html;
        }

        $html = $this->highlight(
            $html,
            $this->queryFactory->get()->getQueryText()
        );

        return $html;
    }

    public function highlight(string $html, string $query): string
    {
        if (strlen($query) < 3) {
            return $html;
        }

        $query         = $this->removeSpecialChars($query);
        $preparedQuery = array_filter(explode(' ', $query));
        usort($preparedQuery, function ($a, $b) {
            return strlen($a) - strlen($b);
        });

        // find terms in the product name
        preg_match_all('/>[\w\d\s\S][^<>]*[' . implode('|', explode(' ', $query)) . ']+[\w\d\s\S][^<>]*<\/a>/iU', $html, $matches);

        foreach ($matches[0] as $key => $match) {
            $replacement = $match;

            // get array of words in the matched phrase. Array [0 => word, 1 => offset in the string]
            $words = preg_split('/\b/', $match, -1, PREG_SPLIT_OFFSET_CAPTURE);

            // sort in the reverse order to make sure that offset will not change
            krsort($words);

            $parts = [];
            foreach ($words as $data) {
                $word     = $data[0];
                $position = $data[1];

                // find start and end position for each term
                foreach ($preparedQuery as $subQuery) {

                    $querylen = strlen($subQuery);
                    $start    = stripos($word, $subQuery);

                    if ($start !== false) {

                        if (!isset($parts[$start])) {
                            $parts[$start] = [$start, $start + $querylen];
                        } elseif ($parts[$start][1] < $start + $querylen) {
                            $parts[$start][1] = $start + $querylen;
                        }
                    }

                }

                // sort positions
                ksort($parts);

                $finalParts = [];

                if (count($parts)) {

                    $arrayMin   = array_keys($parts);
                    $minsAmount = count($arrayMin);

                    // process positions
                    while ($minsAmount) {
                        // compare each position to others
                        foreach ($arrayMin as $tmpMin) {

                            foreach ($parts as $k => $part) {
                                // current position
                                if ($k == $tmpMin) {
                                    continue;
                                } elseif ($part[0] <= $parts[$tmpMin][1]) { // compared position starts before current finished
                                    if ($part[1] > $parts[$tmpMin][1]) { // compared position ends after current finished
                                        $parts[$tmpMin][1] = $part[1]; // change end of current position
                                    }

                                    unset($parts[$k]); // remove processed position
                                } else { // compared position out of current position. Save this selection and start new
                                    $finalParts[] = $parts[$tmpMin];
                                    unset($parts[$tmpMin]);
                                    break(2);
                                }

                            }

                            // save current selection
                            $finalParts[] = $parts[$tmpMin];
                            unset($parts[$tmpMin]);
                        }

                        // recalc positions
                        $minsAmount = count(array_keys($parts));
                    }
                }

                // apply selection tags to the matched terms
                if (count($finalParts)) {
                    foreach ($finalParts as $part) {
                        $word = substr_replace($word, '</span>', $part[1], 0);
                        $word = substr_replace($word, '<span class="mst-search__highlight">', $part[0], 0);
                    }

                    // replace original terms by terms with selection
                    $match = substr_replace($match, $word, $position, strlen($data[0]));
                }
            }

            // apply selection to html
            if ($match != $replacement) {
                $html = str_ireplace($replacement, $match, $html);
            }
        }

        return $html;
    }

    private function removeSpecialChars(string $query): string
    {
        $pattern = '/(\+|-|\/|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/';
        $replace = ' ';

        return preg_replace($pattern, $replace, $query);
    }
}
