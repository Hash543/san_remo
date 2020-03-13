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

use Mageplaza\RewardPointsUltimate\Api\Data\TransactionInterface;

/**
 * Class Transaction
 * @package Mageplaza\RewardPointsUltimate\Model
 */
class Transaction extends \Mageplaza\RewardPoints\Model\Transaction implements TransactionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTransactionId()
    {
        return $this->getData(self::TRANSACTION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setTransactionId($value)
    {
        return $this->setData(self::TRANSACTION_ID, $value);
    }

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
    public function getActionCode()
    {
        return $this->getData(self::ACTION_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setActionCode($value)
    {
        return $this->setData(self::ACTION_CODE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getActionType()
    {
        return $this->getData(self::ACTION_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setActionType($value)
    {
        return $this->setData(self::ACTION_TYPE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($value)
    {
        return $this->setData(self::STORE_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getPointAmount()
    {
        return $this->getData(self::POINT_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setPointAmount($value)
    {
        return $this->setData(self::POINT_AMOUNT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getPointRemaining()
    {
        return $this->getData(self::POINT_REMAINING);
    }

    /**
     * {@inheritdoc}
     */
    public function setPointRemaining($value)
    {
        return $this->setData(self::POINT_REMAINING, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getPointUsed()
    {
        return $this->getData(self::POINT_USED);
    }

    /**
     * {@inheritdoc}
     */
    public function setPointUsed($value)
    {
        return $this->setData(self::POINT_USED, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($value)
    {
        return $this->setData(self::STATUS, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderId($value)
    {
        return $this->setData(self::ORDER_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($value)
    {
        return $this->setData(self::CREATED_AT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getExpirationDate()
    {
        return $this->getData(self::EXPIRATION_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setExpirationDate($value)
    {
        return $this->setData(self::EXPIRATION_DATE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getExpireEmailSent()
    {
        return $this->getData(self::EXPIRE_EMAIL_SENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setExpireEmailSent($value)
    {
        return $this->setData(self::EXPIRE_EMAIL_SENT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtraContent()
    {
        return $this->getData(self::EXTRA_CONTENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setExtraContent($value)
    {
        return $this->setData(self::EXTRA_CONTENT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getComment()
    {
        return $this->getData(self::COMMENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setComment($value)
    {
        return $this->setData(self::COMMENT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getExpireAfter()
    {
        return $this->getData(self::EXPIRE_AFTER);
    }

    /**
     * {@inheritdoc}
     */
    public function setExpireAfter($value)
    {
        return $this->setData(self::EXPIRE_AFTER, $value);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Mageplaza\RewardPointsUltimate\Api\Data\TransactionExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     *
     * @param \Mageplaza\RewardPointsUltimate\Api\Data\TransactionExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(
        \Mageplaza\RewardPointsUltimate\Api\Data\TransactionExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
