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

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;
use Mageplaza\RewardPointsUltimate\Model\BehaviorFactory;
use Mageplaza\RewardPointsUltimate\Model\Source\CustomerEvents;

/**
 * Class NewsletterSubscriber
 * @package Mageplaza\RewardPointsUltimate\Observer
 */
class NewsletterSubscriber implements ObserverInterface
{
    /**
     * @var \Mageplaza\RewardPointsUltimate\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Mageplaza\RewardPointsUltimate\Model\BehaviorFactory
     */
    protected $behaviorFactory;

    /**
     * NewsletterSubscriber constructor.
     *
     * @param \Mageplaza\RewardPointsUltimate\Helper\Data $helperData
     * @param \Mageplaza\RewardPointsUltimate\Model\BehaviorFactory $behaviorFactory
     */
    public function __construct(
        HelperData $helperData,
        BehaviorFactory $behaviorFactory
    ) {
        $this->helperData = $helperData;
        $this->behaviorFactory = $behaviorFactory;
    }

    /**
     * @param EventObserver $observer
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        $subscriber = $observer->getEvent()->getSubscriber();
        $pointSubscriber = $this->behaviorFactory->create()->getPointByAction(CustomerEvents::NEWSLETTER);
        $accountHelper = $this->helperData->getAccountHelper();
        if ($subscriber->getCustomerId()) {
            $customerId = $subscriber->getCustomerId();
            $customer = $accountHelper->getCustomerById($subscriber->getCustomerId());
        } else {
            $customer = $accountHelper->getCustomerSession();
            $customerId = $customer->getId();
        }
        if ($pointSubscriber > 0 && $this->helperData->isEnabled() && $subscriber->isSubscribed() && $subscriber->isStatusChanged() && $customerId) {
            $rewardCustomer = $accountHelper->getByCustomerId($customerId);
            if (($rewardCustomer->getId() && !$this->checkHasSubscribe($customerId)) || !$rewardCustomer->getId()) {
                $this->helperData->getTransaction()->createTransaction(
                    HelperData::ACTION_NEWSLETTER,
                    $customer,
                    new DataObject(
                        [
                            'point_amount'  => $pointSubscriber,
                            'extra_content' => [
                                'subscriber_id' => $subscriber->getId()
                            ]
                        ]
                    )
                );
            }
        }
    }

    /**
     * @param $customerId
     *
     * @return bool
     */
    public function checkHasSubscribe($customerId)
    {
        $filters = [
            'action_code' => HelperData::ACTION_NEWSLETTER,
            'customer_id' => $customerId
        ];
        $newsletterTransaction = $this->helperData->getTransactionByFieldToFilter($filters, false, true);

        return (bool) $newsletterTransaction->getSize();
    }
}
