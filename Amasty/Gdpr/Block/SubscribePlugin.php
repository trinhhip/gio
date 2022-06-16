<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Block;

use Amasty\Gdpr\Model\ConsentLogger;
use Magento\Newsletter\Block\Subscribe as SubscribeBlock;
use Magento\Framework\Exception\LocalizedException;

class SubscribePlugin
{
    /**
     * @param SubscribeBlock $subject
     * @param                 $result
     *
     * @return string
     * @throws LocalizedException
     */
    public function afterToHtml(SubscribeBlock $subject, $result)
    {
        $layout = $subject->getLayout();

        if (!$layout->hasElement('form.subscribe')
            || $layout->hasElement('amasty_gdpr_newsletter')
        ) {
            return $result;
        }

        $checkboxBlock = $layout->createBlock(
            \Amasty\Gdpr\Block\Checkbox::class,
            'amasty_gdpr_newsletter',
            [
                'scope' => ConsentLogger::FROM_SUBSCRIPTION
            ]
        )->setTemplate('Amasty_Gdpr::checkbox.phtml')->toHtml();

        if ($checkboxBlock) {
            $pos = strripos($result, '</form>');
            $endOfHtml = substr($result, $pos);
            $result = substr_replace($result, $checkboxBlock, $pos) . $endOfHtml;
        }

        return $result;
    }
}
