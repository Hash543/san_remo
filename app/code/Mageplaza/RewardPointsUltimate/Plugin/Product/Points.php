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

namespace Mageplaza\RewardPointsUltimate\Plugin\Product;

use Magento\Bundle\Model\Option;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;
use Psr\Log\LoggerInterface;

/**
 * Class Points
 * @package Mageplaza\RewardPointsUltimate\Plugin\Product
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
     * Points constructor.
     *
     * @param HelperData $helperData
     * @param LoggerInterface $logger
     */
    public function __construct(HelperData $helperData, LoggerInterface $logger)
    {
        $this->helperData = $helperData;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Pricing\Render\Amount $subject
     * @param $result
     *
     * @return float|string
     */
    public function afterToHtml(\Magento\Framework\Pricing\Render\Amount $subject, $result)
    {
        $productOption = $subject->getSaleableItem()->getOption();
        if (($productOption && $productOption instanceof Option)) {
            return $result;
        }
        try {
            if ($this->helperData->isEnabled() && $subject->getSaleableItem()->getMpRewardSellProduct() > 0) {
                if ($subject->getData('price_type') != 'finalPrice') {
                    return '';
                }
                if ($subject->getData('price_type') == 'finalPrice') {
                    $html = '<span class="price-container price-final_price">
                                    <span class="price">' . $this->helperData->getPointHelper()->format($subject->getSaleableItem()->getMpRewardSellProduct()) . '</span>
                            </span>
                         ';

                    return $html;
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return $result;
    }
}
