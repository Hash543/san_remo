<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Model\Source;

/**
 * Class CustomerEvents
 * @package Mageplaza\RewardPointsUltimate\Model\Source
 */
class CustomerEvents extends AbstractSource
{
    const SIGN_UP                 = 1;
    const NEWSLETTER              = 2;
    const PRODUCT_REVIEW          = 3;
    const SEND_EMAIL_TO_FRIEND    = 4;
    const CUSTOMER_BIRTHDAY       = 5;
    const LIKE_PAGE_WITH_FACEBOOK = 6;
    const TWEET_PAGE_WITH_TWITTER = 7;
    const GOOGLE_PLUS             = 8;
    const SHARE_PURCHASE_FACEBOOK = 9;
    // const REFER_FRIEND_SIGN_UP = 10;

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public static function getOptionArray()
    {
        return [
            self::SIGN_UP                 => __('Create a new account'),
            self::NEWSLETTER              => __('Sign up for a newsletter'),
            self::PRODUCT_REVIEW          => __('Write a review'),
            self::SEND_EMAIL_TO_FRIEND    => __('Send email to friends'),
            self::CUSTOMER_BIRTHDAY       => __('Customer\'s birthday'),
            self::LIKE_PAGE_WITH_FACEBOOK => __('Like a page with Facebook'),
            self::TWEET_PAGE_WITH_TWITTER => __('Tweet a page with Twitter'),
            self::GOOGLE_PLUS             => __('+1\' a page with Google+'),
            self::SHARE_PURCHASE_FACEBOOK => __('Share a purchase on Facebook'),
            //self::REFER_FRIEND_SIGN_UP    => __('Refer Friend sign up'),

        ];
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function toOptionArray()
    {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[$index] = $value;
        }

        return $result;
    }
}
