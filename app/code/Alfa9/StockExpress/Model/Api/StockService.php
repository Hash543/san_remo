<?php
/**
 *  @author Xavier Sanz <xsanz@alfa9.com>
 *  @copyright Copyright (c) 2017 Alfa9 (http://www.alfa9.com)
 *  @package Alfa9
 */

namespace Alfa9\StockExpress\Model\Api;

use Braintree\Exception;
use Magento\Framework\Component\ComponentRegistrar;
use Alfa9\StockExpress\Model\Api\AbstractModel;

/**
 * Class ContactService
 * @package Alfa9\StockExpress\Model\Api
 */

class StockService extends AbstractModel
{

    /**
     * @var \Alfa9\StockExpress\Helper\StockService
     */
    protected $helper;

    /**
     * @var
     */
    protected $wsdl;

    /**
     * StockService constructor.
     * @param \Alfa9\StockExpress\Logger\Logger $logger
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Alfa9\StockExpress\Helper\StockService $helper
     * @param \Zend\Soap\Client $soapClient
     * @param \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar
     * @param $wsdl
     */

    public function __construct(
        \Alfa9\StockExpress\Logger\Logger $logger,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Alfa9\StockExpress\Helper\StockService $helper,
        \Zend\Soap\Client $soapClient,
        \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar,
        $wsdl
    ) {
        $this->helper = $helper;
        $this->wsdl = $wsdl;
        parent::__construct($logger, $soapClient, $componentRegistrar, $transportBuilder);
    }

    /**
     * @param string $sku
     * @param null|int  $quantity
     * @return null|string
     */
    public function queryStock($sku, $quantity)
    {

        if($sku && $quantity) {
            $options = [
               'database' => $this->helper->getDatabase(),
               'xml_code' => $this->getXmlCode($sku, $quantity),
            ];
            $result = $this->call($this->wsdl, $this->helper, 'executeXML', $options);
            return $result;
        }
        return null;
    }

    /**
     * @param null $products
     * @return null|string
     */
    public function queryStockMulti($products = null) {

        if(is_array($products)) {

            $options = [
                'database' => $this->helper->getDatabase(),
                'xml_code' => $this->getXmlCodeMulti($products),
            ];
            $result = $this->call($this->wsdl, $this->helper, 'executeXML', $options);
            return $result;
        }
        return null;

    }

    /**
     * @param string $sku
     * @param $quantity
     * @return string
     */
    protected function getXmlCode($sku, $quantity) {
        return "<xml_code xsi:type=\"xsd:string\">
            <![CDATA[
			<call name='psr_ws_consulta_stock'>
				<args>
					<arg>
						>sanremoxml>
							<articulos>
							   <articulo>
						            <codigo>$sku</codigo>
						            <cantidad>$quantity</cantidad>
						        </articulo>
						    </articulos>
						</sanremoxml>
					</arg>
			</args>
			</call> 
		]]>
		</xml_code>";
    }

    /**
     * @param $products
     * @return string
     */
    protected function getXmlCodeMulti($products) {

        $returnXml = '';
        foreach ($products as $product) {
            //$productAux = $product;
            $returnXml .= '<articulo><codigo>'.$product['sku'].'</codigo><cantidad>'.$product['qty'].'</cantidad></articulo>';
        }
        $xml = "<xml_code xsi:type=\"xsd:string\">
            <![CDATA[
			<call name='psr_ws_consulta_stock'>
				<args>
					<arg>
						>sanremoxml>
							<articulos>
							$returnXml
						    </articulos>
						</sanremoxml>
					</arg>
			</args>
			</call> 
		]]>
		</xml_code>";
        return $xml;
    }
}