<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Block;

use Magento\Newsletter\Block\Subscribe as SubscribeBlock;
use Magento\Framework\Exception\LocalizedException;
use Amasty\Gdpr\Model\Checkbox as CheckboxModel;

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

        if (!$layout->getBlock('form.subscribe')
            || $layout->getBlock('amasty_gdpr_newsletter')
        ) {
            return $result;
        }

        $checkboxBlock = $layout->createBlock(
            Checkbox::class,
            'amasty_gdpr_newsletter',
            [
                'scope' => CheckboxModel::AREA_NEWSLETTER
            ]
        )->setTemplate('Amasty_Gdpr::checkboxNewsletter.phtml')->toHtml();

        if ($checkboxBlock) {
            $pos = strripos($result, '</form>');
            $endOfHtml = substr($result, $pos);
            $result = substr_replace($result, $checkboxBlock, $pos) . $endOfHtml;
        }

        return $result;
    }
}
