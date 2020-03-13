<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Model\OptionSource\CookieConsent;

use Magento\Framework\Option\ArrayInterface;
use Amasty\GdprCookie\Model\CookieConsent;

class Type implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => CookieConsent::NOTIFICATION_CONSENT,
                'label'=> __('Notification Cookie Bar Consent')
            ],
            [
                'value' => CookieConsent::CONFIRMATION_CONSENT,
                'label'=> __('Confirmation Cookie Bar Consent')
            ]
        ];
    }
}
