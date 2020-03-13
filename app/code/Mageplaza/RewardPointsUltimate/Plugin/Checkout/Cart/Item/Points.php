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

namespace Mageplaza\RewardPointsUltimate\Plugin\Checkout\Cart\Item;

use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;
use Mageplaza\RewardPointsUltimate\Helper\SellPoint;
use Psr\Log\LoggerInterface;

/**
 * Class Points
 * @package Mageplaza\RewardPointsUltimate\Plugin\Chẹcout\Cart\Item
 */
class Points
{
    /**
     * @var \Mageplaza\RewardPointsUltimate\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Mageplaza\RewardPointsUltimate\Helper\SellPoint
     */
    protected $sellPoint;

    /**
     * Points constructor.
     *
     * @param HelperData $helperData
     * @param LoggerInterface $logger
     * @param SellPoint $sellPoint
     */
    public function __construct(HelperData $helperData, LoggerInterface $logger, SellPoint $sellPoint)
    {
        $this->helperData = $helperData;
        $this->logger = $logger;
        $this->sellPoint = $sellPoint;
    }

    /**
     * @param \Magento\Checkout\Block\Cart\Item\Renderer $subject
     * @param $result
     *
     * @return mixed|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetUnitPriceHtml(\Magento\Checkout\Block\Cart\Item\Renderer $subject, $result)
    {
        return $this->getRewardSellProduct($result, $subject, false);
    }

    /**
     * @param \Magento\Checkout\Block\Cart\Item\Renderer $subject
     * @param $result
     *
     * @return mixed|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetRowTotalHtml(\Magento\Checkout\Block\Cart\Item\Renderer $subject, $result)
    {
        return $this->getRewardSellProduct($result, $subject, true);
    }

    /**
     * @param $result
     * @param $subject
     * @param $isQty
     *
     * @return mixed|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRewardSellProduct($result, $subject, $isQty)
    {
        $qty = $isQty ? $subject->getItem()->getQty() : 1;
        $mpRewardSellProduct = $this->sellPoint->getRewardSellProductById($subject->getItem()->getProductId(), $qty);
        if ($mpRewardSellProduct) {
            return $this->getFormatHtml($this->helperData->getPointHelper()->format($mpRewardSellProduct));
        }

        return $result;
    }

    /**
     * @param $mpRewardSellProduct
     *
     * @return string
     */
    public function getFormatHtml($mpRewardSellProduct)
    {
        return '<span class="price-excluding-tax">
                    <span class="cart-price">
                        <span class="price">' . $mpRewardSellProduct . '</span>
                    </span>
            </span>';
    }
}
