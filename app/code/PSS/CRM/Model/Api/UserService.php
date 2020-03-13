<?php
/**
 *  @author Xavier Sanz <xsanz@pss.com>
 *  @copyright Copyright (c) 2017 PSS (http://www.pss.com)
 *  @package PSS
 */

namespace PSS\CRM\Model\Api;

use Braintree\Exception;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use PSS\CRM\Model\Api\AbstractModel;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;

/**
 * Class UserService
 * @package PSS\CRM\Model\Api
 */

class UserService extends AbstractModel
{

    /**
     * @var \PSS\CRM\Helper\UserService
     */
    protected $_helper;

    /**
     * @var
     */
    protected $_wsdl;

    /**
     *
     */
    protected $_logger;

    /**
     * @var \Magento\Customer\Model\Customer
     */

    protected $_customer;

    public $_transportBuilder;

    public $_soapClient;

    public $_componentRegistrar;

    private $_addressFactory;

    CONST ATTRIBUTES_WEBSERVICE_MAGENTO = [
        'calleEnvio' => 'shipping_street',
        'calleFacturacion' => 'billing_street',
        'cifCliente' => 'taxvat',
        'ciudadEnvio' => 'shipping_city',
        'ciudadFacturacion' => 'billing_city',
        'codigoClienteCRM' => 'id_crm',
        'codigoClienteErp' => 'id_erp',
        'codigoPostalEnvio' => 'shipping_postcode',
        'codigoPostalFacturacion' => 'billing_postcode',
        'codigoTarjeta' => 'card_code',
        'correoElectronicoFacturacion' => 'billing_address_email',
        'esEmpleado' => 'is_employee',
        'escaleraEnvio' => 'shipping_stairs',
        'escaleraFacturacion' => 'billing_stairs',
        'fechaAlta' => 'fecha_alta_crm',
        'fechaBaja' => 'fecha_baja_crm',
        //'fechaInicioPuntosFidelizacion' => 'fecha_inicio_puntos',
        //'fechaFinPuntosFidelizacion' => 'fecha_fin_puntos',
        'fechaRGPD' => 'creation',
        'idiomaDeComunicacion' => 'comm_lang',
        'listaMarketing' => 'list_id',
        'movilEnvio' => 'shipping_telephone_mobile',
        'movilFacturacion' => 'billing_telephone_mobile',
        'pisoEnvio' => 'shipping_floor',
        'pisoFacturacion' => 'billing_floor',
        'telefonoEnvio' => 'shipping_telephone',
        'telefonoFacturacion' => 'billing_telephone',
        'tipoDireccionEnvio' => 'shipment_type',
        'ultimaCompra' => 'id_last_purchase'
    ];

