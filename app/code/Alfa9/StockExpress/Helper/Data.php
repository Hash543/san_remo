<?php
namespace Alfa9\StockExpress\Helper;

abstract class Data extends \Magento\Framework\App\Helper\AbstractHelper {
    /**
     * @param null $config
     * @return string
     */
    public function getConfig($config = null) {
        if (!$config) {
            return null;
        }
        return $this->scopeConfig->getValue($config);
    }
    /**
     * @return string
     */
    abstract public function getWSDLUrl();
    /**
     * @return string
     */
    abstract public function getDatabase();

    /**
     * @return boolean
     */
    abstract public function getSSLVerify();

    /**
     * @return string
     */
    abstract public function getAuthenticate();

    /**
     * @return string
     */
    abstract public function getUser();

    /**
     * @return string
     */
    abstract public function getPassword();

    /**
     * @return boolean
     */
    abstract public function getDebugXML();

    /**
     * @return boolean
     */
    abstract public function getDebug();

    /**
     * @return string
     */
    abstract public function getDebugEmail();
}