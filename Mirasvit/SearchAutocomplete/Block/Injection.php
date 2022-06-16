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


declare(strict_types=1);

namespace Mirasvit\SearchAutocomplete\Block;

use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Search\Helper\Data as SearchHelper;
use Mirasvit\SearchAutocomplete\Model\ConfigProvider;

class Injection extends Template
{
    protected $storeManager;

    protected $config;

    protected $localeFormat;

    protected $searchHelper;

    public function __construct(
        Context $context,
        ConfigProvider $config,
        FormatInterface $localeFormat,
        SearchHelper $searchHelper
    ) {
        $this->storeManager = $context->getStoreManager();
        $this->config       = $config;
        $this->localeFormat = $localeFormat;
        $this->searchHelper = $searchHelper;

        parent::__construct($context);
    }

    public function getJsConfig(): array
    {
        return [
            'query'              => $this->searchHelper->getEscapedQueryText(),
            'priceFormat'        => $this->localeFormat->getPriceFormat(),
            'minSearchLength'    => $this->config->getMinChars(),
            'url'                => $this->getUrl(
                'searchautocomplete/ajax/suggest',
                ['_secure' => $this->getRequest()->isSecure()]
            ),
            'storeId'            => $this->storeManager->getStore()->getId(),
            'delay'              => $this->config->getDelay(),
            'layout'             => $this->config->getAutocompleteLayout(),
            'popularTitle'       => (string)__('Hot Searches'),
            'popularSearches'    => $this->config->isShowPopularSearches() ? $this->config->getPopularSearches() : [],
            'isTypeaheadEnabled' => $this->config->isTypeAheadEnabled(),
            'typeaheadUrl'       => $this->getUrl(
                'searchautocomplete/ajax/typeahead',
                ['_secure' => $this->getRequest()->isSecure()]
            ),
            'minSuggestLength'   => 2,
        ];
    }

    public function getCssStyles(): string
    {
        return $this->config->getCssStyles();
    }
}
