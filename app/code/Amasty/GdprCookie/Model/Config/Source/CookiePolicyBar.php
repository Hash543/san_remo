<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class CookiePolicyBar implements ArrayInterface
{
    /**#@+*/
    const DISABLED = 0;

    const NOTIFICATION = 1;

    const CONFIRMATION = 2;

    /**#@-*/

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::DISABLED     => __('No'),
            self::NOTIFICATION => __('Notification bar'),
            self::CONFIRMATION => __('Confirmation bar')
        ];
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        foreach ($this->toArray() as $value => $label) {
            $optionArray[] = ['value' => $value, 'label' => $label];
        }

        return $optionArray;
    }
}
