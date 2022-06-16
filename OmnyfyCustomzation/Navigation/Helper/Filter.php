<?php


namespace OmnyfyCustomzation\Navigation\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\Element\Template;

class Filter extends AbstractHelper
{
    /**
     * @var Template
     */
    public $template;
    public $allowPage = [
        'catalog_category_view',
        'amshopby_index_index'
    ];

    public function __construct(
        Context $context,
        Template $template
    )
    {
        $this->template = $template;
        parent::__construct($context);
    }

    public function getDimensionsForm()
    {
        $form = '';
        if (in_array($this->_request->getFullActionName(), $this->allowPage)) {
            $form = $this->template->getLayout()->createBlock(
                'Magento\Framework\View\Element\Template',
                null)
                ->setDimensionParam($this->getDimensionParam())
                ->setTemplate("OmnyfyCustomzation_Navigation::/layer/filter/dimensions.phtml")
                ->toHtml();
        }
        return $form;
    }

    public function getDimensionParam()
    {
        $paramRequest = $this->_request->getParams();
        $dimensionParam = [];
        if (isset($paramRequest['height'])) {
            $heightParams = $this->splitPram($paramRequest['height']);
            $dimensionParam['height_from'] = isset($heightParams[0]) ? $heightParams[0] : null;
            $dimensionParam['height_to'] = isset($heightParams[1]) ? $heightParams[1] : null;
        }
        if (isset($paramRequest['width'])) {
            $widthParams = $this->splitPram($paramRequest['width']);
            $dimensionParam['width_from'] = isset($widthParams[0]) ? $widthParams[0] : null;
            $dimensionParam['width_to'] = isset($widthParams[1]) ? $widthParams[1] : null;
        }
        if (isset($paramRequest['length'])) {
            $lengthParams = $this->splitPram($paramRequest['length']);
            $dimensionParam['length_from'] = isset($lengthParams[0]) ? $lengthParams[0] : null;
            $dimensionParam['length_to'] = isset($lengthParams[1]) ? $lengthParams[1] : null;
        }
        return $dimensionParam;
    }

    public function splitPram($pram)
    {
        return explode('-', $pram);
    }

    public function getStateClearUrl()
    {
        $filterState = [];
        $requestParams = $this->_request->getParams();
        foreach ($requestParams as $pramName => $requestParam) {
            $filterState[$pramName] = null;
        }
        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_query'] = $filterState;
        $params['_escape'] = true;
        return $this->_urlBuilder->getUrl('*/*/*', $params);
    }
}