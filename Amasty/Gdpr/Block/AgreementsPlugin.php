<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Block;

use Magento\CheckoutAgreements\Block\Agreements as AgreementsBlock;
use Magento\Framework\Exception\LocalizedException;
use Amasty\Gdpr\Model\ConsentLogger;

class AgreementsPlugin
{
    /**
     * @param AgreementsBlock $subject
     * @param                 $result
     *
     * @return string
     * @throws LocalizedException
     */
    public function afterToHtml(AgreementsBlock $subject, $result)
    {
        $layout = $subject->getLayout();

        if (!$layout->getBlock('amasty.gdpr.privacy.policy.popup')
            || $layout->getBlock('amasty_gdpr_agreements')
        ) {
            return $result;
        }

        $checkboxBlock = $layout->createBlock(
            Checkbox::class,
            'amasty_gdpr_agreements',
            [
                'scope' => ConsentLogger::FROM_CHECKOUT
            ]
        );

        return $checkboxBlock->toHtml() . $result;
    }
}
