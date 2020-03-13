<?php
namespace Alfa9\StockExpress\Model;

use \Magento\Framework\Exception\LocalizedException;

/**
 * Class StockRepository
 * @package Alfa9\StockExpress\Model
 */
class StockRepository implements \Alfa9\StockExpress\Api\StockRepositoryInterface {

    /**
     * @var Api\StockService
     */
    protected $stockService;

    /**
     * StockRepository constructor.
     * @param Api\StockService $stockService
     */
    public function __construct(
        Api\StockService $stockService
    ) {
        $this->stockService = $stockService;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($sku, $quantity)
    {

        try {
            $xml = $this->stockService->queryStock($sku, $quantity);
            if (is_string($xml)) {
                $p = new \Magento\Framework\Xml\Parser;
                $p->loadXML($xml);
                $array = $p->xmlToArray();
                try {
                    return $array['SOAP-ENV:Envelope']['SOAP-ENV:Body']['ns1:executeXMLResponse']['_value']['sqlresponse']['_value']['sqlresponse']['_value']['rowset']['_value'];
                } catch (\Exception $e) {
                    return [];
                }
            } else {
                throw new LocalizedException(__('No result for this product.'));
            }
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */

    public function getListMulti($products)
    {
        try {
            //TODO REALIZAR CONSULTA A PRODUCT ID
            $xml = $this->stockService->queryStockMulti($products);

            if (is_string($xml)) {
                $p = new \Magento\Framework\Xml\Parser;
                $p->loadXML($xml);
                $array = $p->xmlToArray();
                try {
                    return $array['SOAP-ENV:Envelope']['SOAP-ENV:Body']['ns1:executeXMLResponse']['_value']['sqlresponse']['_value']['sqlresponse']['_value']['rowset']['_value'];
                } catch (\Exception $e) {
                    return false;
                }
            } else {
                throw new LocalizedException(__('No result for this product.'));
            }
        } catch (\Exception $e) {
            return json_encode(['message' => $e->getMessage()]);
        }

    }
}
