<?php
/**
 *  @author Xavier Sanz <xsanz@pss.com>
 *  @copyright Copyright (c) 2017 PSS (http://www.pss.com)
 *  @package PSS
 */

namespace PSS\CRM\Model\Api;

use Braintree\Exception;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Exception\LocalizedException;
use PSS\CRM\Model\Api\AbstractModel;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;

/**
 * Class UserService
 * @package PSS\CRM\Model\Api
 */

class TicketService extends AbstractModel
{

    /**
     * @var \PSS\CRM\Helper\TicketService
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
     * @var \PSS\Customer\Model\Customer
     */

    protected $_customer;

    public $_transportBuilder;

    public $_soapClient;
    public $_componentRegistrar;

    /**
     * TicketService constructor.
     * @param \PSS\CRM\Logger\Logger $_logger
     * @param \Magento\Framework\Mail\Template\TransportBuilder $_transportBuilder
     * @param \Zend\Soap\Client $_soapClient
     * @param ComponentRegistrarInterface $_componentRegistrar
     * @param \Magento\Customer\Model\Customer $_customer
     * @param \PSS\CRM\Helper\TicketService $_helper
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
        \PSS\CRM\Helper\TicketService $_helper,
        $wsdl
    )
    {

        $this->_logger = $_logger;
        $this->_transportBuilder = $_transportBuilder;
        $this->soapClient = $_soapClient;
        $this->componentRegistrar = $_componentRegistrar;
        $this->_customer = $_customer;
        $this->_helper = $_helper;
        $this->_wsdl = $wsdl;

        parent::__construct($_logger, $_soapClient, $_queueRepositoryInterface, $_queueFactory, $_componentRegistrar, $_transportBuilder);
    }

    public function query($quote = null)
    {

        if($quote) {

            $options = [
                'WcfGestionarTicket' => [
                    'ticketp' => [
                        'IdTicket'    =>  $quote->getReservedOrderId(),

                    ],
                    'origen'    =>  'Magento',
                    'accion'    =>  'Recuperacion',
                ]
            ];

            try {
                $xml= $this->call($this->_wsdl, $this->_helper, 'WcfGestionarTicket', $options);

                if (is_string($xml)) {
                    $p = new \Magento\Framework\Xml\Parser;
                    $p->loadXML($xml);
                    $array = $p->xmlToArray();
                    try {
                        return $array['s:Envelope']['s:Body']['WcfGestionarTicketResponse']['WcfGestionarTicketResult']['a:resultado'];
                    } catch(\Exception $e) {
                        throwException($e);
                    }
                }
                return false;

            } catch (\Exception $e) {
                $this->_logger->info('ERROR API REQUEST SERVICE: ' . $e->getMessage());
                $this->_logger->info('TRACE: ' . $e->getTraceAsString());
                $this->notify($e, 'WcfGestionarTicket', $options);

            }
        }
    }


    public function creationSync($order = null)
    {
        if($order) {

            $items = $order->getItems();
            $customer = $order->getCustomer();
            if ($items != null && null != $customer->getId()) {

                $points_earned = $order->getData('mp_reward_earn');
                $points_spent = $order->getData('mp_reward_spent');
                $tax = $order->getShippingAddress()->getData('tax_amount');

                $CART_PRODUCTS = [];
                foreach ($items as $item) {
                    $prod['IdTicket'] = $item->getData('entity_id');
                    $prod['cantidad'] = $item->getQty();
                    $prod['codigoArticulo'] = $item->getData('sku');
                    $prod['importeIVA'] = $item->getData('tax_amount');
                    $prod['importeNeto'] = $item->getData('base_row_total');
                    $prod['porcentajeIVA'] = $item->getData('tax_percent');
                    $prod['porcent'] = $item->getData('applied_rule_ids');

                    $CART_PRODUCTS[] = $prod;
                }


                $options = [
                    'WcfGestionarTicket' => [
                        'ticketp' => [
                            'codigoEmpleado' => $customer->getCustomAttribute('is_employee') ? $customer->getCustomAttribute('is_employee')->getValue() : '',
                            'codigoclienteCRM' => $customer->getCustomAttribute('id_crm') ? $customer->getCustomAttribute('id_crm')->getValue() : '',
                            'delegacion' => 'TV',
                            'formaPago1' => 'VISA',
                            'formaPago2' => '',
                            'fecha' => date('Y-m-d\TH:i:s', time()),
                            'hora' => date('Y-m-d\TH:i:s', time()),

                            'importeIVA' => $tax,
                            'importeNeto' => $order->getData('subtotal'),
                            'puntosGenerados' => $points_earned,
                            'puntosUtilizados' => $points_spent,
                            'tipoTicket' => 'Venta',

                            'productoticket' => [
                                'ProductoTicket' => $CART_PRODUCTS,
                            ],

                        ],
                        'origen' => 'Magento',
                        'accion' => 'Alta',
                    ]


                ];


                try {
                    $xml = $this->call($this->_wsdl, $this->_helper, 'WcfGestionarTicket', $options);
                    if (is_string($xml)) {
                        $p = new \Magento\Framework\Xml\Parser;
                        $p->loadXML($xml);
                        $array = $p->xmlToArray();
                        try {
                            return $array['s:Envelope']['s:Body']['WcfGestionarTicketResponse']['WcfGestionarTicketResult']['a:resultado'];
                        } catch (\Exception $e) {
                            throwException($e);
                        }
                    }
                    return false;


                } catch (\Exception $e) {
                    $this->_logger->info('ERROR API REQUEST SERVICE: ' . $e->getMessage());
                    $this->_logger->info('TRACE: ' . $e->getTraceAsString());
                    $this->notify($e, 'WcfGestionarTicket', $options);

                }
            }
            else return false;
        }
    }

    public function modifySync($quote = null)
    {

        if($quote) {
            $CART_PRODUCTS = [];
            $options = [
                'WcfGestionarTicket' => [
                    'ticketp' => [
                        'IdTicket'  =>  $quote->IdTicket,
                        'codigoEmpleado'  => $quote->codigoEmpleado,
                        'codigoclienteCRM' =>  $quote->codigoclienteCRM,
                        'delegacion'  =>  $quote->delegacion,
                        'estadoTicket'  => $quote->estadoTicket,
                        'fecha' =>      $quote->fecha,
                        'formaEnvio'     =>  $quote->formaEnvio,

                        'hora' =>  $quote->hora,

                        'importeIVA'  =>  $quote->importeIVA,
                        'importeNeto'  =>  $quote->importeNeto,
                        'puntosGenerados'  =>  $quote->puntosGenerados,
                        'puntosUtilizados'  =>  $quote->puntosUtilizados,
                        'tipoTicket'  =>  $quote->tipoTicket,

                        'productoticket'    =>  [
                            'ProductoTicket'    =>  array_map(function($item){
                                return [
                                    'idTicket' => $item->idTicket,
                                    'cantidad' => $item->cantidad,
                                    'codigoArticulo' => $item->codigoArticulo,
                                    'importeIVA' => $item->importeIVA,
                                    'importeNeto' => $item->importeNeto,
                                    'porcentajeIVA' => $item->porcentajeIVA,
                                ];
                            }, $CART_PRODUCTS),
                        ],

                    ],
                    'origen'    =>  'ERP',
                    'accion'    =>  'Modificacion',
                ]



            ];


            try {
                $xml = $this->call($this->_wsdl, $this->_helper, 'WcfGestionarTicket', $options);
                if (is_string($xml)) {
                    $p = new \Magento\Framework\Xml\Parser;
                    $p->loadXML($xml);
                    $array = $p->xmlToArray();
                    try {
                        return $array['s:Envelope']['s:Body']['WcfGestionarTicketResponse']['WcfGestionarTicketResult']['a:resultado'];
                    } catch(\Exception $e) {
                        throwException($e);
                    }
                }
                return false;
            } catch (\Exception $e) {
                $this->_logger->info('ERROR API REQUEST SERVICE: ' . $e->getMessage());
                $this->_logger->info('TRACE: ' . $e->getTraceAsString());
                $this->notify($e, 'WcfGestionarTicket', $options);

            }
        }
    }


    public function deletionSync($customer)
    {
        if($customer) {
            $options = [
                'WcfGestionarTicket' => [
                    'ticketp' => [
                        'IdTicket'    =>  $customer->getEmail(),
                        'codigoClienteCRM' =>  $customer->getData('id_crm'),
                        'fecha' =>  date('Y-m-d\TH:i:s', time()),
                    ],
                    'origen'    =>  'Magento',
                    'accion'    =>  'Baja',
                ]
            ];

            try {
                $xml = $this->call($this->_wsdl, $this->_helper, 'WcfGestionarTicket', $options);
                if (is_string($xml)) {
                    $p = new \Magento\Framework\Xml\Parser;
                    $p->loadXML($xml);
                    $array = $p->xmlToArray();
                    try {
                        return $array['s:Envelope']['s:Body']['WcfGestionarTicketResponse']['WcfGestionarTicketResult']['a:resultado'];
                    } catch(\Exception $e) {
                        throwException($e);
                    }
                }
                return false;
            } catch (\Exception $e) {
                $this->_logger->info('ERROR API REQUEST SERVICE: ' . $e->getMessage());
                $this->_logger->info('TRACE: ' . $e->getTraceAsString());
                $this->notify($e, 'WcfGestionarTicket', $options);

            }
        }
    }

    /**
     * @param $quote
     * @return null|string
     */
    public function calculatePoints($quote) {
        $options = [
            'WcfGestionarTicket' => [
                'ticketp' => [
                    'productoticket'    => $this->generateProductTicket($quote),
                    'puntosGenerados' => '' ,
                    'puntosUtilizados' => ''
                ],
                'origen'    =>  'Magento',
                'accion'    =>  'CalcularPuntosporCesta',
            ]
        ];
        try {
            $xml = $this->call($this->_wsdl, $this->_helper, 'WcfGestionarTicket', $options);
            if (is_string($xml)) {
                $p = new \Magento\Framework\Xml\Parser;
                try {
                    $p->loadXML($xml);
                    $array = $p->xmlToArray();
                    return $array['s:Envelope']['s:Body']['WcfGestionarTicketResponse'];
                } catch (\Exception $e) {
                    throwException($e);
                }
            }
        } catch (\Exception $e) {
            $this->_logger->info('ERROR API REQUEST SERVICE: ' . $e->getMessage());
            $this->_logger->info('TRACE: ' . $e->getTraceAsString());
            $this->notify($e, 'WcfGestionarTicket', $options);

        }
        return false;
    }

