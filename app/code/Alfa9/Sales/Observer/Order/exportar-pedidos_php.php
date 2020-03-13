<?php
header('Content-Type: text/html; charset=UTF-8');
set_time_limit(0);
ini_set('memory_limit', '1024M');
require_once('..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Mage.php');
require_once 'functions.php';


$htaccess = '..' . DIRECTORY_SEPARATOR . '.htaccess';

Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
umask(0);
Mage::app('admin', 'store');
Mage::register('isSecureArea', 1);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

// path del  folder donde se encuentra el csv a importar
$pathToFolder = '../../erpdata/';
// path del folder donde se almacenaran los pedidos y clientes exportados
$pathToFolderExport = '../../erpdata/export/';
// path al folder de los logs
$pathToFolderLog = '../../erpdata/log/';


$folder = $pathToFolderExport;

// variable donde almacenaremos los errores ocurridos
$logError = '';    // Sep 16 15:23:01 script ERROR: blab bbab
// variable donde almacenaremos el log de los productos actualizados
$logProcesados = '';

$msgOutProcesados = '';

$nombreFichero = 'getorders.php';

if (file_exists($htaccess)) {
    // parse htaccess file
    $data = file_get_contents($htaccess);
    $matches = array();
    preg_match_all('#^\s+?php_value\s+([a-z_]+)\s+(.+)$#siUm', $data, $matches, PREG_SET_ORDER);
    if ($matches) {
        foreach ($matches as $match) {
            @ini_set($match[1], $match[2]);
        }
    }
    preg_match_all('#^\s+?php_flag\s+([a-z_]+)\s+(.+)$#siUm', $data, $matches, PREG_SET_ORDER);
    if ($matches) {
        foreach ($matches as $match) {
            @ini_set($match[1], $match[2]);
        }
    }
}
// actualizamos los pedidos a completado antes de realizar la exportacion
$logError .= updateOrdersPendingWithInvoice();   // lo comentamos por el momento


//echo date('d/m/Y H:i:s') . ' Iniciando proceso getorders()<br />';

// Get orders
// Get datetime of last order
$resource = Mage::getSingleton('core/resource');
$readConnection = $resource->getConnection('core_read');
$writeConnection = $resource->getConnection('core_write');

//$exportedAt = getLastDateExported('pedidos-last-update/last_update.txt');
//echo '<pre>date end order: '.$exportedAt .'</pre>';
$currentDateExport = date('Y-m-d H:i:s');


// support table prefix if one is being used
$table_prefix = Mage::getConfig()->getTablePrefix();

