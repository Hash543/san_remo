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

use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Review\Model\Review;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Mageplaza\RewardPoints\Model\Source\Status;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;
use Mageplaza\RewardPointsUltimate\Model\BehaviorFactory;
use Mageplaza\RewardPointsUltimate\Model\Source\CustomerEvents;
use Psr\Log\LoggerInterface;

/**
 * Class ReviewProduct
 * @package Mageplaza\RewardPointsUltimate\Observer
 */
class ReviewProduct implements ObserverInterface
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
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * ReviewProduct constructor.
     *
     * @param HelperData $helperData
     * @param BehaviorFactory $behaviorFactory
     * @param LoggerInterface $logger
     * @param Session $customerSession
     * @param ProductFactory $productFactory
     * @param CollectionFactory $orderCollectionFactory
     */
    public function __construct(
        HelperData $helperData,
        BehaviorFactory $behaviorFactory,
        LoggerInterface $logger,
        Session $customerSession,
        ProductFactory $productFactory,
        CollectionFactory $orderCollectionFactory
    ) {
        $this->helperData = $helperData;
        $this->behaviorFactory = $behaviorFactory;
        $this->logger = $logger;
        $this->customerSession = $customerSession;
        $this->productFactory = $productFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        try {
            $review = $observer->getEvent()->getDataObject();

            if ($this->helperData->isEnabled() &&
                $review->getCustomerId() &&
                $review->getOrigData('status_id') != $review->getStatusId()
            ) {
                if ($review->isApproved()) {
                    if ($transaction = $this->checkProductHasReview($review, Status::PENDING)) {
                        $transaction->complete();
                    }
                } elseif ($review->getStatusId() == Review::STATUS_PENDING) {
                    /** @var \Mageplaza\RewardPointsUltimate\Model\Behavior $behavior */
                    $behavior = $this->behaviorFactory->create()
                        ->getBehaviorRuleByAction(CustomerEvents::PRODUCT_REVIEW, true);
                    if ($behavior->getId()) {
                        if ($this->checkProductHasReview($review, false)) {
                            return $this;
                        }

                        $pointAmount = $behavior->getPointAmount();
                        if ($behavior->getMaxPoint() > 0) {
                            $pointAmount = $behavior->checkMaxPoint(
                                HelperData::ACTION_REVIEW_PRODUCT,
                                $review->getCustomerId()
                            );
                        }

                        if ($this->checkMinWords($behavior->getMinWords(), $review->getDetail()) && $pointAmount) {
                            if ($this->checkPurchased($behavior, $review)) {
                                $this->helperData->getTransaction()->createTransaction(
                                    HelperData::ACTION_REVIEW_PRODUCT,
                                    $this->helperData->getAccountHelper()->getCustomerById($review->getCustomerId()),
                                    new DataObject(
                                        [
                                            'point_amount'  => $pointAmount,
                                            'extra_content' => [
                                                'product_id' => $review->getEntityPkValue()
                                            ]
                                        ]
                                    )
                                );
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return $this;
    }

    /**
     * @param $behavior
     * @param $review
     *
     * @return bool
     */
    public function checkPurchased($behavior, $review)
    {
        if ($behavior->getIsPurchased()) {
            $orders = $this->orderCollectionFactory
                ->create()
                ->addFieldToFilter(
                    'customer_id',
                    $review->getCustomerId()
                )
                ->setOrder(
                    'created_at',
                    'desc'
                );

            if ($orders->getSize() > 0) {
                foreach ($orders as $order) {
                    /**
                     * @var \Magento\Sales\Model\Order $order
                     */
                    foreach ($order->getAllItems() as $item) {
                        if ($item->getProductId() === $review->getEntityPkValue() &&
                            $item->getQtyOrdered() > $item->getQtyRefunded()
                        ) {
                            return true;
                        }
                    }
                }
            }

            return false;
        }

        return true;
    }

    /**
     * @param $review
     * @param $status
     *
     * @return bool
     */
    public function checkProductHasReview($review, $status)
    {
        $filters = [
            'action_code' => HelperData::ACTION_REVIEW_PRODUCT,
            'customer_id' => $review->getCustomerId()
        ];
        if ($status) {
            $filters['status'] = $status;
        }

        return $this->helperData->getTransactionByFilter(
            $filters,
            true,
            false,
            [
                'field' => 'product_id',
                'value' => $review->getEntityPkValue()
            ]
        );
    }

    /**
     * @param $minWord
     * @param $detail
     *
     * @return bool
     */
    public function checkMinWords($minWord, $detail)
    {
        if ($minWord) {
            return str_word_count(strip_tags(trim($detail))) >= $minWord;
        }

        return false;
    }
}
