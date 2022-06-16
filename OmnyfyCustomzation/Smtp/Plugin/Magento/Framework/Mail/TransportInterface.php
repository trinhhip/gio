<?php
/**
 * Lucas
 * Copyright (C) 2019 
 * 
 * This file is part of OmnyfyCustomzation/Smtp.
 * 
 * OmnyfyCustomzation/Smtp is free software: you can redistribute it and/or modify
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

namespace OmnyfyCustomzation\Smtp\Plugin\Magento\Framework\Mail;

class TransportInterface
{

    public function aroundSendMessage(
        \Magento\Framework\Mail\TransportInterface $subject,
        \Closure $proceed
    ) {
        //Your plugin code
        $result = $proceed();
        return $result;
    }
}
