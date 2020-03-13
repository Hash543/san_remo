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
 * Class CustomerRegisterSuccess
 * @package Mageplaza\RewardPointsUltimate\Observer
 */
class CustomerRegisterSuccess implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var BehaviorFactory
     */
    protected $behaviorFactory;

    /**
     * CustomerRegisterSuccess constructor.
     *
     * @param HelperData $helperData
     * @param BehaviorFactory $behaviorFactory
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
        if ($this->helperData->isEnabled()) {
            $this->behaviorSignUp($observer->getEvent()->getCustomer());
            $this->setCookieReferer($observer);
        }
    }

    /**
     * @param $customer
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function behaviorSignUp($customer)
    {
        $pointSignUp = $this->behaviorFactory->create()->getPointByAction(CustomerEvents::SIGN_UP);
        if ($pointSignUp) {
            $this->helperData->getTransaction()->createTransaction(
                HelperData::ACTION_SIGN_UP,
                $customer,
                new DataObject(['point_amount' => $pointSignUp])
            );
        }
    }

    /**
     * @param $observer
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function setCookieReferer($observer)
    {
        $accountController = $observer->getEvent()->getAccountController();
        $referCodeOrEmail = trim($accountController->getRequest()->getParam('mp_refer'));
        $referCode = $this->helperData->getCryptHelper()->checkReferCodeOrEmail($referCodeOrEmail);
        if ($referCode) {
            $this->helperData->getCookieHelper()->set($referCode);
        }
    }
}
