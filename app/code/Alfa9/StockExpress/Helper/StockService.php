<?php
/**
 *  @author Xavier Sanz <xsanz@alfa9.com>
 *  @copyright Copyright (c) 2017 Alfa9 (http://www.alfa9.com)
 *  @package Alfa9
 */

namespace Alfa9\StockExpress\Helper;

/**
 * Class StockService
 * @package Alfa9\StockExpress\Helper
 */
class StockService extends  Data {

    const STOCK_WS = 'stockexpress/stock_service/';

    /**
     * {@inheritdoc}
     */
    public function getWSDLUrl() {
        return $this->getConfig(self::STOCK_WS . 'wsdl');
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabase() {
        return $this->getConfig(self::STOCK_WS . 'database');
    }
    /**
     * {@inheritdoc}
     */
    public function getSSLVerify() {
        return $this->getConfig(self::STOCK_WS . 'verify_ssl');
    }
    /**
     * {@inheritdoc}
     */
    public function getAuthenticate() {
        return $this->getConfig($this::STOCK_WS . 'authenticate');
    }
    /**
     * {@inheritdoc}
     */
    public function getUser() {
        return $this->getConfig($this::STOCK_WS . 'user');
    }
    /**
     * {@inheritdoc}
     */
    public function getPassword() {
        return $this->getConfig($this::STOCK_WS . 'password');
    }
    /**
     * {@inheritdoc}
     */
    public function getDebugXML() {
        return $this->getConfig($this::STOCK_WS . 'debugXML');
    }
    /**
     * {@inheritdoc}
     */
    public function getDebug() {
        return $this->getConfig($this::STOCK_WS . 'debug');
    }
    /**
     * {@inheritdoc}
     */
    public function getDebugEmail() {
        return $this->getConfig($this::STOCK_WS . 'email');
    }
}