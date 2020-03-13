<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\Sales\Plugin\Block\Adminhtml\Order;

class View {
    /**
     * @param \Magento\Sales\Block\Adminhtml\Order\View $view
     */
    public function beforeSetLayout(\Magento\Sales\Block\Adminhtml\Order\View $view) {

        $order = $view->getOrder();
        $status = $order->getStatus();
        $payment = $order->getPayment();
        if($payment->getMethod() == \Magento\OfflinePayments\Model\Banktransfer::PAYMENT_METHOD_BANKTRANSFER_CODE
            && $status == 'pending' && $order->canInvoice()) {
            $message ='Estas seguro que quieres aceptar el pedido?';
            $url = $view->getUrl('alfa9_sales/order/acceptPayment', ['order' => $view->getOrderId()]);
            $view->addButton(
                'accept_payment',
                [
                    'label' => __('Acceptar Pedido'),
                    'onclick' => "confirmSetLocation('{$message}', '{$url}')"
                ]
            );
        }
    }
}