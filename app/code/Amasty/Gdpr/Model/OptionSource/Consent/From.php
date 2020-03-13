<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model\OptionSource\Consent;

use Magento\Framework\Option\ArrayInterface;
use Amasty\Gdpr\Model\Consent;

class From implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => Consent::FROM_REGISTRATION, 'label'=> __('Registration')],
            ['value' => Consent::FROM_CHECKOUT, 'label'=> __('Checkout')],
            ['value' => Consent::FROM_CONTACTUS, 'label'=> __('Contact Us')],
            ['value' => Consent::FROM_SUBSCRIPTION, 'label'=> __('Newsletter Subscription')],
            ['value' => Consent::FROM_EMAIL, 'label'=> __('Email')]
        ];
    }
}