    /**
     * UserService constructor.
     * @param \PSS\CRM\Logger\Logger $_logger
     * @param \Magento\Framework\Mail\Template\TransportBuilder $_transportBuilder
     * @param \Zend\Soap\Client $_soapClient
     * @param \PSS\CRM\Api\QueueRepositoryInterface $_queueRepositoryInterface
     * @param \PSS\CRM\Model\QueueFactory $_queueFactory
     * @param ComponentRegistrarInterface $_componentRegistrar
     * @param \Magento\Customer\Model\Customer $_customer
     * @param \PSS\CRM\Helper\UserService $_helper
     * @param $wsdl
     */
    public function __construct(
        \PSS\CRM\Logger\Logger $_logger,
        \Magento\Framework\Mail\Template\TransportBuilder $_transportBuilder,
        \Zend\Soap\Client $_soapClient,
        \PSS\CRM\Api\QueueRepositoryInterface $_queueRepositoryInterface,
        \PSS\CRM\Model\QueueFactory $_queueFactory,
        ComponentRegistrarInterface $_componentRegistrar,
        \Magento\Customer\Model\Customer $_customer,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \PSS\CRM\Helper\UserService $_helper,
        $wsdl
    )
    {

        $this->_logger = $_logger;
        $this->_transportBuilder = $_transportBuilder;
        $this->soapClient = $_soapClient;
        $this->componentRegistrar = $_componentRegistrar;
        $this->_customer = $_customer;
        $this->_addressFactory = $addressFactory;
        $this->_helper = $_helper;
        $this->_wsdl = $wsdl;

        parent::__construct($_logger, $_soapClient, $_queueRepositoryInterface, $_queueFactory, $_componentRegistrar, $_transportBuilder);
    }

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return string
     */
    public function query($customer = null) {

        if($customer) {
            $customerLastName = explode(' ', $customer->getLastname());

            $primerApellidoCliente = $customerLastName[0] ? $customerLastName[0] : '';
            $segundoApellidoCliente = isset($customerLastName[1]) ? $customerLastName[1] : '';
            $publicidad =  $customer->getCustomAttribute('wants_publicity') ? $customer->getCustomAttribute('wants_publicity')->getValue() : '';
            $idiomaDeComunicacion =  $customer->getCustomAttribute('comm_lang') ? $customer->getCustomAttribute('comm_lang')->getValue() : '';
            $esEmpleado =  $customer->getCustomAttribute('is_employee') ? $customer->getCustomAttribute('is_employee')->getValue() : '';
            $cifCliente =  $customer->getCustomAttribute('taxvat') ? $customer->getCustomAttribute('taxvat')->getValue() : '';
            $ultimaCompra =  $customer->getCustomAttribute('id_last_purchase') ? $customer->getCustomAttribute('id_last_purchase')->getValue() : '';
            $fechaInicioPuntos =  $customer->getCustomAttribute('fecha_inicio_puntos') ? $customer->getCustomAttribute('fecha_inicio_puntos')->getValue() : '';
            $fechaFinPuntos =  $customer->getCustomAttribute('fecha_fin_puntos') ? $customer->getCustomAttribute('fecha_fin_puntos')->getValue() : '';
            $fechaBajaCRM =  $customer->getCustomAttribute('fecha_baja_crm') ? $customer->getCustomAttribute('fecha_baja_crm')->getValue() : '';
            $fechaAltaCRM =  $customer->getCustomAttribute('fecha_alta_crm') ? $customer->getCustomAttribute('fecha_alta_crm')->getValue() : '';
            $id_crm =  $customer->getCustomAttribute('id_crm') ? $customer->getCustomAttribute('id_crm')->getValue() : '';
            $codigoClienteErp =  $customer->getCustomAttribute('id_erp') ? $customer->getCustomAttribute('id_erp')->getValue() : '';
            $codigoTarjeta =  $customer->getCustomAttribute('card_code') ? $customer->getCustomAttribute('card_code')->getValue() : '';
            $fechaRGPD =  $customer->getCustomAttribute('creation') ? $customer->getCustomAttribute('creation')->getValue() : '';

            $calleFacturacion = $customer->getCustomAttribute('billing_street') ? $customer->getCustomAttribute('billing_street')->getValue() : '';
            $pisoFacturacion =  $customer->getCustomAttribute('billing_floor') ? $customer->getCustomAttribute('billing_floor')->getValue() : '';
            $escaleraFacturacion =  $customer->getCustomAttribute('billing_stairs') ? $customer->getCustomAttribute('billing_stairs')->getValue() : '';
            $codigoPostalFacturacion =  $customer->getCustomAttribute('billing_postcode') ? $customer->getCustomAttribute('billing_postcode')->getValue() : '';
            $ciudadFacturacion =  $customer->getCustomAttribute('billing_city') ? $customer->getCustomAttribute('billing_city')->getValue() : '';
            $telefonoFacturacion =  $customer->getCustomAttribute('billing_telephone') ? $customer->getCustomAttribute('billing_telephone')->getValue() : '';
            $movilFacturacion =  $customer->getCustomAttribute('billing_telephone_mobile') ? $customer->getCustomAttribute('billing_telephone_mobile')->getValue() : '';
            $correoElectronicoFacturacion =  $customer->getCustomAttribute('billing_address_email') ? $customer->getCustomAttribute('billing_address_email')->getValue() : '';

            $calleEnvio =  $customer->getCustomAttribute('shipping_street') ? $customer->getCustomAttribute('shipping_street')->getValue() : '';
            $pisoEnvio =  $customer->getCustomAttribute('shipping_floor') ? $customer->getCustomAttribute('shipping_floor')->getValue() : '';
            $escaleraEnvio =  $customer->getCustomAttribute('shipping_stairs') ? $customer->getCustomAttribute('shipping_stairs')->getValue() : '';
            $codigoPostalEnvio =  $customer->getCustomAttribute('shipping_postcode') ? $customer->getCustomAttribute('shipping_postcode')->getValue() : '';
            $ciudadEnvio =  $customer->getCustomAttribute('shipping_city') ? $customer->getCustomAttribute('shipping_city')->getValue() : '';
            $telefonoEnvio =  $customer->getCustomAttribute('shipping_telephone') ? $customer->getCustomAttribute('shipping_telephone')->getValue() : '';
            $movilEnvio =  $customer->getCustomAttribute('shipping_telephone_mobile') ? $customer->getCustomAttribute('shipping_telephone_mobile')->getValue() : '';
            $correoElectronicoEnvio =  $customer->getCustomAttribute('shipping_address_email') ? $customer->getCustomAttribute('shipping_address_email')->getValue() : '';



            $options = [
                'WcfGestionarCliente' => [
                    'cliente' => [
                        'codigoClienteMagento'  =>  $customer->getId(),
                        'codigoClienteCRM'  => $id_crm,
                        'nombreCliente' =>  $customer->getFirstname(),
                        'sexo'  =>  $customer->getGender(),
                        'codigoClienteErp'  => $codigoClienteErp,
                        'codigoTarjeta' =>      $codigoTarjeta,
                        'fechaRGPD'     =>  $fechaRGPD,


                        'primerApellidoCliente' =>  $primerApellidoCliente,
                        'segundoApellidoCliente' =>  $segundoApellidoCliente,
                        'publicidad'  =>  $publicidad,
                        'idiomaDeComunicacion'  =>  $idiomaDeComunicacion,
                        'esEmpleado'  =>  $esEmpleado,
                        'cifCliente'  =>  $cifCliente,
                        'ultimaCompra'  =>  $ultimaCompra,
                        'fechaInicioPuntos'  =>  $fechaInicioPuntos,
                        'fechaFinPuntos'  =>  $fechaFinPuntos,
                        'fechaBajaCRM'  =>  $fechaBajaCRM,
                        'fechaAltaCRM'  =>  $fechaAltaCRM,


                        'calleFacturacion' =>  $calleFacturacion,
                        'pisoFacturacion' =>  $pisoFacturacion,
                        'escaleraFacturacion' =>  $escaleraFacturacion,
                        'codigoPostalFacturacion' =>  $codigoPostalFacturacion,
                        'ciudadFacturacion' =>  $ciudadFacturacion,
                        'telefonoFacturacion' =>  $telefonoFacturacion,
                        'movilFacturacion' =>  $movilFacturacion,
                        'correoElectronicoFacturacion' =>  $correoElectronicoFacturacion,

                        'calleEnvio' =>  $calleEnvio,
                        'pisoEnvio' =>  $pisoEnvio,
                        'escaleraEnvio' =>  $escaleraEnvio,
                        'codigoPostalEnvio' =>  $codigoPostalEnvio,
                        'ciudadEnvio' =>  $ciudadEnvio,
                        'telefonoEnvio' =>  $telefonoEnvio,
                        'movilEnvio' =>  $movilEnvio,
                        'correoElectronicoEnvio' =>  $correoElectronicoEnvio,



                    ],
                    'origen'    =>  'Magento',
                    'accion'    =>  'Recuperacion',
                ]



            ];


            try {
                $xml= $this->call($this->_wsdl, $this->_helper, 'WcfGestionarCliente', $options);
                if (is_string($xml)) {
                    $p = new \Magento\Framework\Xml\Parser;
                    $p->loadXML($xml);
                    $array = $p->xmlToArray();
                    if(is_array($array)) {
                        $result = [];
                        $result['resultado'] = $array['s:Envelope']['s:Body']['WcfGestionarClienteResponse']['WcfGestionarClienteResult']['a:resultado'];
                        $result['resultadoDescripcion'] = $array['s:Envelope']['s:Body']['WcfGestionarClienteResponse']['WcfGestionarClienteResult']['a:resultadoDescripcion'];
                        if($result['resultado'] == 0) {
                            $result['resultadoDatosCliente'] = $array['s:Envelope']['s:Body']['WcfGestionarClienteResponse']['WcfGestionarClienteResult']['a:clientes']['a:Cliente'];
                        }
                        return $result;
                    }
                }
            } catch (\Exception $e) {
                $this->_logger->info('ERROR API REQUEST SERVICE: ' . $e->getMessage());
                $this->_logger->info('TRACE: ' . $e->getTraceAsString());
                $this->notify($e, 'WcfGestionarCliente', $options);
            }
        }
        return false;
    }

