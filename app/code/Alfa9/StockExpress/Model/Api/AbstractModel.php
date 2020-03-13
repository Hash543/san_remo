<?php
/**
 *  @author Xavier Sanz <xsanz@alfa9.com>
 *  @copyright Copyright (c) 2017 Alfa9 (http://www.alfa9.com)
 *  @package Alfa9
 */

namespace Alfa9\StockExpress\Model\Api;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Exception\MailException;

class AbstractModel
{

    /**
     * @var \Zend\Soap\Client
     */
    public $soapClient;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrarInterface
     */
    public $componentRegistrar;

    /**
     * @var \Alfa9\StockExpress\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */

    public $transportBuilder;


    public function __construct(
        \Alfa9\StockExpress\Logger\Logger $logger,
        \Zend\Soap\Client $soapClient,
        \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    ) {
        $this->logger = $logger;
        $this->soapClient = $soapClient;
        $this->componentRegistrar = $componentRegistrar;
        $this->transportBuilder = $transportBuilder;
    }

    /**
     * @param $wsdl
     * @param \Alfa9\StockExpress\Helper\Data $helper
     * @param $method
     * @param array $options
     * @return string
     */
    public function call($wsdl, $helper, $method, $options = []) {
        $this->soapClient->setWSDLCache(0);
        $this->soapClient->setWSDL($this->componentRegistrar->getPath(ComponentRegistrar::MODULE,'Alfa9_StockExpress') . $wsdl);
        $this->soapClient->setSoapVersion(SOAP_1_1);

        if($helper->getAuthenticate()) {
            $this->soapClient->setHttpLogin($helper->getUser());
            $this->soapClient->setHttpPassword($helper->getPassword());
        }

        if($helper->getSSLVerify()) {
            $this->soapClient->setStreamContext(stream_context_create([
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
            $this->soapClient->setLocation($helper->getWSDLUrl());
            // Este código comentado es para pasar el CDATA en formato XML sin traducir < y >
            $soapXML = new \SoapVar($options['xml_code'], \XSD_ANYXML);
            $this->soapClient->call($method, [$options['database'], $soapXML, 'encoding' => 'UTF-8']);

            if($helper->getDebugXML()) {
                $this->logger->info('LOG XML REQUEST FOR METHOD: '.$method);
                $this->logger->info($this->soapClient->getLastRequest());
                $this->logger->info('LOG XML RESPONSE FOR METHOD: '.$method);
                $this->logger->info($this->soapClient->getLastResponse());

            }
            $response = $this->soapClient->getLastResponse();
            return $response;

        } catch (\SoapFault $e) {
            if($helper->getDebug()) {
                $this->logger->info($this->soapClient->getLastRequest());
                $this->logger->info($e->getMessage());
                $this->logger->info($e->getTraceAsString());
            }
            if($helper->getDebugEmail()!== null) {
                foreach (explode(',',$helper->getDebugEmail()) as $email) {
                    $transport = $this->transportBuilder->setTemplateIdentifier('stockexpress_error_template')
                        ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_ADMINHTML, 'store' => '0'])
                        ->setTemplateVars(
                            [
                                'method' => $method,
                                'error_message' => $e->getMessage(),
                                'trace_message' => $e->getTraceAsString()
                            ]
                        )
                        ->setFrom('general')
                        ->addTo($email, 'OSB WS Error Receipt')
                        ->getTransport();
                    try {
                        //$transport->sendMessage();
                    }catch (MailException $exception) {
                        $this->logger->info($exception->getMessage());
                    }
                }
            }
            return null;
        }
    }


}