    /**
     * @param array $options
     * @return array
     * @throws LocalizedException
     */
    public function retry($options = []){
        if(is_array($options)) {
            $xml = $this->call($this->_wsdl, $this->_helper, 'WcfGestionarTicket', $options);
            if (is_string($xml)) {
                $p = new \Magento\Framework\Xml\Parser;
                $p->loadXML($xml);
                $array = $p->xmlToArray();
                try {
                    $result = [];
                    $result['resultado'] = $array['s:Envelope']['s:Body']['WcfGestionarTicketResponse']['WcfGestionarTicketResult']['a:resultado'];
                    $result['resultadoDescripcion'] = $array['s:Envelope']['s:Body']['WcfGestionarTicketResponse']['WcfGestionarTicketResult']['a:resultadoDescripcion'];
                    return $result;
                } catch (\Exception $e) {
                    $this->notify($e, 'WcfGestionarTicket', $options, false);
                    throwException($e);
                }
            }
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return array
     */
    private function generateProductTicket($quote) {
        $request = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            $request[] = [
                'idTicket' => '',
                'cantidad' => $item->getQty(),
                'codigoArticulo' => $item->getSku(),
                'importeIVA' => $item->getBaseTaxAmount(),
                'importeNeto' => $item->getBaseTaxAmount(),
                'porcentajeIVA' => $item->getTaxPercent(),
                'promocionApplicada' => ''
            ];
        }
        return $request;
    }

    /**
     * @param \Exception $e
     * @param $method
     * @param array $options
     * @param bool $useQueue
     */
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