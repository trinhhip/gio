<?php
/**
 * Project: Approval
 * User: jing
 * Date: 2019-08-19
 * Time: 16:09
 */

namespace Omnyfy\Approval\Ui\DataProvider\Product\Form\Modifier;

use Magento\Framework\Phrase;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;

class Approval extends \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier
{
    protected $_locator;

    protected $_session;

    protected $_recordResource;

    protected $_recordCollectionFactory;

    protected $_helper;

    public function __construct(
        \Magento\Catalog\Model\Locator\LocatorInterface $locator,
        \Magento\Backend\Model\Session $backendSession,
        \Omnyfy\Approval\Helper\Data $_helper,
        \Omnyfy\Approval\Model\Resource\Product $_recordResource,
        \Omnyfy\Approval\Model\Resource\Product\CollectionFactory $_recordCollectionFactory
    )
    {
        $this->_locator = $locator;
        $this->_session = $backendSession;
        $this->_helper = $_helper;
        $this->_recordResource = $_recordResource;
        $this->_recordCollectionFactory = $_recordCollectionFactory;
    }

    public function modifyData(array $data)
    {
        return $data;
    }

    public function modifyMeta(array $meta)
    {
        if (!$this->_helper->isModuleOutputEnabled() || !$this->_helper->isEnabled()) {
            return $meta;
        }
        /*
                $record = $this->_helper->getProductRecord($productId, $vendorInfo['vendor_id']);
                if (empty($record)) {
                    return $meta;
                }
        */

        return $meta;
    }
}
 