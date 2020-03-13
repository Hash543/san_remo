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

namespace Mageplaza\RewardPointsUltimate\Api\Data;

/**
 * Interface RewardCustomerInterface
 * @package Mageplaza\RewardPointsUltimate\Api\Data
 */
interface RewardCustomerInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const REWARD_ID           = 'reward_id';
    const CUSTOMER_ID         = 'customer_id';
    const POINT_BALANCE       = 'point_balance';
    const POINT_SPENT         = 'point_spent';
    const NOTIFICATION_UPDATE = 'notification_update';
    const NOTIFICATION_EXPIRE = 'notification_expire';

    /**
     * @return int
     */
    public function getRewardId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setRewardId($value);

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setCustomerId($value);

    /**
     * @return int
     */
    public function getPointBalance();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setPointBalance($value);

    /**
     * @return int
     */
    public function getPointSpent();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setPointSpent($value);

    /**
     * @return string
     */
    public function getNotificationUpdate();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setNotificationUpdate($value);

    /**
     * @return string
     */
    public function getNotificationExpire();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setNotificationExpire($value);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Mageplaza\RewardPointsUltimate\Api\Data\RewardCustomerExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Mageplaza\RewardPointsUltimate\Api\Data\RewardCustomerExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(
        \Mageplaza\RewardPointsUltimate\Api\Data\RewardCustomerExtensionInterface $extensionAttributes
    );
}
