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

namespace Mageplaza\RewardPointsUltimate\Plugin\Minicart\Item;

use Magento\Checkout\CustomerData\Cart;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;
use Mageplaza\RewardPointsUltimate\Helper\SellPoint;
use Psr\Log\LoggerInterface;

/**
 * Class Points
 * @package Mageplaza\RewardPointsUltimate\Plugin\Minicart\Item
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
     * @param \Mageplaza\RewardPointsUltimate\Helper\Data $helperData
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Mageplaza\RewardPointsUltimate\Helper\SellPoint $sellPoint
     */
    public function __construct(HelperData $helperData, LoggerInterface $logger, SellPoint $sellPoint)
    {
        $this->helperData = $helperData;
        $this->logger = $logger;
        $this->sellPoint = $sellPoint;
    }

    /**
     * @param Cart $subject
     * @param $result
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetSectionData(Cart $subject, $result)
    {
        if ($this->helperData->isEnabled()) {
            $items = $result['items'];
            $pointHelper = $this->helperData->getPointHelper();
            foreach ($items as $key => $item) {
                if (isset($item['product_id'])) {
                    $mpRewardSellProduct = $this->sellPoint->getRewardSellProductById($item['product_id']);
                    if ($mpRewardSellProduct) {
                        $html = '<span class="minicart-price"><span class="price">' . $pointHelper->format($mpRewardSellProduct) . '</span></span>';
                        $items[$key]['product_price'] = $html;
                    }
                }
            }
            $result['items'] = $items;
        }

        return $result;
    }
}
