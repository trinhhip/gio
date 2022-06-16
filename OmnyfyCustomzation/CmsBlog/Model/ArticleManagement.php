<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Model;

use OmnyfyCustomzation\CmsBlog\Model\ArticleFactory;

/**
 * Article management model
 */
class ArticleManagement extends AbstractManagement
{
    /**
     * @var ArticleFactory
     */
    protected $_itemFactory;

    /**
     * Initialize dependencies.
     *
     * @param ArticleFactory $articleFactory
     */
    public function __construct(
        ArticleFactory $articleFactory
    )
    {
        $this->_itemFactory = $articleFactory;
    }

}
