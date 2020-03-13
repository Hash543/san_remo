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

namespace Mageplaza\RewardPointsUltimate\Controller;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DataObject;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;
use Mageplaza\RewardPointsUltimate\Model\BehaviorFactory;
use Mageplaza\RewardPointsUltimate\Model\Source\CustomerEvents;

/**
 * Class Facebook
 * @package Mageplaza\RewardPointsUltimate\Controller\Socials
 */
abstract class Socials extends Action
{
    /**
     * @var \Mageplaza\RewardPointsUltimate\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Mageplaza\RewardPointsUltimate\Model\Behavior
     */
    protected $behaviorFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var string
     */
    protected $response = '';

    /**
     * Socials constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param HelperData $helperData
     * @param BehaviorFactory $behaviorFactory
     * @param Session $customerSession
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        HelperData $helperData,
        BehaviorFactory $behaviorFactory,
        Session $customerSession
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->helperData = $helperData;
        $this->behaviorFactory = $behaviorFactory;
        $this->customerSession = $customerSession;

        return parent::__construct($context);
    }

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();
            if ($this->helperData->getAccountHelper()->isCustomerLoggedIn()) {
                $data = $this->getRequest()->getPostValue();
                if (isset($data['current_url'])) {
                    $customer = $this->customerSession->getCustomer();
                    if ($customer->getId()) {
                        $behavior = $this->behaviorFactory->create()->getBehaviorRuleByAction(
                            $this->getBehaviorAction(),
                            true,
                            $customer->getGroupId()
                        );
                        if ($behavior->getId()) {
                            $hasUrl = $this->hasUrl($customer->getId(), $data['current_url']);
                            $this->processAction($customer, $hasUrl, $behavior, $data['current_url']);
                        }
                    }
                }
            } else {
                $this->messageManager->addNotice(__("Please log in to earn points for this action!"));
            }
        } catch (\Exception $e) {
            $this->messageManager->addNotice(__("Something went wrong while processing this action"));
        }

        return $resultJson->setData([]);
    }

    /**
     * @return int
     */
    public function getBehaviorAction()
    {
        return CustomerEvents::LIKE_PAGE_WITH_FACEBOOK;
    }

    /**
     * @return string
     */
    public function getTransactionAction()
    {
        return HelperData::ACTION_LIKE_FACEBOOK;
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getHasUrlMessage()
    {
        return __("You've already liked this page.");
    }

    /**
     * @param $pointFormat
     *
     * @return \Magento\Framework\Phrase
     */
    public function getCompleteMessageByAction($pointFormat)
    {
        return __("You've earned %1 for liking this page!", $pointFormat);
    }

    /**
     * @param $customer
     * @param $hasUrl
     * @param $behavior
     * @param $url
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processAction($customer, $hasUrl, $behavior, $url)
    {
        if ($hasUrl) {
            $this->messageManager->addNoticeMessage($this->getHasUrlMessage());

            return $this;
        }

        if ($behavior->getMinInterval() && $this->getLastTime()) {
            if ((time() - $this->getLastTime()) < $behavior->getMinInterval()) {
                $this->messageManager->addNoticeMessage(__(
                    "You have to wait at least %1 seconds for the next action!",
                    $behavior->getMinInterval()
                ));

                return $this;
            }
        }

        $pointAmount = $behavior->getPointAmount();
        if ($behavior->getMaxPoint() > 0) {
            $pointAmount = $behavior->checkMaxPoint(
                $this->getTransactionAction(),
                $this->customerSession->getCustomer()->getId()
            );
        }

        if ($pointAmount) {
            $this->_createTransaction(
                $customer,
                [
                    'point_amount'  => $pointAmount,
                    'extra_content' => ['page' => $url]
                ],
                $behavior->getMinInterval()
            );
        }

        return $this;
    }

    /**
     * @param $customer
     * @param $data
     * @param $isCheckTime
     *
     * @return \Mageplaza\RewardPoints\Model\Transaction
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _createTransaction($customer, $data, $isCheckTime)
    {
        $transaction = $this->helperData->getTransaction()->createTransaction(
            $this->getTransactionAction(),
            $customer,
            new DataObject($data)
        );

        if ($transaction->getId()) {
            if ($isCheckTime) {
                $this->setLastTime();
            }
            $pointFormat = $this->helperData->getPointHelper()->format($transaction->getPointAmount());
            $this->messageManager->addSuccessMessage($this->getCompleteMessageByAction($pointFormat));
        }

        return $transaction;
    }

    /**
     * Set last time
     * default process for facebook
     * @return mixed
     */
    public function setLastTime()
    {
        return $this->customerSession->setFacebookLastTime(time());
    }

    /**
     * Get last time
     * default process for facebook
     * @return mixed
     */
    public function getLastTime()
    {
        return $this->customerSession->getFacebookLastTime();
    }

    /**
     * @param $status
     * @param $message
     *
     * @return array
     */
    public function setResponse($status, $message)
    {
        return $this->response = ['status' => $status, 'message' => $message];
    }

    /**
     * @param $customerId
     * @param $url
     *
     * @return bool
     */
    public function hasUrl($customerId, $url)
    {
        $filters = [
            'action_code' => $this->getTransactionAction(),
            'customer_id' => $customerId
        ];
        $transactions = $this->helperData->getTransactionByFieldToFilter($filters, true, false);
        foreach ($transactions as $transaction) {
            $extraContent = $this->helperData->getExtraContent($transaction);
            if (isset($extraContent['page']) && $extraContent['page'] == $url) {
                return true;
            }
        }

        return false;
    }
}
