<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Model;

use OmnyfyCustomzation\CmsBlog\Model\CategoryFactory;

/**
 * Category management model
 */
class CategoryManagement extends AbstractManagement
{
    /**
     * @var CategoryFactory
     */
    protected $_itemFactory;

    /**
     * Initialize dependencies.
     *
     * @param CategoryFactory $categoryFactory
     */
    public function __construct(
        CategoryFactory $categoryFactory
    )
    {
        $this->_itemFactory = $categoryFactory;
    }

}
