<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;

/**
 * Overide sitemap
 */
class Sitemap extends \Magento\Sitemap\Model\Sitemap
{
    /**
     * Initialize sitemap items
     *
     * @return void
     */
    protected function _initSitemapItems()
    {
        parent::_initSitemapItems();

        $this->_sitemapItems[] = new DataObject(
            [
                'changefreq' => 'weekly',
                'priority' => '0.25',
                'collection' => ObjectManager::getInstance()->create(
                    'OmnyfyCustomzation\CmsBlog\Model\Category'
                )->getCollection($this->getStoreId())
                    ->addStoreFilter($this->getStoreId())
                    ->addActiveFilter(),
            ]
        );

        $this->_sitemapItems[] = new DataObject(
            [
                'changefreq' => 'weekly',
                'priority' => '0.25',
                'collection' => ObjectManager::getInstance()->create(
                    'OmnyfyCustomzation\CmsBlog\Model\Article'
                )->getCollection($this->getStoreId())
                    ->addStoreFilter($this->getStoreId())
                    ->addActiveFilter(),
            ]
        );
    }

}
