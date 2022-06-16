<?php
/**
 * Lucas
 * Copyright (C) 2019
 *
 * This file is part of OmnyfyCustomzation/B2C.
 *
 * OmnyfyCustomzation/B2C is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace OmnyfyCustomzation\B2C\Block\Wishlist;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Shared extends Template
{
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('SHARE YOU WISHLIST'));
    }

    public function __construct(
        Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    public function getContinueUrl()
    {
        return $this->getUrl();
    }
}