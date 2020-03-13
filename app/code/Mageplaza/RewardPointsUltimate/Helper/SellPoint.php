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

namespace Mageplaza\RewardPointsUltimate\Helper;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Class SellPoint
 * @package Mageplaza\RewardPointsUltimate\Helper
 */
class SellPoint extends AbstractHelper
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Mageplaza\RewardPointsUltimate\Helper\Data
     */
    protected $helperData;

    /**
     * SellPoint constructor.
     *
     * @param Context $context
     * @param ProductRepository $productRepository
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        ProductRepository $productRepository,
        Data $helperData
    ) {
        $this->productRepository = $productRepository;
        $this->helperData = $helperData;

        parent::__construct($context);
    }

    /**
     * @param $productId
     * @param int $qty
     *
     * @return float|int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRewardSellProductById($productId, $qty = 1)
    {
        if ($productId) {
            $product = $this->productRepository->getById($productId);
            $mpPoint = $product->getMpRewardSellProduct();
            if ($mpPoint > 0) {
                return $mpPoint * $qty;
            }
        }

        return 0;
    }

    /**
     * @param int $sellPoints
     * @param string $customerId
     *
     * @return bool
     */
    public function isValid($sellPoints = 0, $customerId = '')
    {
        $accountHelper = $this->helperData->getAccountHelper();
        $rewardCustomer = $customerId ? $accountHelper->getByCustomerId($customerId) : $accountHelper->get();
        if ($rewardCustomer->getPointBalance() > 0) {
            $quote = $this->helperData->getQuote();
            $sellPoints += $quote->getMpSpent();
            foreach ($quote->getAllItems() as $item) {
                if ($item->getMpRewardSellPoints() > 0) {
                    $sellPoints += ($item->getMpRewardSellPoints() * $item->getQty());
                }
            }
        }
        if ($sellPoints > 0 && $rewardCustomer->getPointBalance() < $sellPoints) {
            return false;
        }

        return true;
    }

    /**
     * @param $item
     * @param bool $isCalculateQty
     *
     * @return bool|string
     */
    public function getMpRewardSellPoints($item, $isCalculateQty = false)
    {
        $itemPoint = $item->getMpRewardSellPoints();
        if ($itemPoint <= 0) {
            return false;
        }
        $qty = 1;
        if ($isCalculateQty) {
            if ($item instanceof \Magento\Sales\Model\Order\Item) {
                $qty = $item->getQtyOrdered();
            } else {
                $qty = $item->getQty();
            }
        }
        $mpRewardSellPoints = $this->helperData->getPointHelper()->format(($itemPoint * $qty));

        return '<span class="price-excluding-tax" >
                    <span class="cart-price">
                        <span class="price">' . $mpRewardSellPoints . '</span>
                    </span>
                </span>';
    }

    /**
     * @param $items
     *
     * @return mixed
     */
    public function setMpRewardSellPoints($items)
    {
        $mpRewardSellPoints = false;
        foreach ($items as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            $orderItem = $item->getOrderItem();
            $pointOrderItem = $orderItem->getMpRewardSellPoints();
            if ($pointOrderItem > 0) {
                $mpRewardSellPoints = true;
                $item->setMpRewardSellPoints($pointOrderItem);
            }
        }

        return $mpRewardSellPoints;
    }
}
