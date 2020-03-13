<?php
/**
 * @author Israel Yasis
 */
namespace Pss\Redsys\Model;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class RedsysModel
 * @package Pss\Redsys\Model
 */
class Redsys extends \Codeko\Redsys\Model\Redsys {
    /**
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * {@inheritdoc}
     */
    public function initialize($paymentAction, $stateObject) {

        $stateObject->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
        $stateObject->setStatus('pending_payment');
        $stateObject->setIsNotified(false);
        try {
            /** @var \Magento\Sales\Model\Order\Payment $payment */
            $payment = $this->getInfoInstance();
            /** @var \Magento\Sales\Model\Order $order */
            $order = $payment->getOrder();
            $order->setCanSendNewEmailFlag(false);
        }catch (LocalizedException $exception) {

        }
        return $this;
    }

}