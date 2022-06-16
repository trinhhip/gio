<?php

namespace Omnyfy\VendorSearch\Plugin\Model\Layer\Filter;

use Magento\Framework\UrlInterface;
use Magento\Theme\Block\Html\Pager;

class Item
{
    const IS_NOT_ALLOWED_MULTI_SELECT_ATTRIBUTES = ['vendor_map_search_distance'];
    protected $_url;
    protected $_htmlPagerBlock;

    public function __construct(
        UrlInterface $url,
        Pager $htmlPagerBlock
    ) {
        $this->_url = $url;
        $this->_htmlPagerBlock = $htmlPagerBlock;
    }

    public function afterGetAddUrl(
        \Omnyfy\LayeredNavigation\Model\Layer\Filter\Item $subject, $result
    ){
        $isNotAllowedMultiSelect = in_array($subject->getFilter()->getRequestVar(), self::IS_NOT_ALLOWED_MULTI_SELECT_ATTRIBUTES);
        if (!$subject->isSelected() && $isNotAllowedMultiSelect) {
            return $this->_url->getUrl('*/*/*', [
                '_current'      => true,
                '_use_rewrite'  => true,
                '_escape'       => true,
                '_query'        => [
                    $subject->getFilter()->getRequestVar() => $subject->getValue(),
                    $this->_htmlPagerBlock->getPageVarName() => null,
                ]
            ]);
        }

        return $result;
    }

}