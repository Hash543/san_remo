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

namespace Mageplaza\RewardPointsUltimate\Model\Api;

use Magento\Checkout\Api\Data\TotalsInformationInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Message\ManagerInterface;
use Mageplaza\RewardPoints\Model\Api\SpendingManagement as RewardSpendingManagement;
use Mageplaza\RewardPointsUltimate\Helper\SellPoint;

/**
 * Class SpendingManagement
 * @package Mageplaza\RewardPointsProUltimate\Model\Api
 */
class SpendingManagement
{
    /**
     * @var \Mageplaza\RewardPointsPro\Helper\Data
     */
    protected $sellPoint;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * SpendingManagement constructor.
     *
     * @param SellPoint $sellPoint
     * @param Session $customerSession
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        SellPoint $sellPoint,
        Session $customerSession,
        ManagerInterface $messageManager
    ) {
        $this->sellPoint = $sellPoint;
        $this->customerSession = $customerSession;
        $this->messageManager = $messageManager;
    }

    /**
     * @param \Mageplaza\RewardPoints\Model\Api\SpendingManagement $subject
     * @param callable $proceed
     * @param $cartId
     * @param TotalsInformationInterface $addressInformation
     * @param $points
     * @param $ruleId
     *
     * @return bool
     */
    public function aroundCalculate(
        RewardSpendingManagement $subject,
        callable $proceed,
        $cartId,
        TotalsInformationInterface $addressInformation,
        $points,
        $ruleId
    ) {
        if ($this->sellPoint->isValid($points, $this->customerSession->getCustomerId())) {
            return $proceed($cartId, $addressInformation, $points, $ruleId);
        }

        $this->messageManager->addNoticeMessage(__('You don\'t have enough points to spend!'));

        return false;
    }
}
