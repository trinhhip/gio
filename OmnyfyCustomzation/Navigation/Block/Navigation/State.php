<?php


namespace OmnyfyCustomzation\Navigation\Block\Navigation;


use Amasty\Shopby\Block\Navigation\State\Swatch;
use Amasty\Shopby\Block\Navigation\Widget\HideMoreOptions;
use Amasty\Shopby\Helper\FilterSetting;
use Amasty\Shopby\Model\Layer\Filter\Item;
use Amasty\Shopby\Helper\Data as ShopbyHelper;
use Amasty\Shopby\Model\Layer\Filter\Price;
use Amasty\Shopby\Model\Source\DisplayMode;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Api\UrlBuilderInterface;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\BlockFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;

class State extends \Magento\LayeredNavigation\Block\Navigation\State
{
    /**
     * @var string
     */
    protected $_template = 'OmnyfyCustomzation_Navigation::layer/state.phtml';

    /**
     * @var FilterSetting
     */
    protected $filterSettingHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $managerInterface;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var ShopbyHelper
     */
    protected $helper;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    public $dimension = [
        'length' => 'Length',
        'width' => 'Width',
        'height' => 'Height',
    ];

    public function __construct(
        Context $context,
        Resolver $layerResolver,
        FilterSetting $filterSettingHelper,
        PriceCurrencyInterface $priceCurrency,
        ShopbyHelper $helper,
        BlockFactory $blockFactory,
        array $data = []
    )
    {
        $this->filterSettingHelper = $filterSettingHelper;
        $this->managerInterface = $context->getStoreManager();
        $this->priceCurrency = $priceCurrency;
        $this->helper = $helper;
        $this->blockFactory = $blockFactory;
        parent::__construct($context, $layerResolver, $data);
    }

    /**
     * @param FilterInterface $filter
     * @return FilterSettingInterface
     */
    public function getFilterSetting(FilterInterface $filter)
    {
        return $this->filterSettingHelper->getSettingByLayerFilter($filter);
    }

    /**
     * @param Item $filter
     * @param bool $showLabels
     * @return string
     * @throws LocalizedException
     */
    public function getSwatchHtml(Item $filter, $showLabels = false)
    {
        return $this->getLayout()->createBlock(Swatch::class)
            ->setFilter($filter)
            ->showLabels($showLabels)
            ->toHtml();
    }

    /**
     * @return string
     */
    public function collectFilters()
    {
        return $this->helper->collectFilters();
    }

    /**
     * @return int
     */
    public function getUnfoldedCount()
    {
        return $this->helper->getUnfoldedCount();
    }

    /**
     * @return string
     */
    public function createShowMoreButtonBlock()
    {
        return $this->blockFactory->createBlock(HideMoreOptions::class)
            ->setIsState(true)
            ->setUnfoldedOptions($this->getUnfoldedCount())
            ->toHtml();
    }

    /**
     * @param Item $filter
     * @return string
     * @throws LocalizedException
     */
    public function viewLabel($filter)
    {
        if ($this->isDimensionFilter($filter)) {
            return $filter->getValueString();
        }

        $filterSetting = $this->getFilterSetting($filter->getFilter());
        switch ($filterSetting->getDisplayMode()) {
            case DisplayMode::MODE_TEXT_SWATCH:
            case DisplayMode::MODE_IMAGES:
                $value = $this->getSwatchHtml($filter);
                break;
            case DisplayMode::MODE_IMAGES_LABELS:
                $value = $this->getSwatchHtml($filter, true);
                break;
            default:
                $value = $this->viewExtendedLabel($filter);
                break;
        }

        return $value;
    }

    /**
     * @param Item $filter
     * @return string
     * @throws LocalizedException
     */
    protected function viewExtendedLabel($filter)
    {
        if ($filter->getFilter()->getRequestVar() == DisplayMode::ATTRUBUTE_PRICE) {
            $currencyRate = (float)$filter->getFilter()->getCurrencyRate();

            if ($currencyRate != 1) {
                $value = $this->generateValueLabel($filter);
            } else {
                $value = $filter->getOptionLabel();
            }
        } else {
            $value = $this->stripTags($filter->getOptionLabel());
        }

        return $value;
    }

