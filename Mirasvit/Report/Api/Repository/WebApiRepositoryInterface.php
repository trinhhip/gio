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
 * @package   mirasvit/module-report
 * @version   1.3.106
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Report\Api\Repository;

use Mirasvit\ReportApi\Api\RequestInterface;

interface WebApiRepositoryInterface
{
    /**
     * @param \Mirasvit\ReportApi\Api\RequestInterface $request
     * @return \Mirasvit\ReportApi\Api\ResponseInterface
     */
    public function request(RequestInterface $request);
}