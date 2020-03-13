<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\Sales\Observer\Order;

use Magento\Customer\Model\CustomerFactory;

class AfterPlacerOrder implements \Magento\Framework\Event\ObserverInterface {

    const FILE_CSV_ORDER = '-order-export.csv';
    const FILE_CSV_CUSTOMER = '-customer-export.csv';
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
     * AfterPlacerOrder constructor.
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Alfa9\Base\Logger\Logger $logger
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriber
     */
    public function __construct(
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Alfa9\Base\Logger\Logger $logger,
        \Magento\Newsletter\Model\SubscriberFactory $subscriber
    ) {
        $this->countryFactory = $countryFactory;
        $this->filesystem = $filesystem;
        $this->directoryList = $directoryList;
        $this->csvProcessor = $csvProcessor;
        $this->logger = $logger;
        $this->subscriber = $subscriber;
        $this->customerDob;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {

        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getData('order');

        if($order && $order->getId() && false) {
            return;
        }

        $filePath = $this->createCsvFile($order->getIncrementId(), self::FILE_CSV_ORDER, self::FOLDER_ORDER_NAME);
        $this->fillOrderData($order, $filePath);

        // add customer csv
        $filePath = $this->createCsvFile($order->getIncrementId(),self::FILE_CSV_CUSTOMER, self::FOLDER_CUSTOMER_NAME);
        $this->fillCustomerData($order, $filePath);

        /*
        $data[] = $this->getDataOrder($order);
        $this->csvProcessor
            ->setEnclosure('"')
            ->setDelimiter(',')
            ->saveData($filePath, $data);*/
    }

    private function fillCustomerData(\Magento\Sales\Model\Order $order, $path){
        $customerId = $order->getCustomerId();
        if(empty($customerId)){
            $customerId = $order->getId();
        }

        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();

        $customerGender = 'M';
        if($billingAddress->getPrefix() === 'Sra.'){
            $customerGender = 'F';
        }
        $this->customerDob = $order->getCustomerDob();
        if (!empty($this->customerDob)) {
            $this->customerDob = explode(' ', $this->customerDob);
            array_pop($this->customerDob);
            $this->customerDob = array_pop($this->customerDob);
        }

        $lastname2 = $order->getBillinggetCustomerSuffix();
        if(empty($lastname2)){
            $lastname2 = '.';
        }

        $checkStreet = $billingAddress->getStreet2();
        if(empty($checkStreet)){
            $billingStreet = $billingAddress->getStreet1();
        }else{
            $billingStreet = $billingAddress->getStreet2();
        }

        $checkStreet = $shippingAddress->getStreet2();
        if(empty($checkStreet)){
            $shippingStreet = $shippingAddress->getStreet1();
        }else{
            $shippingStreet = $shippingAddress->getStreet2();
        }

        $heading = [
            __('Cliente'),
            __('Id Cliente'),
            __('Doble Pipe'),
            __('Nombre Completo Factura'),
            __('Nombre Factura'),
            __('Apellido Factura'),
            __('Segundo Apellido'),
            __('Sexo'),
            __('Dob'),
            __('Tax/Vat Factura'),
            __('Correo Factura'),
            //            __('Dirección Factura'),
            __('Dirección2 Factura'),
            __('Dirección1 Factura'),
            __('Dirección3 Factura'),
            __('Dirección4 Factura'),
            __('Dirección5 Factura'),
            __('Cuidad Factura'),
            __('Código Postal Factura'),
            __('Código Postal Inicial Factura'),
            __('Provincia Factura'),
            __('Estado Factura'),
            __('Teléfono Factura'),
            __('Fax Factura'),
            __('Idioma Factura'),
            __('Nombre Completo Envío'),
            //            __('Dirección Envío'),
            __('Dirección2 Envío'),
            __('Dirección1 Envío'),
            __('Dirección3 Envío'),
            __('Dirección4 Envío'),
            __('Dirección5 Envío'),
            __('Cuidad Envío'),
            __('Código Postal Envío'),
            __('Código Postal Inicial Envío'),
            __('Provincia Envío'),
            __('Estado Envío'),
            __('Newsletter'),
        ];
        $handle = fopen($path, 'w');
        fputcsv($handle, $heading);

        $customerStart = 'A|CLI';
        $customerIde = $customerId;
        $customerPipe = '||';
        $customerFullName= strtoupper($billingAddress->getFirstname(). ' ' .$billingAddress->getLastname() . ' ' . $billingAddress->getSuffix());
        $firstname = strtoupper($billingAddress->getFirstname());
        $lastname = strtoupper($billingAddress->getLastname());
        $lastnamesecond = strtoupper($lastname2);
        $customerGen = strtoupper($customerGender);
        $customerDob = strtoupper($this->customerDob);
        $taxvat = strtoupper($order->getCustomerTaxvat());
        $email = strtoupper($billingAddress->getEmail());
        // Dirección de facturación
        //        $street = strtoupper($billingAddress->getStreet()[0]);
        $street2 = strtoupper($billingAddress->getStreet2());
        $street1 = strtoupper($billingAddress->getStreet1());
        $street3 = '';
        $street4 = '';
        $street5 = '';
        $city = strtoupper($billingAddress->getCity());
        $postCode = strtoupper($billingAddress->getPostcode());
        $postCodeShort = strtoupper(substr($billingAddress->getPostcode(), 0, 2));
        $region = strtoupper($billingAddress->getRegion());
        $state = 'ESP';
        $telephone = strtoupper($billingAddress->getTelephone());
        $fax = strtoupper($billingAddress->getFax());
        $countryId = 'ES';

        // Dirección de envío
        $customerFullNameship = strtoupper($shippingAddress->getFirstname(). ' ' .$shippingAddress->getLastname(). ' ' . $shippingAddress->getSuffix());
        $street2ship = strtoupper($shippingAddress->getStreetLine(2));
        $street1ship = strtoupper($shippingAddress->getStreetLine(1));
        $street3ship = strtoupper($shippingAddress->getStreetLine(3));
        $street4ship = strtoupper($shippingAddress->getStreetLine(4));
        $street5ship = '';
        $cityship = strtoupper($shippingAddress->getCity());;
        $postCodeship = strtoupper($shippingAddress->getPostcode());;
        $postCodeShortship = strtoupper(substr($shippingAddress->getPostcode(), 0, 2));
        $regionship = strtoupper($shippingAddress->getRegion());
        $stateship = 'ESP';

        $newsletter = 'N';
        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $this->subscriber->create()->loadByEmail($order->getCustomerEmail());
        if($subscriber->getId()){
            $newsletter = 'S';
        }
        $subscriberNews = $newsletter;

        $row = [
            $customerStart,
            $customerIde,
            $customerPipe,
            $customerFullName,
            $firstname,
            $lastname,
            $lastnamesecond,
            $customerGen,
            $customerDob,
            $taxvat,
            $email,
            //            $street,
            $street2,
            $street1,
            $street3,
            $street4,
            $street5,
            $city,
            $postCode,
            $postCodeShort,
            $region,
            $state,
            $telephone,
            $fax,
            $countryId,
            $customerFullNameship,
            //            $streetship,
            $street2ship,
            $street1ship,
            $street3ship,
            $street4ship,
            $street5ship,
            $cityship,
            $postCodeship,
            $postCodeShortship,
            $regionship,
            $stateship,
            $subscriberNews
        ];
        fputcsv($handle, $row);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param string $path
     */
    public function fillOrderData(\Magento\Sales\Model\Order $order, $path) {
        $heading = [
            __('A'),
            __('B'),
            __('C'),
            __('D'),
            __('E'),
            __('F'),
            __('G'),
            __('H'),
            __('I'),
            __('J'),
            __('K'),
            __('L'),
            __('M'),
            __('N'),
            __('O'),
            __('P'),
            __('Banco'),
            __('Tipo'),
            __('Hrs'),
            __(''),
            __(''),
            __(''),
            __(''),
            __(''),
            __(''),
            __(''),
            __(''),
            __(''),
            __(''),
            __(''),
            __(''),
            __(''),
            __(''),
            __(''),
            __(''),
            __('')
        ];

        $heading = [
            __('Fecha'),
            __('Numero de Pedido'),
            __('Estado'),
            __('Inpuesto'),
            __('Subtotal'),
            __('Inpuesto Envio'),
            __('Descuento'),
            __('Nombre de Factura'),
            __('Tax/Vat Factura'),
            __('Email Factura'),
            __('Direccion Factura'),
            __('Ciudad Factura'),
            __('Codigo Postal Factura'),
            __('Provincia Factura'),
            __('Pais Factura'),
            __('Telefono Factura'),
            __('Fax Factura'),
            __('Nombre de Envio'),
            __('Direccion de Envio'),
            __('Ciudad de Envio'),
            __('Codigo Postal Envio'),
            __('Provincia de Envio'),
            __('Pais de Envio'),
            __('Producto Sku')
        ];
        $handle = fopen($path, 'w');
        fputcsv($handle, $heading);

        $createdAt = $order->getCreatedAt();
        $orderID = $order->getIncrementId();

        $orderLines = [];

        foreach ($order->getAllItems() as $item) {
            // ARTICULOS VENTA
            $orderLines['items'][] = 'PVTV|PSR|TV|0';
            $orderLines['items'][] = $createdAt . '|' . $createdAt;
            $orderLines['items'][] = $orderID;
            $orderLines['items'][] = 0;
            $orderLines['items'][] = strtoupper($order->getCustomerTaxvat());
            $orderLines['items'][] = 0;
            $orderLines['items'][] = '111111';    // @ respuesta TPV
            $orderLines['items'][] = number_format($order->getGrandTotal(), 2, ',', '.');
            $orderLines['items'][] = 1;     //TODO $i

            $orderLines['items'][] = strtoupper($item->getSku());
            $orderLines['items'][] = round($item->getQtyOrdered(), 0);
            $orderLines['items'][] = number_format($item->getPrice(), 2, ',', '.');
            $orderLines['items'][] = '0,00';
            $orderLines['items'][] = number_format(($item->getQtyOrdered() * $item->getPrice()), 2, ',', '.');
            $orderLines['items'][] = number_format($item->getTaxAmount(), 2, ',', '.');
        }
        // TRANSPORTE
        $orderLines['transporte'][] = 'PVTV|PSR|TV|0';
        $orderLines['transporte'][] = $createdAt . '|' . $createdAt;
        $orderLines['transporte'][] = $orderID;
        $orderLines['transporte'][] = 0;
        $orderLines['transporte'][] = strtoupper($order->getCustomerTaxvat());
        $orderLines['transporte'][] = 0;
        $orderLines['transporte'][] = '111111'; // @ respuesta TPV
        $orderLines['transporte'][] = number_format($order->getGrandTotal(), 2, ',', '.');
        $orderLines['transporte'][] = '1';     //TODO $i
        $orderLines['transporte'][] = 'TRANS001';
        $orderLines['transporte'][] = 1; // cantidad en transporte? 1 por defecto? entiendo que son los gastos de env�o: a nivel de pedido, no de item
        $orderLines['transporte'][] = number_format($order->getShippingAmount(), 2, ',', '.'); // precio en transporte? gastos de env�o a nivel de pedido, no de item
        $orderLines['transporte'][] = '0,00';
        $orderLines['transporte'][] = number_format($order->getShippingAmount(), 2, ',', '.');
        $orderLines['transporte'][] = number_format($order->getShippingTaxAmount(), 2, ',', '.');

        if ($order->getDiscountAmount() > 0) {
            // DESCUENTOS
            $orderLines['descuento'][] = 'PVTV|PSR|TV|0';
            $orderLines['descuento'][] = $createdAt . '|' . $createdAt;
            $orderLines['descuento'][] = $orderID;
            $orderLines['descuento'][] = 0;
            $orderLines['descuento'][] = strtoupper($order->getCustomerTaxvat());
            $orderLines['descuento'][] = 0;
            $orderLines['descuento'][] = '111111'; // @ respuesta TPV
            $orderLines['descuento'][] = number_format($order->getGrandTotal(), 2, ',', '.');
            $orderLines['descuento'][] = '1';     //TODO $i
            $orderLines['descuento'][] = 'DTETV001';
            $orderLines['descuento'][] = 1; // cantidad de descuento? 1 por defecto?
            $orderLines['descuento'][] = number_format($order->getDiscountAmount(), 2, ',', '.');
            $orderLines['descuento'][] = '0,00';
            $orderLines['descuento'][] = number_format($order->getDiscountAmount(), 2, ',', '.');
            $orderLines['descuento'][] = number_format(($order->getDiscountAmount() - $order->getBaseDiscountAmount()), 2, ',', '.');
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

        var_dump($orderLines);die;

        $createdAt = $order->getCreatedAt();
        $incrementId = $order->getIncrementId();
        $orderStatus = $order->getStatus();
        $taxAmount = $order->getTaxAmount();
        $subTotal = $order->getSubtotal();
        $shippingTax = $order->getShippingTaxAmount();
        $discount = $order->getDiscountAmount();
        $nameBilling = '';
        $taxVatBilling = '';
        $emailBilling = '';
        $streetBilling = '';
        $cityBilling = '';
        $postalBilling = 0;
        $regionBilling = '';
        $countryBilling = '';
        $phoneBilling = 0;
        $faxBilling = '';
        $shippingMethodName = $order->getShippingDescription();
        $streetShipping = '';
        $cityShipping = '';
        $postalShipping = '';
        $regionShipping = '';
        $countryShipping = '';
        if($billingAddress = $order->getBillingAddress()) {
            $nameBilling = $billingAddress->getFirstname().' '.$billingAddress->getLastname();
            $taxVatBilling = $billingAddress->getVatId();
            $emailBilling = $billingAddress->getEmail();
            if($streetBillingArray = $billingAddress->getStreet()) {
                $streetBilling .= isset($streetBillingArray[0]) ? $streetBillingArray[0] : '';
                $streetBilling .= isset($streetBillingArray[1]) ? $streetBillingArray[1] : '';
            }
            $cityBilling = $billingAddress->getCity();
            $postalBilling = $billingAddress->getPostcode();
            $regionBilling = $billingAddress->getRegion();
            $countryBilling = $this->getCountryNameById($billingAddress->getCountryId());
            $phoneBilling = $billingAddress->getTelephone();
            $faxBilling = $billingAddress->getFax();
        }
        if($shippingAddress = $order->getShippingAddress()) {
            if($streetShippingArray = $shippingAddress->getStreet()) {
                $streetShipping .= isset($streetShippingArray[0]) ? $streetShippingArray[0] : '';
                $streetShipping .= isset($streetShippingArray[1]) ? $streetShippingArray[1] : '';
            }
            $cityShipping = $shippingAddress->getCity();
            $postalShipping = $shippingAddress->getPostcode();
            $regionShipping = $shippingAddress->getRegion();
            $countryShipping = $this->getCountryNameById($shippingAddress->getCountryId());
        }
        /**
         * @var \Magento\Sales\Model\Order\Item $item
         */
        foreach ($order->getAllItems() as $item) {
            $row = [
                $createdAt,
                $incrementId,
                $orderStatus,
                $taxAmount,
                $subTotal,
                $shippingTax,
                $discount,
                $nameBilling,
                $taxVatBilling,
                $emailBilling,
                $streetBilling,
                $cityBilling,
                $postalBilling,
                $regionBilling,
                $countryBilling,
                $phoneBilling,
                $faxBilling,
                $shippingMethodName,
                $streetShipping,
                $cityShipping,
                $postalShipping,
                $regionShipping,
                $countryShipping,
                $item->getProduct()->getSku()
            ];
            fputcsv($handle, $row);
        }
    }
    /**
     * @var string $orderIncrement
     * @return string
     */
    private function createCsvFile($orderIncrement, $csv, $folder) {
        $fileDirectoryPath = null;
        try {
            $mediaPath = \Magento\Framework\App\Filesystem\DirectoryList::ROOT;
            $fileDirectoryPath = $this->directoryList->getPath($mediaPath);
            $fileDirectoryPath = $fileDirectoryPath.'/'.$folder;
        }catch (\Exception $exception) {
            $this->logger->addCritical(__('Error accessing to the folder in order to save the order csv.'));
        }
        if(!is_dir($fileDirectoryPath)) {
            mkdir($fileDirectoryPath, 0777, true);
        }
        return $fileDirectoryPath . '/' .$orderIncrement.$csv;
    }

    /**
     * @param $countryCode
     * @return string
     */
    private function getCountryNameById($countryCode){
        $country = $this->countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
    }
}