//sales_order_exportadas  es una  tabla auxiliar donde se almacenan los pedidos ya exportados
$sales_order_exportadas = 'sales_order_exportadas';
// get an array of the orders
$orders = $readConnection->fetchAll("SELECT sales.*
                                      FROM sales_flat_order AS sales
                                      WHERE sales.status = 'complete'
                                      and sales.entity_id not in (
                                        SELECT so_exp.order_id
                                        FROM " . $sales_order_exportadas . " so_exp )
                                      ORDER BY sales.entity_id ASC");

// $write->query("update TableName set field = 'value'");

$orderToUpdate = array();

/*
$orders = Mage::getModel('sales/order')->getCollection();
$orders ->addAttributeToFilter('status', array('eq' => Mage_Sales_Model_Order::STATE_COMPLETE))
        ->addAttributeToFilter('created_at', array('from' => $exportedAt));
*/
/*
CONSTANTES:
STATE_NEW
STATE_PENDING_PAYMENT
STATE_COMPLETE
STATE_PROCESSING

Mage_Sales_Model_Order::STATE_PROCESSING

 */

//echo '<pre>Nro de orders: '.count($orders) .'</pre>';
if (count($orders) == 0) {
    $logError .= date('M d H:i:s') . ' ' . $nombreFichero . ' ERROR: ' . 'No se han encontrado pedidos por exportar';
    // archivo donde guardaremos el log ocurrido
    $nameoutput = $pathToFolderLog . 'log_error_exportar' . date('YmdHis');
    // escribimos en un archivo el log de error
    write_file_log($nameoutput, $logError);

    die();
    //die($logError);
}

//echo date('d/m/Y H:i:s') . ' Se han encontrado ' . count($orders) . ' pedidos por exportar<br />';
$i = 0;
$orderLines = array();
foreach ($orders as $key => $value) {
    // order id
    $orderid = $value['entity_id'];
    // shipping address
    $order = Mage::getModel('sales/order')->load($orderid);

    $orderID = $order->getIncrementId();

    $listOrderIds[] = array(
        'increment_id' => $orderID,
        'fecha_exportacion' => $currentDateExport,
        'order_id' => $orderid
    );


    $state = $order->getState();
    $methodPayment = $order->getPayment()->getMethod();

    $createdAt = $order->getCreatedAt();
    $idOrder = $order->getId();

    $createdAt = explode(' ', $order->getCreatedAt());
    array_pop($createdAt);
    $createdAt = array_pop($createdAt);
    // Y-m-d > d-m-Y
    $createdAt = explode('-', $createdAt);
    $createdAt = array_reverse($createdAt);
    $createdAt = implode('-', $createdAt);


    $customerDob = $order->getCustomerDob();
    if (!empty($customerDob)) {
        $customerDob = explode(' ', $customerDob);
        array_pop($customerDob);
        $customerDob = array_pop($customerDob);
    }

    $i = 1;
    foreach ($order->getAllItems() as $item) {
        if ($item->getParentItem()) {
            continue;
        }

        // ARTICULOS VENTA
        $orderLines[$orderID]['items']['item' . $i] = array();
        $orderLines[$orderID]['items']['item' . $i][] = 'PVTV|PSR|TV|0';
        $orderLines[$orderID]['items']['item' . $i][] = $createdAt . '|' . $createdAt;
        $orderLines[$orderID]['items']['item' . $i][] = $orderID;
        $orderLines[$orderID]['items']['item' . $i][] = 0;
        $orderLines[$orderID]['items']['item' . $i][] = strtoupper($order->getCustomerTaxvat());
        $orderLines[$orderID]['items']['item' . $i][] = 0;
        $orderLines[$orderID]['items']['item' . $i][] = '111111'; // @ respuesta TPV
        $orderLines[$orderID]['items']['item' . $i][] = number_format($order->getGrandTotal(), 2, ',', '.');

        /*
        echo '<h2>'.$item->getSku().'</h2>';
        echo '<h2>----------------------------</h2>';
        */
        $orderLines[$orderID]['items']['item' . $i][] = $i;
        $orderLines[$orderID]['items']['item' . $i][] = strtoupper($item->getSku());
        $orderLines[$orderID]['items']['item' . $i][] = round($item->getQtyOrdered(), 0);
        $orderLines[$orderID]['items']['item' . $i][] = number_format($item->getPrice(), 2, ',', '.');
        $orderLines[$orderID]['items']['item' . $i][] = '0,00';
        $orderLines[$orderID]['items']['item' . $i][] = number_format(($item->getQtyOrdered() * $item->getPrice()), 2, ',', '.');
        $orderLines[$orderID]['items']['item' . $i][] = number_format($item->getTaxAmount(), 2, ',', '.');

        $i++;
    }

    $i = 1; // Desde aca es constante = 1

    // TRANSPORTE
    $orderLines[$orderID]['transporte'][] = 'PVTV|PSR|TV|0';
    $orderLines[$orderID]['transporte'][] = $createdAt . '|' . $createdAt;
    $orderLines[$orderID]['transporte'][] = $orderID;
    $orderLines[$orderID]['transporte'][] = 0;
    $orderLines[$orderID]['transporte'][] = strtoupper($order->getCustomerTaxvat());
    $orderLines[$orderID]['transporte'][] = 0;
    $orderLines[$orderID]['transporte'][] = '111111'; // @ respuesta TPV
    $orderLines[$orderID]['transporte'][] = number_format($order->getGrandTotal(), 2, ',', '.');
    $orderLines[$orderID]['transporte'][] = $i;
    $orderLines[$orderID]['transporte'][] = 'TRANS001';
    $orderLines[$orderID]['transporte'][] = '1'; // cantidad en transporte? 1 por defecto? entiendo que son los gastos de env�o: a nivel de pedido, no de item
    $orderLines[$orderID]['transporte'][] = number_format($order->getShippingAmount(), 2, ',', '.'); // precio en transporte? gastos de env�o a nivel de pedido, no de item
    $orderLines[$orderID]['transporte'][] = '0,00';
    $orderLines[$orderID]['transporte'][] = number_format($order->getShippingAmount(), 2, ',', '.');
    $orderLines[$orderID]['transporte'][] = number_format($order->getShippingTaxAmount(), 2, ',', '.');

    if ($order->getDiscountAmount() > 0) {
        // DESCUENTOS
        $orderLines[$orderID]['descuento'][] = 'PVTV|PSR|TV|0';
        $orderLines[$orderID]['descuento'][] = $createdAt . '|' . $createdAt;
        $orderLines[$orderID]['descuento'][] = $orderID;
        $orderLines[$orderID]['descuento'][] = 0;
        $orderLines[$orderID]['descuento'][] = strtoupper($order->getCustomerTaxvat());
        $orderLines[$orderID]['descuento'][] = 0;
        $orderLines[$orderID]['descuento'][] = '111111'; // @ respuesta TPV
        $orderLines[$orderID]['descuento'][] = number_format($order->getGrandTotal(), 2, ',', '.');
        $orderLines[$orderID]['descuento'][] = $i;
        $orderLines[$orderID]['descuento'][] = 'DTETV001';
        $orderLines[$orderID]['descuento'][] = '1'; // cantidad de descuento? 1 por defecto?
        $orderLines[$orderID]['descuento'][] = number_format($order->getDiscountAmount(), 2, ',', '.');
        $orderLines[$orderID]['descuento'][] = '0,00';
        $orderLines[$orderID]['descuento'][] = number_format($order->getDiscountAmount(), 2, ',', '.');
        $orderLines[$orderID]['descuento'][] = number_format(($order->getDiscountAmount() - $order->getBaseDiscountAmount()), 2, ',', '.');
    }

    if ($order->getGiftMessageId()) {
        // ENVOLTORIO REGALO
        $orderLines[$orderID]['regalo'][] = 'PVTV|PSR|TV|0';
        $orderLines[$orderID]['regalo'][] = $createdAt . '|' . $createdAt;
        $orderLines[$orderID]['regalo'][] = $orderID;
        $orderLines[$orderID]['regalo'][] = 0;
        $orderLines[$orderID]['regalo'][] = strtoupper($order->getCustomerTaxvat());
        $orderLines[$orderID]['regalo'][] = 0;
        $orderLines[$orderID]['regalo'][] = '111111'; // @ respuesta TPV
        $orderLines[$orderID]['regalo'][] = number_format($order->getGrandTotal(), 2, ',', '.');
        $orderLines[$orderID]['regalo'][] = $i;
        $orderLines[$orderID]['regalo'][] = 'ENVTV001';
        $orderLines[$orderID]['regalo'][] = 1;
        $orderLines[$orderID]['regalo'][] = '0,00';
        $orderLines[$orderID]['regalo'][] = '0,00';
        $orderLines[$orderID]['regalo'][] = '0,00';
        $orderLines[$orderID]['regalo'][] = '0,00';
    }


    // CLIENTES
    $customerId = $order->getCustomerId();
    if (empty($customerId)) {
        $customerId = $orderID;
    }

    $billingAddress = $order->getBillingAddress();
    $shippingAddress = $order->getShippingAddress();

    $customerGender = 'M';
    if ($billingAddress->getPrefix() == 'Sra.') {
        $customerGender = 'F';
    }

    $lastName2 = $order->getBillinggetCustomerSuffix();
    if (empty($lastName2)) {
        $lastName2 = '.';
    }

    $checkStreet = $billingAddress->getStreet2();
    if (empty($checkStreet)) {
        $billingStreet = $billingAddress->getStreet1();
    } else {
        $billingStreet = $billingAddress->getStreet2();
    }

    $checkStreet = $shippingAddress->getStreet2();
    if (empty($checkStreet)) {
        $shippingStreet = $shippingAddress->getStreet1();
    } else {
        $shippingStreet = $shippingAddress->getStreet2();
    }

    $orderLines[$orderID]['cliente'][] = 'A|CLI';
    $orderLines[$orderID]['cliente'][] = strtoupper($customerId);
    $orderLines[$orderID]['cliente'][] = '||'; // ser�an 4 pipes, pero al hacer implode para el fichero de texto ya se a�adir�n por delante y por detr�s
    $orderLines[$orderID]['cliente'][] = strtoupper($billingAddress->getFirstname() . ' ' . $billingAddress->getLastname() . ' ' . $billingAddress->getSuffix());
    $orderLines[$orderID]['cliente'][] = strtoupper($billingAddress->getFirstname());
    $orderLines[$orderID]['cliente'][] = strtoupper($billingAddress->getLastname());
    $orderLines[$orderID]['cliente'][] = strtoupper($lastName2);
    $orderLines[$orderID]['cliente'][] = strtoupper($customerGender);
    $orderLines[$orderID]['cliente'][] = strtoupper($customerDob);
    $orderLines[$orderID]['cliente'][] = strtoupper($order->getCustomerTaxvat());
    $orderLines[$orderID]['cliente'][] = strtoupper($billingAddress->getEmail());
    // direccion facturacion
    $orderLines[$orderID]['cliente'][] = strtoupper($billingAddress->getStreet2());//strtoupper($billingStreet); // Calle
    $orderLines[$orderID]['cliente'][] = strtoupper($billingAddress->getStreet1()); // Tipo de Calle
    $orderLines[$orderID]['cliente'][] = '';//strtoupper($billingAddress->getStreet3()); // ESCALERA
    $orderLines[$orderID]['cliente'][] = '';//strtoupper($billingAddress->getStreet4()); // PLANTA
    $orderLines[$orderID]['cliente'][] = '';//strtoupper($billingAddress->getStreet5()); // PUERTA
    $orderLines[$orderID]['cliente'][] = strtoupper($billingAddress->getCity());
    $orderLines[$orderID]['cliente'][] = strtoupper($billingAddress->getPostcode());
    $orderLines[$orderID]['cliente'][] = strtoupper(substr($billingAddress->getPostcode(), 0, 2));
    $orderLines[$orderID]['cliente'][] = strtoupper($billingAddress->getRegion());
    $orderLines[$orderID]['cliente'][] = 'ESP';
    $orderLines[$orderID]['cliente'][] = strtoupper($billingAddress->getTelephone());
    $orderLines[$orderID]['cliente'][] = strtoupper($billingAddress->getFax());
    $orderLines[$orderID]['cliente'][] = 'ES';


    // direccion envio
    $orderLines[$orderID]['cliente'][] = strtoupper($order->getShippingAddress()->getFirstname() . ' ' . $order->getShippingAddress()->getLastname() . ' ' . $order->getShippingAddress()->getSuffix());
    $orderLines[$orderID]['cliente'][] = strtoupper($shippingAddress->getStreet2());//strtoupper($shippingStreet); // Calle
    $orderLines[$orderID]['cliente'][] = strtoupper($shippingAddress->getStreet1()); // Tipo de Calle
    $orderLines[$orderID]['cliente'][] = strtoupper($shippingAddress->getStreet3()); // ESCALERA
    $orderLines[$orderID]['cliente'][] = strtoupper($shippingAddress->getStreet4()); // PLANTA
    $orderLines[$orderID]['cliente'][] = strtoupper($shippingAddress->getStreet5()); // PUERTA
    $orderLines[$orderID]['cliente'][] = strtoupper($shippingAddress->getCity());
    $orderLines[$orderID]['cliente'][] = strtoupper($shippingAddress->getPostcode());
    $orderLines[$orderID]['cliente'][] = strtoupper(substr($shippingAddress->getPostcode(), 0, 2));
    $orderLines[$orderID]['cliente'][] = strtoupper($shippingAddress->getRegion());
    $orderLines[$orderID]['cliente'][] = 'ESP';
    $newsletter = 'N';
    $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($order->getCustomerEmail());
    if ($subscriber->getId()) {
        $newsletter = 'S';
    }
    $orderLines[$orderID]['cliente'][] = $newsletter;
    //break;
    /*
    try {

      // esto  se  debe  remplazar por un archivo de texto que tendra el dato de la ultima exportacion de pedidos
      $nameoutput = $pathToFolderExport.'last_update.txt';

      write_file_log($nameoutput, date('Y-m-d H:i:s'));


      // $writeConnection->query('INSERT INTO `sales_exported` VALUES (NULL,"' . $orderID . '","' . now() . '")');


    } catch (Exception $e) {

      $logError .=  date('M d H:i:s') .' '.$nombreFichero.' ERROR: '.'Error al registrar el pedido ' . $orderID . ' como exportado: ' . $e->getMessage();
      // archivo donde guardaremos el log ocurrido
      $nameoutput = $pathToFolderLog.'log_error_'.date('YmdHis');
      // escribimos en un archivo el log de error
      write_file_log($nameoutput, $logError);
      //die($logError);

    }
    echo date('d/m/Y H:i:s') . ' Datos recogidos OK para el pedido ' . $orderID . '<br />';

    */
}// fin  principal for each

//echo '<pre>'.print_r($listOrderIds, true).'</pre>';


$limit = count($orderLines);
$i = 1;
foreach ($orderLines as $orderId => $data) {

    $filenameTime = date('dmYHi');
    $orderFileLines = array();
    $customerFileLines = array();
    $orderFilename = $folder . 'psr_web_carga_pedven' . $filenameTime . '.txt';
    $customerFilename = $folder . 'psr_web_carga_clien' . $filenameTime . '.txt';

    echo '<pre>' . print_r($data, true) . '</pre>';
    echo '<pre> ************************************* </pre>';

    foreach ($data['items'] as $item) {
        $orderFileLines[] = implode('|', $item) . '|';
    }
    $orderFileLines[] = implode('|', $data['transporte']) . '|';
    if (array_key_exists('descuento', $data)) {
        $orderFileLines[] = implode('|', $data['descuento']) . '|';
    }
    if (array_key_exists('regalo', $data)) {
        $orderFileLines[] = implode('|', $data['regalo']) . '|';
    }

    $customerFileLines[] = implode('|', $data['cliente']) . '|';

    $last = FALSE;
    if ($i == $limit) {
        $last = TRUE;
    }

    // save order file
    saveFile($orderFilename, $orderFileLines, $last);
    $msgOutProcesados .= date('d/m/Y H:i:s') . ' Fichero de pedido generado OK para el pedido ' . $orderId . "\n";

    // save customer file
    saveCustomerFile($customerFilename, $customerFileLines, $last);
    $msgOutProcesados .= date('d/m/Y H:i:s') . ' Fichero de cliente generado OK para el pedido ' . $orderId . "\n";

    $i++;
}

// guardamos la fecha de la ultima exportacion
//$nameoutput = 'pedidos-last-update/last_update.txt';
//write_file_log($nameoutput, $currentDateExport);


// guardamos el  log del  numero de pedidos exportados
$nameoutput = $pathToFolderLog . 'log_pedidos_' . date('YmdHis');
$logProcesados .= date('M d H:i:s') . ' ' . $nombreFichero . ' MENSAJE: ' . $msgOutProcesados;
write_file_log($nameoutput, $logProcesados);

if ($logError != '') {

    // archivo donde guardaremos el log ocurrido
    $nameoutput = $pathToFolderLog . 'log_error_exportar_' . date('YmdHis');
    // escribimos en un archivo el log de error
    write_file_log($nameoutput, $logError);

}

// insertar los pedidos que se estan exportando
insertPedidosExportados($listOrderIds, $writeConnection);


//die('fin de la prueba de obtener metodo de pago '. date('Y/d/m  H:i:s'));


/*******************************************************************************
 * FUNCTION UTILITY
 *******************************************************************************/
/**
 * Funcion que actualiza los pedidos pendientes pero que cuentan con factura
 * @return [type] [description]
 */
function updateOrdersPendingWithInvoice()
{

    $logError = '';

    $orders = Mage::getModel('sales/order')->getCollection();
    $orders->addAttributeToFilter('status', array('eq' => 'pending'));

    if (count($orders) == 0) {
        return '';
    }

    foreach ($orders->getItems() as $order) {

        $orderID = $order->getIncrementId();
        $state = $order->getState();
        $methodPayment = $order->getPayment()->getMethod();

        // verificamos que solo ingresen los pedidos pendientes
        if (strcmp(trim($state), 'pending') !== 0) {
            continue;
        }

        // verificamos que el pedido tenga una factura
        if (!$order->hasInvoices()) {
            continue;
        }


        // en caso que sea un pedido que tiene factura  actualizamos el estado a complatado
        $hasComplete = _createShipment($orderID);
        if (!$hasComplete) {
            $logError .= date('M d H:i:s') . '  getorders.php  ERROR: No se han logrado completar el pedido  con  Nro oreden: ' . $orderID;
        }
    }

    return $logError;
}

function _createShipment($orderIncrementId = '')
{
    // Load Product ..
    $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);

    // Create Qty array
    $shipmentItems = array();
    foreach ($order->getAllItems() as $item) {
        $shipmentItems [$item->getId()] = $item->getQtyToShip();
    }

    // Prepear shipment and save ....
    if ($order->getId() && !empty($shipmentItems) && $order->canShip()) {

        $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($shipmentItems);

        $shipment->register();
        $shipment->sendEmail();

        $tracker = Mage::getModel('sales/order_shipment_track');
        $tracker->setShipment($shipment);
        //$tracker->setData( 'title', 'United Parcel Service' );
        //$tracker->setData( 'number', $importData['Tracking Number'] );
        //$tracker->setData( 'carrier_code', 'ups' );
        $tracker->setData('order_id', $orderIncrementId);

        $shipment->addTrack($tracker);
        $shipment->save();

        $order->setData('state', "complete");
        $order->setStatus("complete");

        $history = $order->addStatusHistoryComment('Order marked as complete by shipment code.', false);
        $history->setIsCustomerNotified(false);
        $order->save();

        return true;
    }

    return false;
}

// Generic function to save files to filesystem
function saveFile($filename, $filedata, $isLast)
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
        $fh = @fopen($filename, 'a');
        fwrite($fh, $output);
        fclose($fh);
    } catch (Exception $e) {
        echo '<h4>Problemas para almacenar el archivo  ' . $filename . '</h4>';
        //die();
        //die('Error al intentar guardar el fichero: ' . $e->getMessage());
    }
}

// Generic function to save files to filesystem
function saveCustomerFile($filename, $filedata, $last)
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
        $fh = @fopen($filename, 'a');
        fwrite($fh, $output);
        fclose($fh);
    } catch (Exception $e) {
        echo '<h4>Problemas para almacenar el archivo  ' . $filename . '</h4>';
        //die();
        //die('Error al intentar guardar el fichero: ' . $e->getMessage());
    }
}

function insertPedidosExportados($datas, $coneccion)
{

    if (!is_array($datas) && count($datas) < 1)
        return '';

    foreach ($datas as $key => $value) {
        $coneccion->beginTransaction();
        $__fields = $value;
        $coneccion->insert('sales_order_exportadas', $__fields);
        $coneccion->commit();
    }

}