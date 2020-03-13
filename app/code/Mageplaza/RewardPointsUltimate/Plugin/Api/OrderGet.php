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

namespace Mageplaza\RewardPointsUltimate\Plugin\Api;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderItemExtensionFactory;
use Mageplaza\RewardPointsUltimate\Model\OrderFactory;
use Mageplaza\RewardPointsUltimate\Model\OrderItemFactory;

/**
 * Class OrderGet
 * @package Mageplaza\RewardPointsUltimate\Plugin
 */
class OrderGet
{
    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var OrderItemFactory
     */
    protected $orderItemFactory;

    /** @var OrderExtensionFactory */
    protected $orderExtensionFactory;

    /** @var OrderItemExtensionFactory */
    protected $orderItemExtensionFactory;

    /**
     * OrderGet constructor.
     *
     * @param OrderFactory $orderFactory
     * @param OrderItemFactory $orderItemFactory
     * @param OrderExtensionFactory $orderExtensionFactory
     * @param OrderItemExtensionFactory $orderItemExtensionFactory
     */
    public function __construct(
        OrderFactory $orderFactory,
        OrderItemFactory $orderItemFactory,
        OrderExtensionFactory $orderExtensionFactory,
        OrderItemExtensionFactory $orderItemExtensionFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->orderItemFactory = $orderItemFactory;
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->orderItemExtensionFactory = $orderItemExtensionFactory;
    }

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderInterface $resultOrder
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $resultOrder
    ) {
        $resultOrder = $this->getOrderReward($resultOrder);
        $resultOrder = $this->getOrderItemReward($resultOrder);

        return $resultOrder;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    protected function getOrderReward(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $extensionAttributes = $order->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getMpReward()) {
            return $order;
        }

        try {
            /** @var \Mageplaza\RewardPointsUltimate\Model\Order $orderData */
            $orderData = $this->orderFactory->create()->load($order->getEntityId());
        } catch (NoSuchEntityException $e) {
            return $order;
        }

        /** @var \Magento\Sales\Api\Data\OrderExtension $orderExtension */
        $orderExtension = $extensionAttributes ? $extensionAttributes : $this->orderExtensionFactory->create();
        $orderExtension->setMpReward($orderData);
        $order->setExtensionAttributes($orderExtension);

        return $order;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    protected function getOrderItemReward(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $orderItems = $order->getItems();
        if (null !== $orderItems) {
            /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
            foreach ($orderItems as $orderItem) {
                $extensionAttributes = $orderItem->getExtensionAttributes();
                if ($extensionAttributes && $extensionAttributes->getMpReward()) {
                    continue;
                }

                try {
                    /** @var \Mageplaza\RewardPointsUltimate\Model\OrderItem $orderItemData */
                    $orderItemData = $this->orderItemFactory->create()->load($orderItem->getItemId());
                } catch (NoSuchEntityException $e) {
                    continue;
                }

                /** @var \Magento\Sales\Api\Data\OrderItemExtension $orderItemExtension */
                $orderItemExtension = $extensionAttributes ? $extensionAttributes : $this->orderItemExtensionFactory->create();
                $orderItemExtension->setMpReward($orderItemData);
                $orderItem->setExtensionAttributes($orderItemExtension);
            }
        }

        return $order;
    }

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $resultOrder
     *
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Model\ResourceModel\Order\Collection $resultOrder
    ) {
        /** @var  $order */
        foreach ($resultOrder->getItems() as $order) {
            $this->afterGet($subject, $order);
        }

        return $resultOrder;
    }
}
