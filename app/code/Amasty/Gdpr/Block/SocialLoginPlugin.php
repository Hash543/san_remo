<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Block;

use Amasty\SocialLogin\Block\Popup;
use Magento\Framework\Exception\LocalizedException;
use Amasty\Gdpr\Model\Checkbox as CheckboxModel;

class SocialLoginPlugin
{
    /**
     * @param Popup $subject
     * @param       $result
     *
     * @return string
     * @throws LocalizedException
     */
    public function afterToHtml(Popup $subject, $result)
    {
        $layout = $subject->getLayout();

        if (!$layout->getBlock('social-login-popup')
            || $layout->getBlock('amasty_gdpr_social')
        ) {
            return $result;
        }

        $checkboxBlock = $layout->createBlock(
            Checkbox::class,
            'amasty_gdpr_social',
            [
                'scope' => CheckboxModel::AREA_REGISTRATION
            ]
        )->setTemplate('Amasty_Gdpr::checkbox.phtml')->toHtml();

        if ($checkboxBlock) {
            $fieldsetText = '</fieldset>';
            $pos = strripos($result, $fieldsetText);
            $result = substr_replace($result, $checkboxBlock . $fieldsetText, $pos, strlen($fieldsetText));
        }

        return $result;
    }
}