    public function creationSync(\Magento\Customer\Model\Data\Customer $customer = null)
    {
        if($customer) {

                    $surName = explode(' ', $customer->getLastname());

                    $options = [
                        'WcfGestionarCliente' => [
                            'cliente' => [
                                'correoElectronicoEnvio'    =>  $customer->getEmail(),
                                'fechaAlta' =>  date('Y-m-d\TH:i:s', time()),
                                'codigoClienteMagento'  =>  $customer->getId(),
                                'nombreCliente' =>  $customer->getFirstname(),
                                'primerApellidoCliente' =>  isset($surName[0]) ? $surName[0]:'',
                                'segundoApellidoCliente' =>  isset($surName[1])? $surName[1]:'',
                                'sexo'  =>  $customer->getGender(),
                                'cifCliente' => $customer->getTaxvat(),

                            ],
                            'origen'    =>  'Magento',
                            'accion'    =>  'Alta',
                        ]

            ];

            try {
                $xml = $this->call($this->_wsdl, $this->_helper, 'WcfGestionarCliente', $options);

                if (is_string($xml)) {
                    $p = new \Magento\Framework\Xml\Parser;
                    $p->loadXML($xml);
                    $array = $p->xmlToArray();
                    try {
                        $result = [];
                        $result['resultado'] = $array['s:Envelope']['s:Body']['WcfGestionarClienteResponse']['WcfGestionarClienteResult']['a:resultado'];
                        $result['resultadoDescripcion'] = $array['s:Envelope']['s:Body']['WcfGestionarClienteResponse']['WcfGestionarClienteResult']['a:resultadoDescripcion'];
                        if($result['resultado'] == 0) {
                            $result['resultadoDatosCliente'] = $array['s:Envelope']['s:Body']['WcfGestionarClienteResponse']['WcfGestionarClienteResult']['a:clientes']['a:Cliente'];
                        }
                        return $result;
                    } catch (\Exception $e) {
                        throwException($e);
                    }
                }

            } catch (\Exception $e) {
                $this->_logger->info('ERROR API REQUEST SERVICE: ' . $e->getMessage());
                $this->_logger->info('TRACE: ' . $e->getTraceAsString());
                $this->notify($e, 'WcfGestionarCliente', $options);
            }
        }

        return false;
    }

