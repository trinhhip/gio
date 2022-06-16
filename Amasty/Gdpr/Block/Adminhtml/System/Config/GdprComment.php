<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


declare(strict_types=1);

namespace Amasty\Gdpr\Block\Adminhtml\System\Config;

use Amasty\Gdpr\ViewModel\Adminhtml\System\Config\GdprCommentViewModel;

class GdprComment extends \Magento\Backend\Block\Template
{
    public function getViewModel(): GdprCommentViewModel
    {
        return $this->getData('viewModel');
    }
}
