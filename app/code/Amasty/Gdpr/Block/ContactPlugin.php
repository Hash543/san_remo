<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Block;

use Magento\Contact\Block\ContactForm as ContactBlock;
use Magento\Framework\Exception\LocalizedException;
use Amasty\Gdpr\Model\Checkbox as CheckboxModel;

class ContactPlugin
{
    /**
     * @param ContactBlock $subject
     * @param               $result
     *
     * @return string
     * @throws LocalizedException
     */
    public function afterToHtml(ContactBlock $subject, $result)
    {
        $layout = $subject->getLayout();

        if (!$layout->getBlock('contactForm')
            || $layout->getBlock('amasty_gdpr_contact')
        ) {
            return $result;
        }

        $checkboxBlock = $layout->createBlock(
            Checkbox::class,
            'amasty_gdpr_contact',
            [
                'scope' => CheckboxModel::AREA_CONTACT_US
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
