<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\Sales\Model\Order\Email\Sender;
use Magento\Framework\DataObject;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;

/**
 * Class OrderSender
 * @package Alfa9\Sales\Model\Order\Email\Sender
 */
class OrderSender extends \Magento\Sales\Model\Order\Email\Sender\OrderSender {
    /**
     * @var \Magento\Email\Model\ResourceModel\Template\CollectionFactory
     */
    protected $templateCollectionFactory;
    /**
     * OrderSender constructor.
     * @param \Magento\Sales\Model\Order\Email\Container\Template $templateContainer
     * @param \Magento\Sales\Model\Order\Email\Container\OrderIdentity $identityContainer
     * @param \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param Renderer $addressRenderer
     * @param PaymentHelper $paymentHelper
     * @param OrderResource $orderResource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Email\Model\ResourceModel\Template\CollectionFactory  $templateCollectionFactory
     */
    public function __construct(
        \Magento\Sales\Model\Order\Email\Container\Template $templateContainer,
        \Magento\Sales\Model\Order\Email\Container\OrderIdentity $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory  $senderBuilderFactory,
        \Psr\Log\LoggerInterface $logger,
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper, OrderResource $orderResource,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Email\Model\ResourceModel\Template\CollectionFactory $templateCollectionFactory
    ) {
        $this->templateCollectionFactory = $templateCollectionFactory;
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory, $logger, $addressRenderer, $paymentHelper, $orderResource, $globalConfig, $eventManager);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    protected function prepareTemplate(\Magento\Sales\Model\Order $order)
    {

        $transport = [
            'order' => $order,
            'billing' => $order->getBillingAddress(),
            'payment_html' => $this->getPaymentHtml($order),
            'store' => $order->getStore(),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
            'grand_total_formatted' => $order->formatPrice($order->getGrandTotal())
        ];
        $transportObject = new DataObject($transport);

        /**
         * Event argument `transport` is @deprecated. Use `transportObject` instead.
         */
        $this->eventManager->dispatch(
            'email_order_set_template_vars_before',
            ['sender' => $this, 'transport' => $transportObject, 'transportObject' => $transportObject]
        );

        $this->templateContainer->setTemplateVars($transportObject->getData());
        $this->templateContainer->setTemplateOptions($this->getTemplateOptions());

        if ($order->getCustomerIsGuest()) {
            $customerName = $order->getBillingAddress()->getName();
        } else {
            $customerName = $order->getCustomerName();
        }
        $templateId = $this->getTemplateId($order);
        $this->identityContainer->setCustomerName($customerName);
        $this->identityContainer->setCustomerEmail($order->getCustomerEmail());
        $this->templateContainer->setTemplateId($templateId);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return int
     */
    private function getTemplateId(\Magento\Sales\Model\Order $order) {
        if ($order->getCustomerIsGuest()) {
            $templateId = $this->identityContainer->getGuestTemplateId();
        } else {
            $templateId = $this->identityContainer->getTemplateId();
        }
        $paymentMethod = $order->getPayment()->getMethodInstance();
        $templateSubject = "SR > New Order Email - ";
        if($paymentMethod) {
            $paymentCode = $paymentMethod->getCode();
            $templateSubject = $templateSubject.$paymentCode;
        }
        $templateCollection = $this->templateCollectionFactory->create();
        $templateCollection->addFieldToFilter('template_code', ['eq' => $templateSubject])->load();
        if($templateCollection->count() > 0) {
            $templateId = $templateCollection->getFirstItem()->getData('template_id');
        }
        return $templateId;
    }
}