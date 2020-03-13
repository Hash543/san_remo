<?php

namespace Alfa9\Sales\Observer\Order;

class AfterPlacerOrderLegacy implements \Magento\Framework\Event\ObserverInterface
{
    const FILE_CSV_ORDER = 'psr_web_carga_pedven';
    const FILE_CSV_CUSTOMER = 'psr_web_carga_clien';
    const FOLDER_ORDER_NAME = 'order';
    const FOLDER_CUSTOMER_NAME = 'customer';
    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;
    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;
    /**
     * @var \Magento\Framework\File\Csv
     */
    private $csvProcessor;
    /**
     * @var \Alfa9\Base\Logger\Logger
     */
    private $logger;
    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    private $countryFactory;
    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    private $subscriber;

    private $customerDob;
    /**
     * @var \Alfa9\StockExpress\Model\StockRepository
     */
    private $stockService;
    /**
     * @var \Alfa9\StorePickup\Helper\Data
     */
    private $storePickupHelper;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * AfterPlacerOrderLegacy constructor.
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Alfa9\Base\Logger\Logger $logger
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriber
     * @param \Alfa9\StockExpress\Model\StockRepository $stockService
     * @param \Alfa9\StorePickup\Helper\Data $storePickupHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Alfa9\Base\Logger\Logger $logger,
        \Magento\Newsletter\Model\SubscriberFactory $subscriber,
        \Alfa9\StockExpress\Model\StockRepository $stockService,
        \Alfa9\StorePickup\Helper\Data $storePickupHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerRepository = $customerRepository;
        $this->countryFactory = $countryFactory;
        $this->filesystem = $filesystem;
        $this->directoryList = $directoryList;
        $this->csvProcessor = $csvProcessor;
        $this->logger = $logger;
        $this->subscriber = $subscriber;
        $this->customerDob;
        $this->stockService = $stockService;
        $this->storePickupHelper = $storePickupHelper;
        $this->dateTime = $dateTime;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getData('order');
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $observer->getEvent()->getData('invoice');

        if($invoice) {
            $orderInvoice = $invoice->getOrder();
            $paymentMethod = $orderInvoice->getPayment()->getMethod();
            /** The CSV is created by the event of invoice but only in the cases of Bank transfer and reserve & collect */
            if($paymentMethod == \Magento\OfflinePayments\Model\Banktransfer::PAYMENT_METHOD_BANKTRANSFER_CODE
                || $paymentMethod ==  \Alfa9\ReserveAndCollect\Model\Payment\Reserveandcollect::PAYMENT_METHOD_RESERVE_AND_COLLECT) {
                $order = $orderInvoice;
                $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
            } else {
                $order = null;
            }
        }
        if(!$order) {
            return $this;
        }
        $status = $order->getStatus();
        if (!in_array($status, ['processing', 'complete'])) {
            return $this;
        }
        $debug = $observer->getEvent()->getData('debug');

        $filePath = $this->createCsvFile($order->getIncrementId(), self::FILE_CSV_ORDER, self::FOLDER_ORDER_NAME);
        $this->fillOrderData($order, $filePath, $debug);

        // add customer csv
        $filePath = $this->createCsvFile($order->getIncrementId(), self::FILE_CSV_CUSTOMER, self::FOLDER_CUSTOMER_NAME);
        $this->fillCustomerData($order, $filePath, $debug);

