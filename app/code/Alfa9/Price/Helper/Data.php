<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\Price\Helper;

/**
 * Class Data
 * @package Alfa9\Price\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    /**
     * Const PATH && Grid views
     */
    const CONFIG_ENABLE_DECIMAL_PRECISION = 'alfa9_price/price_decimals/enable';
    const CONFIG_DECIMAL_VIEW = 'alfa9_price/price_decimals/views';
    const DEFAULT_PRECISION_PRICE = 3;
    const PATH_ORDER_GRID = 'sales_order_grid';
    const PATH_ORDER_VIEW_INVOICE = 'sales_order_view_invoice_grid';
    const PATH_ORDER_VIEW_CREDITMEMO = 'sales_order_view_creditmemo_grid';
    const PATH_ORDER_VIEW_SHIPMENT = 'sales_order_view_shipment_grid';
    const PATH_ORDER_GRID_INVOICE = 'sales_order_invoice_grid';
    const PATH_ORDER_GRID_CREDITMEMO  = 'sales_order_creditmemo_grid';
    const PATH_ORDER_VIEW = 'sales_order_view';
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;
    /**
     * @var \Magento\Framework\App\State
     */
    private $state;
    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\State $state
    ){
        $this->request = $request;
        $this->state = $state;
        parent::__construct($context);
    }

    /**
     * Get the precision
     * @param $defaultPrecision
     * @return int
     */
    public function getPricePrecision($defaultPrecision = \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION) {

        $enable = $this->scopeConfig->getValue(self::CONFIG_ENABLE_DECIMAL_PRECISION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($enable) {
            /*$gridViews = [
                self::PATH_ORDER_GRID,
                self::PATH_ORDER_GRID_INVOICE,
                self::PATH_ORDER_VIEW_CREDITMEMO,
                self::PATH_ORDER_VIEW_SHIPMENT,
                self::PATH_ORDER_GRID_CREDITMEMO
            ];
            $paths = [
                self::PATH_ORDER_VIEW
            ];
            $params = $this->request->getParams();
            $fullName = $this->request->getFullActionName();
            if((isset($params['namespace']) && in_array($params['namespace'], $gridViews)) || in_array($fullName, $paths)) {
                $defaultPrecision = self::DEFAULT_PRECISION_PRICE;
            }*/
            try {
                if($this->state->getAreaCode() != \Magento\Framework\App\Area::AREA_FRONTEND) {
                    $defaultPrecision = self::DEFAULT_PRECISION_PRICE;
                }
            }catch (\Exception $exception) {

            }

        }
        return $defaultPrecision;
    }
}