    public function modifySync(\Magento\Customer\Api\Data\CustomerInterface $customer = null)
    {

        if($customer) {

            if($customerBillingAddress = $customer->getDefaultBilling()) {
                $billingAddress = $this->_addressFactory->create()->load($customerBillingAddress);
                if($billingAddress->getCustomAttribute('floor')) {
                    $billingAddressFloor = $billingAddress->getCustomAttribute('floor')->getValue();
                } else {
                    $billingAddressFloor = null;
                }
                if($billingAddress->getCustomAttribute('door')) {
                    $billingAddressDoor = $billingAddress->getCustomAttribute('door')->getValue();
                } else {
                    $billingAddressDoor = null;
                }
                if($billingAddress->getCustomAttribute('stairs')){
                    $billingAddressStairs = $billingAddress->getCustomAttribute('stairs')->getValue();
                } else {
                    $billingAddressStairs = null;
                }
                if($billingAddress->getCustomAttribute('telephone_mobile')){
                    $billingAddressMobile = $billingAddress->getCustomAttribute('telephone_mobile')->getValue();
                } else {
                    $billingAddressMobile = null;
                }
                if($billingAddress->getCustomAttribute('address_email')){
                    $billingAddressEmail = $billingAddress->getCustomAttribute('address_email')->getValue();
                } else {
                    $billingAddressEmail = null;
                }
                $hasDefaultBillingAddress = true;
            } else {
                $hasDefaultBillingAddress = false;
            }

            if($customerShippingAddress = $customer->getDefaultShipping()) {
                $shippingAddress = $this->_addressFactory->create()->load($customerShippingAddress);
                if($shippingAddress->getCustomAttribute('floor')) {
                    $shippingAddressFloor = $shippingAddress->getCustomAttribute('floor')->getValue();
                } else {
                    $shippingAddressFloor = null;
                }
                if($shippingAddress->getCustomAttribute('door')) {
                    $shippingAddressDoor = $shippingAddress->getCustomAttribute('door')->getValue();
                } else {
                    $shippingAddressDoor = null;
                }
                if($shippingAddress->getCustomAttribute('stairs')){
                    $shippingAddressStairs = $shippingAddress->getCustomAttribute('stairs')->getValue();
                } else {
                    $shippingAddressStairs = null;
                }
                if($shippingAddress->getCustomAttribute('telephone_mobile')){
                    $shippingAddressMobile = $shippingAddress->getCustomAttribute('telephone_mobile')->getValue();
                } else {
                    $shippingAddressMobile = null;
                }
                if($shippingAddress->getCustomAttribute('address_email')){
                    $shippingAddressEmail = $shippingAddress->getCustomAttribute('address_email')->getValue();
                } else {
                    $shippingAddressEmail = null;
                }
                $hasDefaultShippingAddress = true;
            } else {
                $hasDefaultShippingAddress = false;
            }

            $customerLastName = explode(' ', $customer->getLastname());

            $primerApellidoCliente = isset($customerLastName[0]) ? $customerLastName[0] : '';
            $segundoApellidoCliente = isset($customerLastName[1]) ? $customerLastName[1] : '';
            $publicidad =  $customer->getCustomAttribute('wants_publicity') ? $customer->getCustomAttribute('wants_publicity')->getValue() : '';
            $idiomaDeComunicacion =  $customer->getCustomAttribute('comm_lang') ? $customer->getCustomAttribute('comm_lang')->getValue() : '';
            $esEmpleado =  $customer->getCustomAttribute('is_employee') ? $customer->getCustomAttribute('is_employee')->getValue() : '';
            $cifCliente =  $customer->getTaxvat() ? $customer->getTaxvat() : '';
            $ultimaCompra =  $customer->getCustomAttribute('id_last_purchase') ? $customer->getCustomAttribute('id_last_purchase')->getValue() : '';
            $fechaInicioPuntos =  $customer->getCustomAttribute('fecha_inicio_puntos') ? $customer->getCustomAttribute('fecha_inicio_puntos')->getValue() : '';
            $fechaFinPuntos =  $customer->getCustomAttribute('fecha_fin_puntos') ? $customer->getCustomAttribute('fecha_fin_puntos')->getValue() : '';
            $fechaBajaCRM =  $customer->getCustomAttribute('fecha_baja_crm') ? $customer->getCustomAttribute('fecha_baja_crm')->getValue() : '';
            $fechaAltaCRM =  $customer->getCustomAttribute('fecha_alta_crm') ? $customer->getCustomAttribute('fecha_alta_crm')->getValue() : '';
            $id_crm =  $customer->getCustomAttribute('id_crm') ? $customer->getCustomAttribute('id_crm')->getValue() : '';
            $codigoClienteErp =  $customer->getCustomAttribute('id_erp') ? $customer->getCustomAttribute('id_erp')->getValue() : '';
            $codigoTarjeta =  $customer->getCustomAttribute('card_code') ? $customer->getCustomAttribute('card_code')->getValue() : '';
            $fechaRGPD =  $customer->getCustomAttribute('creation') ? $customer->getCustomAttribute('creation')->getValue() : '';

            $calleFacturacion = $customer->getCustomAttribute('billing_street') ? $customer->getCustomAttribute('billing_street')->getValue() : ($hasDefaultBillingAddress) ? $billingAddress->getStreetFull() : '';
            $pisoFacturacion =  $customer->getCustomAttribute('billing_floor') ? $customer->getCustomAttribute('billing_floor')->getValue() : ($hasDefaultBillingAddress) ? $billingAddressFloor : '';
            $puertaFacturacion =  $customer->getCustomAttribute('billing_door') ? $customer->getCustomAttribute('billing_door')->getValue() : ($hasDefaultBillingAddress) ? $billingAddressDoor : '';
            $escaleraFacturacion =  $customer->getCustomAttribute('billing_stairs') ? $customer->getCustomAttribute('billing_stairs')->getValue() : ($hasDefaultBillingAddress) ? $billingAddressStairs : '';
            $codigoPostalFacturacion =  $customer->getCustomAttribute('billing_postcode') ? $customer->getCustomAttribute('billing_postcode')->getValue() : ($hasDefaultBillingAddress) ? $billingAddress->getPostcode() : '';
            $ciudadFacturacion =  $customer->getCustomAttribute('billing_city') ? $customer->getCustomAttribute('billing_city')->getValue() : ($hasDefaultBillingAddress) ? $billingAddress->getCity() : '';
            $telefonoFacturacion =  $customer->getCustomAttribute('billing_telephone') ? $customer->getCustomAttribute('billing_telephone')->getValue() : ($hasDefaultBillingAddress) ? $billingAddress->getTelephone() :'';
            $movilFacturacion =  $customer->getCustomAttribute('billing_telephone_mobile') ? $customer->getCustomAttribute('billing_telephone_mobile')->getValue() : ($hasDefaultBillingAddress) ? $billingAddressMobile : '';
            $correoElectronicoFacturacion =  $customer->getCustomAttribute('billing_address_email') ? $customer->getCustomAttribute('billing_address_email')->getValue() : ($hasDefaultBillingAddress) ? $billingAddressEmail : $customer->getEmail();

            $calleEnvio =  $customer->getCustomAttribute('shipping_street') ? $customer->getCustomAttribute('shipping_street')->getValue() : ($hasDefaultShippingAddress) ? $shippingAddress->getStreetFull() : '';
            $puertaEnvio =  $customer->getCustomAttribute('shipping_door') ? $customer->getCustomAttribute('shipping_door')->getValue() : ($hasDefaultShippingAddress) ? $shippingAddressDoor : '';
            $pisoEnvio =  $customer->getCustomAttribute('shipping_floor') ? $customer->getCustomAttribute('shipping_floor')->getValue() : ($hasDefaultShippingAddress) ? $shippingAddressFloor : '';
            $escaleraEnvio =  $customer->getCustomAttribute('shipping_stairs') ? $customer->getCustomAttribute('shipping_stairs')->getValue() : ($hasDefaultShippingAddress) ? $shippingAddressStairs : '';
            $codigoPostalEnvio =  $customer->getCustomAttribute('shipping_postcode') ? $customer->getCustomAttribute('shipping_postcode')->getValue() : ($hasDefaultShippingAddress) ? $shippingAddress->getPostcode() : '';
            $ciudadEnvio =  $customer->getCustomAttribute('shipping_city') ? $customer->getCustomAttribute('shipping_city')->getValue() : ($hasDefaultShippingAddress) ? $shippingAddress->getCity() : '';
            $telefonoEnvio =  $customer->getCustomAttribute('shipping_telephone') ? $customer->getCustomAttribute('shipping_telephone')->getValue() : ($hasDefaultShippingAddress) ? $shippingAddress->getTelephone() : '';
            $movilEnvio =  $customer->getCustomAttribute('shipping_telephone_mobile') ? $customer->getCustomAttribute('shipping_telephone_mobile')->getValue() : ($hasDefaultShippingAddress) ? $shippingAddressMobile: '';
            $correoElectronicoEnvio =  $customer->getCustomAttribute('shipping_address_email') ? $customer->getCustomAttribute('shipping_address_email')->getValue() : ($hasDefaultShippingAddress) ? $shippingAddressEmail : $customer->getEmail();



            $options = [
                'WcfGestionarCliente' => [
                    'cliente' => [
                        'codigoClienteMagento'  =>  $customer->getId(),
                        'codigoClienteCRM'  => $id_crm,
                        'nombreCliente' =>  $customer->getFirstname(),
                        'sexo'  =>  $customer->getGender(),
                        'codigoClienteErp'  => $codigoClienteErp,
                        'codigoTarjeta' =>      $codigoTarjeta,
                        'fechaRGPD'     =>  $fechaRGPD,


                        'primerApellidoCliente' =>  $primerApellidoCliente,
                        'segundoApellidoCliente' =>  $segundoApellidoCliente,
                        'publicidad'  =>  $publicidad,
                        'idiomaDeComunicacion'  =>  $idiomaDeComunicacion,
                        'esEmpleado'  =>  $esEmpleado,
                        'cifCliente'  =>  $cifCliente,
                        'ultimaCompra'  =>  $ultimaCompra,
                        'fechaInicioPuntos'  =>  $fechaInicioPuntos,
                        'fechaFinPuntos'  =>  $fechaFinPuntos,
                        'fechaBajaCRM'  =>  $fechaBajaCRM,
                        'fechaAltaCRM'  =>  $fechaAltaCRM,


                        'calleFacturacion' =>  $calleFacturacion,
                        'pisoFacturacion' =>  $pisoFacturacion,
                        'puertaFacturacion' => $puertaFacturacion,
                        'escaleraFacturacion' =>  $escaleraFacturacion,
                        'codigoPostalFacturacion' =>  $codigoPostalFacturacion,
                        'ciudadFacturacion' =>  $ciudadFacturacion,
                        'telefonoFacturacion' =>  $telefonoFacturacion,
                        'movilFacturacion' =>  $movilFacturacion,
                        'correoElectronicoFacturacion' =>  $correoElectronicoFacturacion,

                        'calleEnvio' =>  $calleEnvio,
                        'pisoEnvio' =>  $pisoEnvio,
                        'puertaEnvio' => $puertaEnvio,
                        'escaleraEnvio' =>  $escaleraEnvio,
                        'codigoPostalEnvio' =>  $codigoPostalEnvio,
                        'ciudadEnvio' =>  $ciudadEnvio,
                        'telefonoEnvio' =>  $telefonoEnvio,
                        'movilEnvio' =>  $movilEnvio,
                        'correoElectronicoEnvio' =>  $correoElectronicoEnvio,

                    ],
                    'origen'    =>  'Magento',
                    'accion'    =>  'Modificacion',
                ]



            ];

            try {
                $xml = $this->call($this->_wsdl, $this->_helper, 'WcfGestionarCliente', $options);
                if (is_string($xml)) {
                    $p = new \Magento\Framework\Xml\Parser;
                    $p->loadXML($xml);
                    $array = $p->xmlToArray();
                    try {
                        $result = [];
                        $result['resultado'] = $array['s:Envelope']['s:Body']['WcfGestionarClienteResponse']['WcfGestionarClienteResult']['a:resultado'];
                        $result['resultadoDescripcion'] = $array['s:Envelope']['s:Body']['WcfGestionarClienteResponse']['WcfGestionarClienteResult']['a:resultadoDescripcion'];
                        if($result['resultado'] == 0) {
                            $result['resultadoDatosCliente'] = $array['s:Envelope']['s:Body']['WcfGestionarClienteResponse']['WcfGestionarClienteResult']['a:clientes']['a:Cliente'];
                        }
                        return $result;
                    } catch (\Exception $e) {
                        throwException($e);
                    }
                }

            } catch (\Exception $e) {
                $this->_logger->info('ERROR API REQUEST SERVICE: ' . $e->getMessage());
                $this->_logger->info('TRACE: ' . $e->getTraceAsString());
                $this->notify($e, 'WcfGestionarCliente', $options);
            }
        }
    }


    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return array
     */

