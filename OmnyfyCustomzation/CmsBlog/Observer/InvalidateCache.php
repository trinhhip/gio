<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@omnyfy.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace OmnyfyCustomzation\CmsBlog\Observer;

use Magento\Framework\App\Cache\Type\Block;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\PageCache\Model\Cache\Type;
use Magento\PageCache\Model\Config;

/**
 * Cms observer
 */
class InvalidateCache implements ObserverInterface
{
    /**
     * @var TypeListInterface
     */
    protected $_typeList;

    /**
     * Application config object
     *
     * @var Config
     */
    protected $_config;

    /**
     * @param Config $config
     * @param TypeListInterface $typeList
     */
    public function __construct(
        Config $config,
        TypeListInterface $typeList
    )
    {
        $this->_config = $config;
        $this->_typeList = $typeList;
    }

    /**
     * Invalidate full page and block cache
     *
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        if ($this->_config->isEnabled()) {
            $this->_typeList->invalidate(
                Type::TYPE_IDENTIFIER
            );
        }

        $this->_typeList->invalidate(
            Block::TYPE_IDENTIFIER
        );
    }
}
