<?php
/**
 *  @author Xavier Sanz <xsanz@pss.com>
 *  @copyright Copyright (c) 2017 PSS (http://www.pss.com)
 *  @package PSS
 */

namespace PSS\CRM\Model\Api;

use PSS\CRM\Logger\Logger;
use Magento\Framework\Component\ComponentRegistrar;

class AbstractModel
{

    /**
     * @var \Zend\Soap\Client
     */
    public $_soapClient;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrarInterface
     */
    public $_componentRegistrar;

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * @var \PSS\CRM\Api\QueueRepositoryInterface
     */
    protected $_queueRepositoryInterface;

    /**
     * @var \PSS\CRM\Model\QueueFactory
     */
    protected $_queueFactory;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */

    public $_transportBuilder;

    /**
     * AbstractModel constructor.
     * @param Logger $logger
     * @param \Zend\Soap\Client $soapClient
     * @param \PSS\CRM\Api\QueueRepositoryInterface $queueRepositoryInterface
     * @param \PSS\CRM\Model\QueueFactory $queueFactory
     * @param \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     */

    public function __construct(
        \PSS\CRM\Logger\Logger $logger,
        \Zend\Soap\Client $soapClient,
        \PSS\CRM\Api\QueueRepositoryInterface $queueRepositoryInterface,
        \PSS\CRM\Model\QueueFactory $queueFactory,
        \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    )
    {
        $this->_logger = $logger;
        $this->_soapClient = $soapClient;
        $this->_queueRepositoryInterface = $queueRepositoryInterface;
        $this->_queueFactory = $queueFactory;
        $this->_componentRegistrar = $componentRegistrar;
        $this->_transportBuilder = $transportBuilder;

    }


    public function call($wsdl, $helper, $method, $options = [])
    {

        $this->_soapClient->setWSDLCache(0);

        $this->_soapClient->setWSDL($this->_componentRegistrar->getPath(ComponentRegistrar::MODULE,'PSS_CRM') . $wsdl);

        $this->_soapClient->setSoapVersion(SOAP_1_1);

        if($helper->getAuthenticate()) {
            $this->_soapClient->setHttpLogin($helper->getUser());
            $this->_soapClient->setHttpPassword($helper->getPassword());
        }

        if(!$helper->getSSLVerify()) {
            $this->_soapClient->setStreamContext(stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
                'http' => [
                    'connection_timeout' => intval('90')
                ]
            ]));
        }

        try {

            $this->_soapClient->setLocation($helper->getWSDLUrl());

            $this->_soapClient->call($method, $options);

            if($helper->getDebugXML()) {
                $this->_logger->info('LOG XML REQUEST FOR METHOD: '.$method);
                $this->_logger->info($this->_soapClient->getLastRequest());
                $this->_logger->info('LOG XML RESPONSE FOR METHOD: '.$method);
                $this->_logger->info($this->_soapClient->getLastResponse());

            }

            $response = $this->_soapClient->getLastResponse();

            return $response;

        } catch (\SoapFault $e) {
            if($helper->getDebug()) {
                $this->_logger->info($e->getMessage());
                $this->_logger->info($e->getTraceAsString());
            }
            if($helper->getDebugXML()) {
                $this->_logger->info('LOG XML REQUEST FOR METHOD: '.$method);
                $this->_logger->info($this->_soapClient->getLastRequest());
                $this->_logger->info('LOG XML RESPONSE FOR METHOD: '.$method);
                $this->_logger->info($this->_soapClient->getLastResponse());
            }
            if($helper->getUseQueue()) {
                //HERE WE INSERT THE RECORD INSIDE THE QUEUE
                $queueItem = $this->_queueFactory->create();
                $queueItem->addData([
                    'pss_crm_queue_id' => null,
                    'process_name' => $options[$method]['accion'],
                    'model' => null,
                    'method' => $method,
                    'data' => json_encode($options),
                    'result' => $this->_soapClient->getLastResponse(),
                    'process_status' => 0,
                    'process_message' => ($this->_soapClient->getLastRequest())?$this->_soapClient->getLastRequest():$e->getMessage()
                ]);
                $queueItem->save();
            }
            if($helper->getDebugEmail()!== null) {
                foreach (explode(',',$helper->getDebugEmail()) as $email) {
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
                }
            }
        }
    }


}