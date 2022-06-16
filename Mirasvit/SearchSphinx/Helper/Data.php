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
namespace Mirasvit\SearchSphinx\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function filterTemplate(string $template, array $variables): string
    {
        foreach ($variables as $var => $value) {
            $template = str_replace("{{var $var}}", $value, $template);
        }

        return $template;
    }

    public function exec(string $command): array
    {
        $status = null;
        $data = [];
        /** mp comment start **/
        if (function_exists('exec')) {
            // @codingStandardsIgnoreStart
            exec($command, $data, $status);
            // @codingStandardsIgnoreEnd
        } else {
            throw new \LogicException((string)__('PHP function "exec" not available'));
        }
        /** mp comment end **/

        /** mp uncomment start
        throw new \LogicException((string)__('PHP function "exec" not available'));
        mp uncomment end **/

        return ['status' => $status, 'data' => implode(PHP_EOL, $data)];
    }
}