    public function deletionSync(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        if($customer) {
            $options = [
                'WcfGestionarCliente' => [
                    'cliente' => [
                        'correoElectronicoEnvio'    =>  $customer->getEmail(),
                        'codigoClienteMagento'  =>  $customer->getId(),
                        'codigoClienteCRM' =>  $customer->getData('id_crm'),
                        'fechaBaja' =>  date('Y-m-d\TH:i:s', time()),
                    ],
                    'origen'    =>  'Magento',
                    'accion'    =>  'Baja',
                ]
            ];

            try {
                $xml = $this->call($this->_wsdl, $this->_helper, 'WcfGestionarCliente', $options);
                if (is_string($xml)) {
                    $p = new \Magento\Framework\Xml\Parser;
                    $p->loadXML($xml);
                    $array = $p->xmlToArray();
                    try {
                        $result = [];
                        $result['resultado'] = $array['s:Envelope']['s:Body']['WcfGestionarClienteResponse']['WcfGestionarClienteResult']['a:resultado'];
                        $result['resultadoDescripcion'] = $array['s:Envelope']['s:Body']['WcfGestionarClienteResponse']['WcfGestionarClienteResult']['a:resultadoDescripcion'];
                        return $result;
                    } catch (\Exception $e) {
                        throwException($e);
                    }
                }
            } catch (\Exception $e) {
                $this->_logger->info('ERROR API REQUEST SERVICE: ' . $e->getMessage());
                $this->_logger->info('TRACE: ' . $e->getTraceAsString());
                $this->notify($e, 'WcfGestionarCliente', $options);
                $path = $_SERVER['DOCUMENT_ROOT'];
                error_log($e->getMessage().PHP_EOL, 3, $path.self::USER_ERROR_DIR);
            }
        }
    }

