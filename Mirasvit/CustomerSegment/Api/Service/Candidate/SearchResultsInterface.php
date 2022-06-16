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
 * @package   mirasvit/module-customer-segment
 * @version   1.1.5
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\CustomerSegment\Api\Service\Candidate;

/**
 * Interface for segment candidates search results.
 * @api
 */
interface SearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get candidates list.
     *
     * @return \Mirasvit\CustomerSegment\Api\Data\CandidateInterface[]
     */
    public function getItems();

    /**
     * Set candidates list.
     *
     * @param \Mirasvit\CustomerSegment\Api\Data\CandidateInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items);
}