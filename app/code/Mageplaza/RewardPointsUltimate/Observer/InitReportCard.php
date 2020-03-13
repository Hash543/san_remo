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

namespace Mageplaza\RewardPointsUltimate\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class InitReportCard
 * @package Mageplaza\RewardPointsUltimate\Observer
 */
class InitReportCard implements ObserverInterface
{
    const MP_REWARD_EARNED = 'Mageplaza\RewardPointsUltimate\Block\Adminhtml\Reports\Dashboard\Earned';
    const MP_REWARD_SPENT  = 'Mageplaza\RewardPointsUltimate\Block\Adminhtml\Reports\Dashboard\EarnedAndSpentRatio';

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(EventObserver $observer)
    {
        $carts = $observer->getEvent()->getCards();
        $carts->setMpRewardEarned(self::MP_REWARD_EARNED)->setMpRewardSpent(self::MP_REWARD_SPENT);
    }
}