    /**
     * @param $filterItem
     * @return Phrase
     */
    private function generateValueLabel($filterItem)
    {
        $arguments = $filterItem->getLabel()->getArguments();

        if (!isset($arguments[1])) {
            $arguments[1] = "";
        }


        $arguments[0] = preg_replace("/[^,.0-9]/", '', $arguments[0]);
        $arguments[1] = preg_replace("/[^,.0-9]/", '', $arguments[1]);

        $posDotInFrom = strpos($arguments[0], '.');
        $posDotInTo = strpos($arguments[1], '.');
        $posCommaInFrom = strpos($arguments[0], ',');
        $posCommaInTo = strpos($arguments[1], ',');

        $arguments[0] = $this->removeSeparator($posDotInFrom, $posCommaInFrom, $arguments[0]);
        $arguments[1] = $this->removeSeparator($posDotInTo, $posCommaInTo, $arguments[1]);

        $arguments[0] = preg_replace("/[']/", '', $arguments[0]);
        $arguments[1] = preg_replace("/[']/", '', $arguments[1]);

        return __(
            '%1 - %2',
            $this->generateSpanPrice((float)$arguments[0]),
            $this->generateSpanPrice(
                $arguments[1] ? (float)$arguments[1] : $arguments[1],
                true
            )
        );
    }

    /**
     * @param $posDot
     * @param $posComma
     * @param $value
     * @return string
     */
    private function removeSeparator($posDot, $posComma, $value)
    {
        if ($posDot !== false && $posComma !== false) {
            if ($posDot < $posComma) {
                $value = preg_replace("/[.]/", '', $value);
            } else {
                $value = preg_replace("/[,]/", '', $value);
            }
        }

        return $value;
    }

    /**
     * @param $value
     * @param bool $flagTo
     * @return string
     */
    private function generateSpanPrice($value, $flagTo = false)
    {
        if (!$value && $flagTo) {
            $resultPrice = __('above');
        } else {
            $resultPrice = $this->priceCurrency->format($value);
        }

        return '<span class="price">' . $resultPrice . '</span>';
    }

    /**
     * @param $value
     * @param $filterItem
     * @return float|string
     */
    public function getFilterValue($value, $filterItem)
    {
        $filter = $filterItem->getFilter();
        if ($filter instanceof Price && count($value) >= 2) {
            $value[0] = $value[0] ? $value[0] * $filter->getCurrencyRate() : '';
            $value[1] = $value[1] ? $value[1] * $filter->getCurrencyRate() : '';
        } elseif (is_array($value)) {
            $value = $value[0];
        }

        return $value;
    }

    /**
     * @param $resultValue
     * @return string
     */
    public function getDataValue($resultValue)
    {
        $value = null;

        if (isset($resultValue)) {
            $value = $this->escapeHtml(
                $this->stripTags(is_array($resultValue) ? implode('-', $resultValue) : $resultValue, false)
            );
        }

        return $value;
    }

    /**
     * @param $filter
     * @param $value
     * @return array
     */
    public function changeValueForMultiselect($filter, $value)
    {
        if ($filter instanceof Price) {
            $value = [];
        } else {
            $value = array_filter(array_slice((array)$value, 1));
        }

        return $value;
    }

    public function getFilterLabel($filter)
    {
        if ($this->isDimensionFilter($filter)) {
            return __($this->dimension[$filter->getLabel()]) . ' (cm)';
        }
        return $filter->getName();
    }

    public function getClearLinkUrl($filter, $filterLabel, $labelList = [])
    {
        $requestVar = $filter->getFilter()->getRequestVar();
        if ($this->isDimensionFilter($filter)) {
            $query = [
                $filter->getLabel() => null
            ];
        } else {
            $filterValue = $filter->getValue();
            $filterParams = null;
            $i = 0;

            foreach ($labelList as $label) {
                if ($filterLabel == $label && is_array($filterValue)) {
                    $i++;
                }
            }
            if (is_array($filterValue) && count($filterValue) > 1 && $requestVar != 'price') {
                unset($filterValue[$i]);
                $filterParams = implode(',', $filterValue);
            }
            $query = $this->isDimensionFilter($filter) ? [$filter->getLabel() => null] : [$requestVar => $filterParams];
        }
        $urlParams = [
            '_current' => true,
            '_use_rewrite' => true,
            '_query' => $query,
            '_escape' => true,
        ];
        return $this->getUrl('*/*/*', $urlParams);
    }

    public function isDimensionFilter($filter)
    {
        try {
            if (isset($this->dimension[$filter->getLabel()])) {
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }
        return false;
    }
}