    /**
     * @param array $options
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function retry($options = []){
        if(is_array($options)) {
            $xml = $this->call($this->_wsdl, $this->_helper, 'WcfGestionarCliente', $options);
            if (is_string($xml)) {
                $p = new \Magento\Framework\Xml\Parser;
                $p->loadXML($xml);
                $array = $p->xmlToArray();
                try {
                    $result = [];
                    $result['resultado'] = $array['s:Envelope']['s:Body']['WcfGestionarClienteResponse']['WcfGestionarClienteResult']['a:resultado'];
                    $result['resultadoDescripcion'] = $array['s:Envelope']['s:Body']['WcfGestionarClienteResponse']['WcfGestionarClienteResult']['a:resultadoDescripcion'];
                    return $result;
                } catch (\Exception $e) {
                    $this->notify($e, 'WcfGestionarCliente', $options, false);
                    throwException($e);
                }
            }
        }
    }

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param array $attributes
     * @return array
     */
    private function convertCustomerToWSArray(\Magento\Customer\Api\Data\CustomerInterface $customer, $attributes = self::ATTRIBUTES_WEBSERVICE_MAGENTO) {
        $response = [];
        foreach ($attributes as $paramWS => $customAttribute) {
            $customerCustomAttribute = $customer->getCustomAttribute($customAttribute);
            $customCustomAttributeValue = '';
            if($customerCustomAttribute) {
                $customCustomAttributeValue = $customerCustomAttribute->getValue();
            }
            $response[$paramWS] = $customCustomAttributeValue;
        }
        return $response;
    }