        /*
        $data[] = $this->getDataOrder($order);
        $this->csvProcessor
            ->setEnclosure('"')
            ->setDelimiter(',')
            ->saveData($filePath, $data);*/
        return $this;
    }

    private function fillCustomerData(\Magento\Sales\Model\Order $order, $path, $debug = false)
    {
        // CLIENTES
        $customerId = $order->getCustomerId();
        if (empty($customerId)) {
            $customerId = $order->getId();
        }

        /** @var \Magento\Sales\Api\Data\OrderAddressInterface $billingAddress */
        $billingAddress = $order->getBillingAddress();
        /** @var \Magento\Sales\Model\Order\Address $shippingAddress */
        $shippingAddress = $order->getShippingAddress();

        $customerGender = 'M';
        if ($billingAddress->getPrefix() === 'Sra.') {
            $customerGender = 'F';
        }

        $lastName2 = $billingAddress->getSuffix();
        if (empty($lastName2)) {
            $lastName2 = '.';
        }

        $checkStreet = $billingAddress->getStreet();
        if (empty($checkStreet)) {
            $billingStreet = $billingAddress->getStreetLine(1);
        } else {
            $billingStreet = $billingAddress->getStreetLine(2);
        }

        $checkStreet = $shippingAddress->getStreetLine(2);
        if (empty($checkStreet)) {
            $shippingStreet = $shippingAddress->getStreetLine(1);
        } else {
            $shippingStreet = $shippingAddress->getStreetLine(2);
        }

        $customerLines['cliente'][] = 'A|CLI';
        $customerLines['cliente'][] = strtoupper($customerId);
        $customerLines['cliente'][] = '||'; // seran 4 pipes, pero al hacer implode para el fichero de texto ya se a�adir�n por delante y por detr�s
        $customerLines['cliente'][] = strtoupper($billingAddress->getFirstname() . ' ' . $billingAddress->getLastname() . ' ' . $billingAddress->getSuffix());
        $customerLines['cliente'][] = strtoupper($billingAddress->getFirstname());
        $customerLines['cliente'][] = strtoupper($billingAddress->getLastname());
        $customerLines['cliente'][] = strtoupper($lastName2);
        $customerLines['cliente'][] = strtoupper($customerGender);
        $this->customerDob = $order->getCustomerDob();
        if (!empty($this->customerDob)) {
            $this->customerDob = explode(' ', $this->customerDob);
            array_pop($this->customerDob);
            $this->customerDob = array_pop($this->customerDob);
        }
        $customerLines['cliente'][] = strtoupper($this->customerDob);

        // TODO: Make sure with client
        $taxVat = $order->getCustomerTaxvat() != null ? $order->getCustomerTaxvat() : $billingAddress->getVatId();

        $customerLines['cliente'][] = strtoupper($taxVat);
        $customerLines['cliente'][] = strtoupper($billingAddress->getEmail());
        // direccion facturacion
        $customerLines['cliente'][] = strtoupper($billingAddress->getStreetLine(1));//strtoupper($billingStreet); // Calle
        $customerLines['cliente'][] = strtoupper($billingAddress->getStreetLine(2)); // Tipo de Calle
        $customerLines['cliente'][] = '';//strtoupper($billingAddress->getStreet3()); // ESCALERA
        $customerLines['cliente'][] = '';//strtoupper($billingAddress->getStreet4()); // PLANTA
        $customerLines['cliente'][] = '';//strtoupper($billingAddress->getStreet5()); // PUERTA
        $customerLines['cliente'][] = strtoupper($billingAddress->getCity());
        $customerLines['cliente'][] = strtoupper($billingAddress->getPostcode());
        $customerLines['cliente'][] = strtoupper(substr($billingAddress->getPostcode(), 0, 2));
        $customerLines['cliente'][] = strtoupper($billingAddress->getRegion());
        $customerLines['cliente'][] = 'ESP';
        $customerLines['cliente'][] = strtoupper($billingAddress->getTelephone());
        $customerLines['cliente'][] = strtoupper($billingAddress->getFax());
        $customerLines['cliente'][] = 'ES';


        // direccion envio
        $customerLines['cliente'][] = strtoupper($order->getShippingAddress()->getFirstname() . ' ' . $order->getShippingAddress()->getLastname() . ' ' . $order->getShippingAddress()->getSuffix());
        $customerLines['cliente'][] = strtoupper($shippingAddress->getStreetLine(1));//strtoupper($shippingStreet); // Calle
        $customerLines['cliente'][] = strtoupper($shippingAddress->getStreetLine(2)); // Tipo de Calle
        $customerLines['cliente'][] = strtoupper($shippingAddress->getStreetLine(3)); // ESCALERA
        $customerLines['cliente'][] = strtoupper($shippingAddress->getStreetLine(4)); // PLANTA
        $customerLines['cliente'][] = strtoupper($shippingAddress->getStreetLine(5)); // PUERTA
        $customerLines['cliente'][] = strtoupper($shippingAddress->getCity());
        $customerLines['cliente'][] = strtoupper($shippingAddress->getPostcode());
        $customerLines['cliente'][] = strtoupper(substr($shippingAddress->getPostcode(), 0, 2));
        $customerLines['cliente'][] = strtoupper($shippingAddress->getRegion());
        $customerLines['cliente'][] = 'ESP';
        $newsletter = 'N';
        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $this->subscriber->create()->loadByEmail($order->getCustomerEmail());
        if ($subscriber->getId()) {
            $newsletter = 'S';
        }
        $customerLines['cliente'][] = $newsletter;
        $crmId = '';
        if($order->getCustomerId()) {
            try {
               $customer = $this->customerRepository->getById($order->getCustomerId());
               if($customer->getCustomAttribute('id_crm')) {
                   $crmId = $customer->getCustomAttribute('id_crm')->getValue();
               }
            }catch (\Magento\Framework\Exception\LocalizedException $exception) {
                $crmId = '';
            }
        }
        $customerLines['cliente'][] = $crmId;
        //$customerLines['cliente'][] = $cust
        /* Mask Customer lines to data */
        $data = $customerLines;
        $customerFileLines[] = implode('|', $data['cliente']) . '|';
        /*if ($debug) {
            echo "<pre>";
            var_export($customerFileLines);
            echo "</pre>";
        }*/
        $this->saveCustomerFile($path, $customerFileLines, TRUE);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param string $path
     */
    public function fillOrderData(\Magento\Sales\Model\Order $order, $path, $debug = false)
    {
        $createdAt = $order->getCreatedAt();
        if($createdAt == null) {
            $createdAt = $this->dateTime->gmtDate('d-m-Y'); //Fix issue with Paypal
        } else {
            $createdAt = date('d-m-Y', strtotime($createdAt));
        }
        $orderID = $order->getIncrementId();

        /** @var \Magento\Sales\Model\Order\Item $item */
        $orderLines = [];
        $i = 1;

        //  NEW Fields
        // Payment code
        $paymentMethod = $order->getPayment()->getMethod();

        $formaDePago = '';

        switch ($paymentMethod) {
            case 'redsys':
                $formaDePago = 'VISA';
                break;
            case 'paypal_express':
                $formaDePago = 'PAYPAL';
                break;
            case 'reserveandcollect':
                $formaDePago = 'RESERVEANDCOLLECT';
                break;
            case 'banktransfer':
                $formaDePago = 'TRANS';
                break;
        }
        $operadorLogistico = '';
        $tipoDeEnvio = '';
        $isClickAndCollect = '';
        $stockExpress = '';
        $tiendaDeRecogida = '';

        $shippingMethod = $order->getShippingMethod();
        switch ($shippingMethod) {
            case 'amstrates_amstrates1':
                $operadorLogistico = 'MEB';
                $tipoDeEnvio = '24H';
                break;
            case 'amstrates_amstrates2':
                $operadorLogistico = 'TOUR';
                $tipoDeEnvio = '48C';
                break;
            case (strpos($shippingMethod,'storepickup_store') !== false):
                $isClickAndCollect = 'CC';
                $tiendaDeRecogida = $this->getStoreName($shippingMethod); //'PSR01';
                $shipDesc = strtolower($order->getShippingDescription());
                $stockExpress = strpos($shipDesc, ' 2h') !== false ? 'D': 'ND'; // 2h = D; Resto ND
                break;
        }
        foreach ($order->getAllItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            $itemSku = strtoupper($item->getSku());
            $qtyOrdered = round($item->getQtyOrdered(), 0);
            // TODO: MAYBE ['item' . $i] CAN BE DELETED
            // ARTICULOS VENTA
            $orderLines['items']['item' . $i][] = 'PVTV|PSR|TV|0';
            $orderLines['items']['item' . $i][] = $createdAt . '|' . $createdAt; // Fecha de Creacion | Fecha de Creacion
            $orderLines['items']['item' . $i][] = $orderID; // Codigo Pedido
            $orderLines['items']['item' . $i][] = 0;
            $orderLines['items']['item' . $i][] = strtoupper($order->getCustomerTaxvat()); // DNI/NIE/CIF
            $orderLines['items']['item' . $i][] = 0;
            $orderLines['items']['item' . $i][] = '111111';    // Estatico
            $orderLines['items']['item' . $i][] = number_format($order->getGrandTotal(), 2, ',', '.'); // Total pedido sin IVA

            $orderLines['items']['item' . $i][] = $i; // Numero de linea
            $orderLines['items']['item' . $i][] = $itemSku; // Codigo Articulo
            $orderLines['items']['item' . $i][] = $qtyOrdered; // Unidades
            $orderLines['items']['item' . $i][] = number_format($item->getPrice(), 2, ',', '.'); // Precio Unitario sin iva
            $orderLines['items']['item' . $i][] = '0,00'; // Estatico
            $orderLines['items']['item' . $i][] = number_format(($item->getQtyOrdered() * $item->getPrice()), 2, ',', '.'); // Precio Unitario * Unidades
            $orderLines['items']['item' . $i][] = number_format($item->getTaxAmount(), 2, ',', '.'); // Iva

            /*
             amstrates_amstrates1 = Envio domicilio: Entrega Express 24H MEB
             amstrates_amstrates2 = Envio domicilio: Envio Standard = 48/72H TOUR
             storepickup_store_1 = click&collect 2h / click&collect 48-72h
             */

            /*
            $shipData = $this->isStorePickup($order, $itemSku, $qtyOrdered);
            $isClickAndCollect = $shipData['click_and_collect'];
            $isInStock = $shipData['is_in_stock'];
            $shippingMethod = $shipData['shipping_method'];
            */

            $orderLines['items']['item' . $i][] = $formaDePago; // Forma de pago
            $orderLines['items']['item' . $i][] = $operadorLogistico; // Operador Logistico
            $orderLines['items']['item' . $i][] = $tipoDeEnvio; // Tipo de envio
            $orderLines['items']['item' . $i][] = ''; // Vacio
            $orderLines['items']['item' . $i][] = ''; // Vacio
            $orderLines['items']['item' . $i][] = ''; // Vacio
            $orderLines['items']['item' . $i][] = $isClickAndCollect; // Click&Collect
            $orderLines['items']['item' . $i][] = $stockExpress; // Stock Express D = Disponible
            $orderLines['items']['item' . $i][] = $tiendaDeRecogida; // Tienda de Recogida
            $orderLines['items']['item' . $i][] = ''; // Vacio
            $orderLines['items']['item' . $i][] = ''; // Vacio
            $orderLines['items']['item' . $i][] = ''; // Vacio
            $orderLines['items']['item' . $i][] = ''; // Vacio
            $orderLines['items']['item' . $i][] = ''; // Vacio
            $orderLines['items']['item' . $i][] = ''; // Vacio
            $orderLines['items']['item' . $i][] = ''; // Vacio
            $orderLines['items']['item' . $i][] = ''; // Vacio
            $orderLines['items']['item' . $i][] = ''; // Vacio
            $orderLines['items']['item' . $i][] = ''; // Vacio
            $orderLines['items']['item' . $i][] = ''; // Vacio
            $i++;
        }

        // PAYPAL FEE
        $fee = $order->getData('paypal_fee_amount');
        if($fee > 0) {
            $feeSku = 'COMISION';
            $fee_tax = $fee / 1.21; // calculated manually cause is not informed on sales_order table
            $fee_iva = $fee - $fee_tax;
            $orderLines['paypal_fee'][] = 'PVTV|PSR|TV|0';
            $orderLines['paypal_fee'][] = $createdAt . '|' . $createdAt;
            $orderLines['paypal_fee'][] = $orderID;
            $orderLines['paypal_fee'][] = 0;
            $orderLines['paypal_fee'][] = strtoupper($order->getCustomerTaxvat());
            $orderLines['paypal_fee'][] = 0;
            $orderLines['paypal_fee'][] = '111111'; // @ respuesta TPV
            $orderLines['paypal_fee'][] = number_format($order->getGrandTotal(), 2, ',', '.');
            $orderLines['paypal_fee'][] = $i; // Incremental de linea
            $orderLines['paypal_fee'][] = $feeSku;
            $orderLines['paypal_fee'][] = 1; // cantidad en paypal_fee? 1 por defecto? entiendo que son los gastos de env�o: a nivel de pedido, no de item
            $orderLines['paypal_fee'][] = number_format($fee_tax, 2, ',', '.'); // precio en paypal_fee? gastos de env�o a nivel de pedido, no de item
            $orderLines['paypal_fee'][] = '0,00';
            $orderLines['paypal_fee'][] = number_format($fee_tax, 2, ',', '.');
            $orderLines['paypal_fee'][] = number_format($fee_iva, 2, ',', '.');
            $orderLines['paypal_fee'][] = $formaDePago;
            $orderLines['paypal_fee'][] = $operadorLogistico;
            $orderLines['paypal_fee'][] = $tipoDeEnvio;
            $orderLines['paypal_fee'][] = '';
            $orderLines['paypal_fee'][] = '';
            $orderLines['paypal_fee'][] = '';
            $orderLines['paypal_fee'][] = $isClickAndCollect;
            $orderLines['paypal_fee'][] = $stockExpress;
            $orderLines['paypal_fee'][] = $tiendaDeRecogida;
            $orderLines['paypal_fee'][] = '';
            $orderLines['paypal_fee'][] = '';
            $orderLines['paypal_fee'][] = '';
            $orderLines['paypal_fee'][] = '';
            $orderLines['paypal_fee'][] = '';
            $orderLines['paypal_fee'][] = '';
            $orderLines['paypal_fee'][] = '';
            $orderLines['paypal_fee'][] = '';
            $orderLines['paypal_fee'][] = '';
            $orderLines['paypal_fee'][] = '';
            $orderLines['paypal_fee'][] = '';
        }

        // TRANSPORTE

        $transSKU = 'TRANS001';
        $transQtyOrdered = 1;

        /*
        $shipData = $this->isStorePickup($order, $transSKU, $transQtyOrdered);
        $isClickAndCollect = $shipData['click_and_collect'];
        $isInStock = 'D';
        $shippingMethod = $shipData['shipping_method'];
        */

        $orderLines['transporte'][] = 'PVTV|PSR|TV|0';
        $orderLines['transporte'][] = $createdAt . '|' . $createdAt;
        $orderLines['transporte'][] = $orderID;
        $orderLines['transporte'][] = 0;
        $orderLines['transporte'][] = strtoupper($order->getCustomerTaxvat());
        $orderLines['transporte'][] = 0;
        $orderLines['transporte'][] = '111111'; // @ respuesta TPV
        $orderLines['transporte'][] = number_format($order->getGrandTotal(), 2, ',', '.');
        $orderLines['transporte'][] = '1'; // constante
        $orderLines['transporte'][] = $transSKU;
        $orderLines['transporte'][] = $transQtyOrdered; // cantidad en transporte? 1 por defecto? entiendo que son los gastos de env�o: a nivel de pedido, no de item
        $orderLines['transporte'][] = number_format($order->getShippingAmount(), 2, ',', '.'); // precio en transporte? gastos de env�o a nivel de pedido, no de item
        $orderLines['transporte'][] = '0,00';
        $orderLines['transporte'][] = number_format($order->getShippingAmount(), 2, ',', '.');
        $orderLines['transporte'][] = number_format($order->getShippingTaxAmount(), 2, ',', '.');

        $orderLines['transporte'][] = $formaDePago;
        $orderLines['transporte'][] = $operadorLogistico;
        $orderLines['transporte'][] = $tipoDeEnvio;
        $orderLines['transporte'][] = '';
        $orderLines['transporte'][] = '';
        $orderLines['transporte'][] = '';
        $orderLines['transporte'][] = $isClickAndCollect;
        $orderLines['transporte'][] = $stockExpress;
        $orderLines['transporte'][] = $tiendaDeRecogida;
        $orderLines['transporte'][] = '';
        $orderLines['transporte'][] = '';
        $orderLines['transporte'][] = '';
        $orderLines['transporte'][] = '';
        $orderLines['transporte'][] = '';
        $orderLines['transporte'][] = '';
        $orderLines['transporte'][] = '';
        $orderLines['transporte'][] = '';
        $orderLines['transporte'][] = '';
        $orderLines['transporte'][] = '';
        $orderLines['transporte'][] = '';

        $discount = abs($order->getDiscountAmount());
        if ($discount > 0) {
            // DESCUENTOS
            $dctoIVA = $order->getDiscountAmount() / 1.21;//($order->getDiscountAmount() - $order->getBaseDiscountAmount())
            $orderLines['descuento'][] = 'PVTV|PSR|TV|0';
            $orderLines['descuento'][] = $createdAt . '|' . $createdAt;
            $orderLines['descuento'][] = $orderID;
            $orderLines['descuento'][] = 0;
            $orderLines['descuento'][] = strtoupper($order->getCustomerTaxvat());
            $orderLines['descuento'][] = 0;
            $orderLines['descuento'][] = '111111'; // @ respuesta TPV
            $orderLines['descuento'][] = number_format($order->getGrandTotal(), 2, ',', '.');
            $orderLines['descuento'][] = '1'; // constante
            $orderLines['descuento'][] = 'DTETV001';
            $orderLines['descuento'][] = 1; // cantidad de descuento? 1 por defecto?
            $orderLines['descuento'][] = number_format($dctoIVA, 2, ',', '.');
            $orderLines['descuento'][] = '0,00';
            $orderLines['descuento'][] = number_format($dctoIVA, 2, ',', '.');
            $discountIva = 0;//($order->getDiscountAmount() - $order->getBaseDiscountAmount())
            $orderLines['descuento'][] = number_format($discountIva, 2, ',', '.'); // Precio Unitario * Unidades
            $orderLines['descuento'][] = $formaDePago;
            $orderLines['descuento'][] = '';
            $orderLines['descuento'][] = '';
            $orderLines['descuento'][] = '';
            $orderLines['descuento'][] = '';
            $orderLines['descuento'][] = '';
            $orderLines['descuento'][] = '';
            $orderLines['descuento'][] = '';
            $orderLines['descuento'][] = '';
            $orderLines['descuento'][] = '';
            $orderLines['descuento'][] = '';
            $orderLines['descuento'][] = '';
            $orderLines['descuento'][] = '';
            $orderLines['descuento'][] = '';
            $orderLines['descuento'][] = '';
            $orderLines['descuento'][] = '';
        }

        if ($order->getGiftMessageId()) {
            // ENVOLTORIO REGALO
            $orderLines['regalo'][] = 'PVTV|PSR|TV|0';
            $orderLines['regalo'][] = $createdAt . '|' . $createdAt;
            $orderLines['regalo'][] = $orderID;
            $orderLines['regalo'][] = 0;
            $orderLines['regalo'][] = strtoupper($order->getCustomerTaxvat());
            $orderLines['regalo'][] = 0;
            $orderLines['regalo'][] = '111111'; // @ respuesta TPV
            $orderLines['regalo'][] = number_format($order->getGrandTotal(), 2, ',', '.');
            $orderLines['regalo'][] = 1;     //TODO $i
            $orderLines['regalo'][] = 'ENVTV001';
            $orderLines['regalo'][] = 1;
            $orderLines['regalo'][] = '0,00';
            $orderLines['regalo'][] = '0,00';
            $orderLines['regalo'][] = '0,00';
            $orderLines['regalo'][] = '0,00';
        }
        // mask variable $orderLines

        $data = $orderLines;
        $orderFileLines = [];
        $customerFileLines = [];
        foreach ($data['items'] as $item) {
            $orderFileLines[] = implode('|', $item) . '|';
        }

        if (array_key_exists('paypal_fee', $data)) {
            $orderFileLines[] = implode('|', $data['paypal_fee']) . '|';
        }

        $orderFileLines[] = implode('|', $data['transporte']) . '|';
        if (array_key_exists('descuento', $data)) {
            $orderFileLines[] = implode('|', $data['descuento']) . '|';
        }
        if (array_key_exists('regalo', $data)) {
            $orderFileLines[] = implode('|', $data['regalo']) . '|';
        }
        /*if ($debug) {
            echo "<pre>";
            var_export($orderFileLines);
            echo "</pre>";
        }*/
        try {
            $this->saveFile($path, $orderFileLines, true);
        } catch (\Exception $exception) {
        }
    }

    /**
     * @return string
     * @var string $orderIncrement
     */
    private function createCsvFile($orderIncrement, $csv, $folder)
    {
        $fileDirectoryPath = null;
        try {
            $mediaPath = \Magento\Framework\App\Filesystem\DirectoryList::ROOT;
            $fileDirectoryPath = $this->directoryList->getPath($mediaPath);
            $fileDirectoryPath = $fileDirectoryPath . '/' . $folder;
        } catch (\Exception $exception) {
            $this->logger->addCritical(__('Error accessing to the folder in order to save the order csv.'));
        }
        if (!is_dir($fileDirectoryPath)) {
            mkdir($fileDirectoryPath, 0777, true);
        }

        //CHANGE 2019-06-14 - Changed Date() to Time()
        //$date = \Date('dmYHi');
        $date = \Time();
        return $fileDirectoryPath . '/' . $csv . $date . '.txt';
    }

    /**
     * @param $countryCode
     * @return string
     */
    private function getCountryNameById($countryCode)
    {
        $country = $this->countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
    }

    // Generic function to save files to filesystem
    private function saveFile($filename, $filedata, $isLast)
    {
        ob_start();
        $last = count($filedata);
        $i = 1;
        foreach ($filedata as $fileline) {
            echo $fileline;
            if ($i < $last) {
                echo "\n";
            } elseif ($i == $last && !$isLast) {
                echo "\n";
            }
            $i++;
        }
        $output = ob_get_contents();
        // write output to log file
        ob_end_clean();
        try {
            $fh = @fopen($filename, 'w');
            fwrite($fh, $output);
            fclose($fh);
        } catch (\Exception $e) {
        }
    }

    // Generic function to save files to filesystem
    private function saveCustomerFile($filename, $filedata, $last)
    {
        ob_start();

        foreach ($filedata as $fileline) {
            echo $fileline;
            if (!$last) {
                echo "\n";
            }
        }
        $output = ob_get_contents();
        // write output to log file
        ob_end_clean();
        try {
            $fh = @fopen($filename, 'w');
            fwrite($fh, $output);
            fclose($fh);
        } catch (\Exception $e) {
        }
    }

    public function getStoreName($shippingMethod)
    {
        $prefix = 'storepickup_store_';
        if (substr($shippingMethod, 0, strlen($prefix)) == $prefix) {
            $shippingCode = substr($shippingMethod, strlen($prefix));

            $shippingList = [
                '1' => 'PSR01',
                '2' => 'PSR02',
                '3' => 'PSR03',
                '4' => 'BSA04',
                '5' => 'BSA05',
                '6' => 'PSR06',
                '7' => 'PSR07',
                '8' => 'PSR08',
                '9' => 'PSR09',
                '10' => 'PSR10',
                '11' => 'PSR11',
                '12' => 'PSR12',
                '13' => 'PSR13',
                '16' => 'PSR16',
                '17' => 'CAT17',
                '18' => 'CAT18',
                '19' => 'PSR19',
                '20' => 'PSR20',
                '21' => 'CAT21',
                '22' => 'CAT22',
                '25' => 'PSR25',
                '26' => 'PSR26',
                '27' => 'PSR27',
                '28' => 'PSR28',
                '30' => 'PSR30',
                '31' => 'PSR31',
                '32' => 'PSR32',
                '33' => 'PSR33',
                '35' => 'PSR35',
                '36' => 'PSR36',
                '37' => 'PSR37',
                '38' => 'PSR38',
                '39' => 'CAT39',
            ];

            return isset($shippingList[$shippingCode]) ? $shippingList[$shippingCode] : 'NONE';
        }
    }

    private function isStorePickup(\Magento\Sales\Model\Order $order, $itemSku, $qtyOrdered)
    {
        $shippingMethod = $order->getShippingMethod();
        $prefix = 'storepickup_store_';

        // Is store pickup
        if (substr($shippingMethod, 0, strlen($prefix)) == $prefix) {
            $shippingCode = substr($shippingMethod, strlen($prefix));

            $shippingList = [
                '1' => 'PSR01',
                '2' => 'PSR02',
                '3' => 'PSR03',
                '4' => 'BSA04',
                '5' => 'BSA05',
                '6' => 'PSR06',
                '7' => 'PSR07',
                '8' => 'PSR08',
                '9' => 'PSR09',
                '10' => 'PSR10',
                '11' => 'PSR11',
                '12' => 'PSR12',
                '13' => 'PSR13',
                '16' => 'PSR16',
                '17' => 'CAT17',
                '18' => 'CAT18',
                '19' => 'PSR19',
                '20' => 'PSR20',
                '21' => 'CAT21',
                '22' => 'CAT22',
                '25' => 'PSR25',
                '26' => 'PSR26',
                '27' => 'PSR27',
                '28' => 'PSR28',
                '30' => 'PSR30',
                '31' => 'PSR31',
                '32' => 'PSR32',
                '33' => 'PSR33',
                '35' => 'PSR35',
                '36' => 'PSR36',
                '37' => 'PSR37',
                '38' => 'PSR38',
                '39' => 'CAT39',
            ];

            //$res = $this->stockService->getList($itemSku, $qtyOrdered);
            //$ids = \Alfa9\StorePickup\Helper\Data::getSrIdFromResponse($res);
            $ids =[];
            $isInStock = isset($ids[\Alfa9\StorePickup\Helper\Data::PREFIX_STOCK_EXPRESS]) &&
            is_array($ids[\Alfa9\StorePickup\Helper\Data::PREFIX_STOCK_EXPRESS]) &&
            in_array($shippingCode, $ids[\Alfa9\StorePickup\Helper\Data::PREFIX_STOCK_EXPRESS]) ? 'D' : 'ND';

            $shippingMethod = isset($shippingList[$shippingCode]) ? $shippingList[$shippingCode] : $shippingCode;

            return [
                'is_in_stock' => $isInStock,
                'shipping_method' => $shippingMethod,
                'click_and_collect' => 'CC'
            ];
        }
        return [
            'is_in_stock' => '',
            'shipping_method' => $shippingMethod,
            'click_and_collect' => ''
        ];
    }
}
