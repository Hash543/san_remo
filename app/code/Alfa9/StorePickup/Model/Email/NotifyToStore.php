<?php

namespace Alfa9\StorePickup\Model\Email;

use Alfa9\StorePickup\Model\Carrier\StorePickup;
use PSS\ShippingMethod\Model\Carrier\ReserveAndCollect;
use Alfa9\StoreInfo\Api\Data\StockistInterface;

class NotifyToStore {
    /**
     * Email constants
     */
    const PATH_XML_EMAIL_NEW_ORDER_PICK_UP = 'carriers/storepickup/order_new_template';
    const PATH_XML_EMAIL_NEW_ORDER_GUEST_PICK_UP = 'carriers/storepickup/order_new_template_guest';
    const PATH_XML_EMAIL_STORE_PICKUP_CANCEL = 'carriers/storepickup/storepickup_cancel_template';
    const PATH_XML_EMAIL_STORE_PICKUP_CONFIRM = 'carriers/storepickup/storepickup_confirm_template';
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilderFactory
     */
    protected $transportBuilderFactory;
    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentHelper;
    /**
     * @var \Magento\Sales\Model\Order\Address\Renderer
     */
    protected $addressRenderer;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Alfa9\StoreInfo\Model\ResourceModel\Stores\CollectionFactory
     */
    private $collectionStoreFactory;

    /**
     * NotifyToStore constructor.
     * @param \Magento\Framework\Mail\Template\TransportBuilderFactory $transportBuilderFactory
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Alfa9\StoreInfo\Model\ResourceModel\Stores\CollectionFactory $collectionStoreFactory
     */
    public function __construct(
        \Magento\Framework\Mail\Template\TransportBuilderFactory $transportBuilderFactory,
        \Magento\Framework\UrlInterface $url,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Alfa9\StoreInfo\Model\ResourceModel\Stores\CollectionFactory $collectionStoreFactory
    ) {
        $this->url = $url;
        $this->transportBuilderFactory = $transportBuilderFactory;
        $this->paymentHelper = $paymentHelper;
        $this->addressRenderer = $addressRenderer;
        $this->scopeConfig = $scopeConfig;
        $this->collectionStoreFactory = $collectionStoreFactory;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @throws \Magento\Framework\Exception\MailException
     */
    public function send(\Magento\Sales\Model\Order $order)
    {
        try {
            $emailData = $this->prepareTemplate($order);
            $addTo = $this->getAddTo($order);
            $multipleBcc = $this->scopeConfig->getValue('carriers/storepickup/bcc_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $multipleBcc = explode(',', $multipleBcc);

            /** @var \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder */
            $transportBuilder = $this->transportBuilderFactory->create();
            $transportBuilder->setTemplateIdentifier(
                $this->getEmailTemplate($order)
            )->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $order->getStoreId()
                ]
            )->setFrom(
                $this->getFrom()
            )->setTemplateVars(
                $emailData->getData()
            )->addTo($addTo['address'], $addTo['name']) //TODO enviar el email al correo de la tienda
            ->addBcc($multipleBcc);
            $transportBuilder->getTransport()->sendMessage();
        } catch (\Exception $exception) {
        }
    }

    /**
     * @return string
     */
    protected function getFrom()
    {
        return 'general';
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    protected function getAddTo(\Magento\Sales\Model\Order $order)
    {
        $shippingMethod = $order->getShippingMethod();
        $storeId = 0;
        if(strpos($shippingMethod, ReserveAndCollect::SHIPPING_CODE) !== false) {
            $storeId = str_replace(ReserveAndCollect::SHIPPING_METHOD, '', $shippingMethod);
        } else if(strpos($shippingMethod, StorePickup::SHIPPING_CODE) !== false){
            $storeId = str_replace(StorePickup::SHIPPING_METHOD, '', $shippingMethod);
        }
        $storeId = (integer)$storeId;
        $emailOrder = '';
        $emailName = '';
        if($storeId > 0) {
            /** @var  \Alfa9\StoreInfo\Model\ResourceModel\Stores\Collection $storeCollection */
            $storeCollection = $this->collectionStoreFactory->create();
            $storeCollection = $storeCollection->addFieldToFilter(StockistInterface::SR_ID, ['eq' =>  $storeId])->load();
            /** @var \Alfa9\StoreInfo\Model\Stores $store */
            $store = $storeCollection->getFirstItem();
            if($store) {
                $emailOrder = $store->getEmailOrder();
                $emailName = $store->getName();
            }
        }
        return [
            'address' => $emailOrder,
            'name' => $emailName
        ];
    }

    /**
     * Prepare email template with variables
     *
     * @param \Magento\Sales\Model\Order $order
     * @return \Magento\Framework\DataObject
     */
    protected function prepareTemplate(\Magento\Sales\Model\Order $order)
    {
        $url = $this->getUrls($order);

        $shippingMethod = $order->getShippingMethod();
        $subject = '';

        if(strpos($shippingMethod, StorePickup::SHIPPING_CODE) !== false){
            $subject = "Confirmación de Click & Collect #{$order->getIncrementId()}";
        }elseif(strpos($shippingMethod, ReserveAndCollect::SHIPPING_CODE) !== false){
            $subject = "Confirmación de reserva #{$order->getIncrementId()}";
        }

        $transport = [
            'order' => $order,
            'billing' => $order->getBillingAddress(),
            'payment_html' => $this->getPaymentHtml($order),
            'store' => $order->getStore(),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
            'confirmAction' => $url['confirm'],
            'cancelAction' => $url['cancel'],
            'subject' => $subject,
        ];
        return new \Magento\Framework\DataObject($transport);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    private function getUrls(\Magento\Sales\Model\Order $order)
    {
        $incrementId = $order->getIncrementId();
        $date = new \DateTime();
        $timeStamp = $date->getTimestamp();

        $code = "{$timeStamp}:{$incrementId}";
        $hash = base64_encode($code);
        $url = [];
        $url['cancel'] = $this->url->getUrl('pickup/cancel/index', ['k' => $hash]);
        $url['confirm'] = $this->url->getUrl('pickup/confirm/index', ['k' => $hash]);
        return $url;
    }

    /**
     * Get payment info block as html
     *
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    protected function getPaymentHtml(\Magento\Sales\Model\Order $order)
    {
        return $this->paymentHelper->getInfoBlockHtml(
            $order->getPayment(),
            $order->getStoreId()
        );
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return string|null
     */
    protected function getFormattedShippingAddress(\Magento\Sales\Model\Order $order)
    {
        return $order->getIsVirtual()
            ? null
            : $this->addressRenderer->format($order->getShippingAddress(), 'html');
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return string|null
     */
    protected function getFormattedBillingAddress(\Magento\Sales\Model\Order $order)
    {
        /** @var \Magento\Sales\Model\Order\Address $billingAddress */
        $billingAddress = $order->getBillingAddress();
        return $this->addressRenderer->format($billingAddress, 'html');
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    protected function getEmailTemplate(\Magento\Sales\Model\Order $order) {
        $newOrderEmail = $this->scopeConfig->getValue(self::PATH_XML_EMAIL_NEW_ORDER_PICK_UP, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $newOrderEmailGuest = $this->scopeConfig->getValue(self::PATH_XML_EMAIL_NEW_ORDER_GUEST_PICK_UP, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $order->getCustomerIsGuest() == 1 ? $newOrderEmailGuest : $newOrderEmail;
    }
}