    private function notify(\Exception $e, $method,  $options = [], $useQueue = true) {
        if($this->_helper->getUseQueue() && $useQueue) {
            //HERE WE INSERT THE RECORD INSIDE THE QUEUE
            $queueItem = $this->_queueFactory->create();
            $queueItem->addData([
                'pss_crm_queue_id' => null,
                'process_name' => $options[$method]['accion'],
                'model' => get_class($this),
                'method' => $method,
                'data' => json_encode($options),
                'result' => $e->getMessage(),
                'process_status' => 2,
                'process_message' => ($this->_soapClient->getLastRequest())?$this->_soapClient->getLastRequest():$e->getMessage()
            ]);
            try {
                $queueItem->save();
            } catch (\Exception $exception) {
                //
            }
        }
        if($this->_helper->getDebugEmail()!== null) {
            foreach (explode(',',$this->_helper->getDebugEmail()) as $email) {
                try {
                    $transport = $this->_transportBuilder->setTemplateIdentifier('crm_error_template')
                        ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_ADMINHTML, 'store' => '0'])
                        ->setTemplateVars(
                            [
                                'method' => $method,
                                'error_message' => $e->getMessage(),
                                'trace_message' => $e->getTraceAsString()
                            ]
                        )
                        ->setFrom('general')
                        ->addTo($email, 'CRM WS Error Receipt')
                        ->getTransport();
                    $transport->sendMessage();
                } catch (\Exception $e) {
                    //TODO
                }
            }
        }

    }

}