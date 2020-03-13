<?php
/**
 *  @author Xavier Sanz <xsanz@pss.com>
 *  @copyright Copyright (c) 2017 PSS (http://www.pss.com)
 *  @package PSS
 */

namespace PSS\CRM\Model\Api;

use Braintree\Exception;
use Magento\Framework\Component\ComponentRegistrarInterface;
use PSS\CRM\Model\Api\AbstractModel;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;

/**
 * Class UserService
 * @package PSS\CRM\Model\Api
 */

class PromotionService extends AbstractModel
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
     * @var \PSS\Customer\Model\Customer
     */

    protected $_customer;

    public $_transportBuilder;

    public $_soapClient;
    public $_componentRegistrar;

    /**
     * UserService constructor.
     * @param \PSS\CRM\Logger\Logger $logger
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Zend\Soap\Client $soapClient
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param \PSS\CRM\Helper\PromotionService $helper
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
        \PSS\CRM\Helper\PromotionService $_helper,
        \Amasty\Feed\Api\FeedRepositoryInterface $_feedRepository,
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


    // Get Promotion Details
    public function query($ruleId)
    {

        if($ruleId) {

            $options = [
                'WcfGestionarPromocion' => [
                    'promocion' => [
                        'CodigoPromocion'    =>  $ruleId,
                        ],
                    'origen'    =>  'Magento',
                    'accion'    =>  'Recuperacion',
                ]
            ];

            try {
                $xml= $this->call($this->_wsdl, $this->_helper, 'WcfGestionarPromocion', $options);
                return $xml;
            } catch (\Exception $e) {
                $this->_logger->info('ERROR API REQUEST SERVICE: ' . $e->getMessage());
                $this->_logger->info('TRACE: ' . $e->getTraceAsString());

            }
        }
    }


    // Get marketing List
    public function getlistSync()
    {
        $options = [
                'WcfGestionarListaMarketing' => [
                    'origen'    =>  'Magento',
                    'accion'    =>  'Recuperacion',
                ]
            ];

        try {
            $xml= $this->call($this->_wsdl, $this->_helper, 'WcfGestionarListaMarketing', $options);
            return $xml;
        } catch (\Exception $e) {
            $this->_logger->info('ERROR API REQUEST SERVICE: ' . $e->getMessage());
            $this->_logger->info('TRACE: ' . $e->getTraceAsString());

        }
        return null;
    }

    public function getcouponSync($couponCode)
    {
        if($couponCode) {

            $options = [
                'WcfGestionarVale' => [
                    'vale' => [
                        'CodigoDeVale'    =>  $couponCode,
                    ],
                    'origen'    =>  'Magento',
                    'accion'    =>  'Recuperacion',
                ]
            ];

            try {
                $xml= $this->call($this->_wsdl, $this->_helper, 'WcfGestionarVale', $options);
                return $xml;
            } catch (\Exception $e) {
                $this->_logger->info('ERROR API REQUEST SERVICE: ' . $e->getMessage());
                $this->_logger->info('TRACE: ' . $e->getTraceAsString());

            }
        }

    }

   // Create Promotion Sync

    public function creationSync($rule, $action)
    {
        if($rule) {
            $id = $rule->getId();
            $toDateStr = $rule->getToDate();
            $fromDateStr = $rule->getFromDate();
            $marketing_list = $rule->getOrigData('marketing_list');
            $url_banner = $rule->getOrigData('url_banner');


            $toDate = date("d-m-Y", strtotime($toDateStr));
            $fromDate = date("d-m-Y", strtotime($fromDateStr));


                    $options = [
                        'WcfGestionarPromocion' => [
                            'promocion' => [
                                'CodigoPromocion'    =>  $rule->getId(),
                                'Descripcion' =>  $rule->getDescription(),
                                'FechaDeFin'  =>  $toDate,
                                'FechaDeInicio' =>  $fromDate,
                                'IDsListasMarketing' =>  $marketing_list,
                                'URLBanner' =>  $url_banner,

                            ],
                            'origen'    =>  'Magento',
                            'accion'    =>  $action,
                        ]



            ];

            try {
                $xml = $this->call($this->_wsdl, $this->_helper, 'WcfGestionarPromocion', $options);
                return $xml;


            } catch (\Exception $e) {
                $this->_logger->info('ERROR API REQUEST SERVICE: ' . $e->getMessage());
                $this->_logger->info('TRACE: ' . $e->getTraceAsString());

            }
        }
    }


    // Create Coupon Sync
    public function createCouponSync($rule, $action)
    {
        if($rule) {
            $crm_id = $rule->getOrigData('crm_id');
            $coupon_code = $rule->getOrigData('coupon_code');
            $status = $rule->getOrigData('is_active');
            $modification_date = date("d-m-Y", time());
            $promocionconsumo = $rule->getId();
            $ticket_origen = 'TV';
            $toDateStr = $rule->getToDate();
            $fromDateStr = $rule->getFromDate();

            $toDate = date("d-m-Y", strtotime($toDateStr));
            $fromDate = date("d-m-Y", strtotime($fromDateStr));


            $options = [
                'WcfGestionarVale' => [
                    'vale' => [
                        'CodigoCliente'    =>  $crm_id,
                        'CodigoDeVale' =>  $coupon_code,
                        'Estado'    =>  $status,
                        'FechaDeAlta'  =>  $fromDate,
                        'FechaFin' =>  $toDate,
                        'FechaModificacion' =>  $modification_date,
                        'PromocionConsumo' =>  $promocionconsumo,
                        'TicketOrigen' =>  $ticket_origen,


                    ],
                    'origen'    =>  'Magento',
                    'accion'    =>  $action,
                ]



            ];

            try {
                $xml = $this->call($this->_wsdl, $this->_helper, 'WcfGestionarVale', $options);
                return $xml;


            } catch (\Exception $e) {
                $this->_logger->info('ERROR API REQUEST SERVICE: ' . $e->getMessage());
                $this->_logger->info('TRACE: ' . $e->getTraceAsString());

            }
        }
    }


    // Modify Promotion Sync
    public function modifySync($rule)
    {

        if($rule) {
            $id = $rule->getId();
            $toDateStr = $rule->getToDate();
            $fromDateStr = $rule->getFromDate();
            $marketing_list = $rule->getOrigData('marketing_list');
            $url_banner = $rule->getOrigData('url_banner');

            $toDate = date("d-m-Y", strtotime($toDateStr));
            $fromDate = date("d-m-Y", strtotime($fromDateStr));


            $options = [
                'WcfGestionarPromocion' => [
                    'promocion' => [
                        'CodigoPromocion'    =>  $id,
                        'Descripcion' =>  $rule->getDescription(),
                        'FechaDeFin'  =>  $toDate,
                        'FechaDeInicio' =>  $fromDate,
                        'IDsListasMarketing' =>  $marketing_list,
                        'URLBanner' =>  $url_banner,

                    ],
                    'origen'    =>  'Magento',
                    'accion'    =>  'Modificacion',
                ]



            ];

            try {
                $xml = $this->call($this->_wsdl, $this->_helper, 'WcfGestionarPromocion', $options);
                return $xml;


            } catch (\Exception $e) {
                $this->_logger->info('ERROR API REQUEST SERVICE: ' . $e->getMessage());
                $this->_logger->info('TRACE: ' . $e->getTraceAsString());

            }
        }
    }


    // Modify Coupon Sync
    public function modifyCouponSync($rule)
    {
        if($rule) {
            $crm_id = $rule->getOrigData('crm_id');
            $coupon_code = $rule->getOrigData('coupon_code');
            $status = $rule->getOrigData('is_active');
            $modification_date = date("d-m-Y", time());
            $promocionconsumo = $rule->getId();
            $ticket_origen = 'TV';
            $toDateStr = $rule->getToDate();
            $fromDateStr = $rule->getFromDate();

            $toDate = date("d-m-Y", strtotime($toDateStr));
            $fromDate = date("d-m-Y", strtotime($fromDateStr));


            $options = [
                'WcfGestionarVale' => [
                    'vale' => [
                        'CodigoCliente'    =>  $crm_id,
                        'CodigoDeVale' =>  $coupon_code,
                        'Estado'    =>  $status,
                        'FechaDeAlta'  =>  $fromDateStr,
                        'FechaFin' =>  $toDateStr,
                        'FechaModificacion' =>  $modification_date,
                        'PromocionConsumo' =>  $promocionconsumo,
                        'TicketOrigen' =>  $ticket_origen,


                    ],
                    'origen'    =>  'Magento',
                    'accion'    =>  'Modificacion',
                ]



            ];

            try {
                $xml = $this->call($this->_wsdl, $this->_helper, 'WcfGestionarVale', $options);
                return $xml;


            } catch (\Exception $e) {
                $this->_logger->info('ERROR API REQUEST SERVICE: ' . $e->getMessage());
                $this->_logger->info('TRACE: ' . $e->getTraceAsString());

            }
        }
    }

// Delete Promotion Code
    public function deletionSync($rule)
{
    if($rule) {
        $id = $rule->getId();
        $toDateStr = $rule->getToDate();
        $fromDateStr = $rule->getFromDate();

        $toDate = date("d-m-Y", strtotime($toDateStr));
        $fromDate = date("d-m-Y", strtotime($fromDateStr));


        $options = [
            'WcfGestionarPromocion' => [
                'promocion' => [
                    'CodigoPromocion'    =>  $rule->getId(),
                    'Descripcion' =>  $rule->getDescription(),
                    'FechaDeFin'  =>  $toDate,
                    'FechaDeInicio' =>  $fromDate,

                ],
                'origen'    =>  'Magento',
                'accion'    =>  'Baja',
            ]



        ];

        try {
            $result = $this->call($this->_wsdl, $this->_helper, 'WcfGestionarPromocion', $options);
            return $result;
        } catch (\Exception $e) {
            $this->_logger->info('ERROR API REQUEST SERVICE: ' . $e->getMessage());
            $this->_logger->info('TRACE: ' . $e->getTraceAsString());

        }
    }
}


// Delete Coupon code/ Vale
    public function deleteCouponSync($rule)
    {
        if($rule) {

            $options = [
                'WcfGestionarVale' => [
                    'promocion' => [
                        'CodigoPromocion'    =>  $rule->getOrigData('coupon_code'),

                    ],
                    'origen'    =>  'Magento',
                    'accion'    =>  'Baja',
                ]

            ];

            try {
                $result = $this->call($this->_wsdl, $this->_helper, 'WcfGestionarVale', $options);
                return $result;
            } catch (\Exception $e) {
                $this->_logger->info('ERROR API REQUEST SERVICE: ' . $e->getMessage());
                $this->_logger->info('TRACE: ' . $e->getTraceAsString());

            }
        }
    }

}