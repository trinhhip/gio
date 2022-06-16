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



namespace Mirasvit\Search\Index\Aheadworks\Blog\Post;

use Mirasvit\Search\Api\Data\Index\BatchDataMapperInterface;
use Mirasvit\Search\Index\AbstractBatchDataMapper;

class BatchBatchDataMapper extends AbstractBatchDataMapper implements BatchDataMapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function map(array $documentData, $storeId, array $context = [])
    {
        foreach ($documentData as $id => $document) {
            foreach ($document as $attribute => $value) {
                $documentData[$id][$attribute] = $this->context->getContentService()
                    ->processHtmlContent($storeId, $value);
            }
        }

        $documentData = parent::map($documentData, $storeId, $context);

        return $documentData;
    }
}
