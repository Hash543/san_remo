<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
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

namespace Mageplaza\RewardPointsUltimate\Model;

use Mageplaza\RewardPointsUltimate\Api\Data\RewardCustomerInterface;

/**
 * Class Transaction
 * @package Mageplaza\RewardPointsUltimate\Model
 */
class RewardCustomer extends \Mageplaza\RewardPoints\Model\Account implements RewardCustomerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRewardId()
    {
        return $this->getData(self::REWARD_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setRewardId($value)
    {
        return $this->setData(self::REWARD_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerId($value)
    {
        return $this->setData(self::CUSTOMER_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getPointBalance()
    {
        return $this->getData(self::POINT_BALANCE);
    }

    /**
     * {@inheritdoc}
     */
    public function setPointBalance($value)
    {
        return $this->setData(self::POINT_BALANCE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getPointSpent()
    {
        return $this->getData(self::POINT_SPENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setPointSpent($value)
    {
        return $this->setData(self::POINT_SPENT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getNotificationUpdate()
    {
        return $this->getData(self::NOTIFICATION_UPDATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setNotificationUpdate($value)
    {
        return $this->setData(self::NOTIFICATION_UPDATE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getNotificationExpire()
    {
        return $this->getData(self::NOTIFICATION_EXPIRE);
    }

    /**
     * {@inheritdoc}
     */
    public function setNotificationExpire($value)
    {
        return $this->setData(self::NOTIFICATION_EXPIRE, $value);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Mageplaza\RewardPointsUltimate\Api\Data\RewardCustomerExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     *
     * @param \Mageplaza\RewardPointsUltimate\Api\Data\RewardCustomerExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(
        \Mageplaza\RewardPointsUltimate\Api\Data\RewardCustomerExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
