<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\MDirector\Model\Api;

use Alfa9\MDirector\Helper\Data as HelperDirector;

abstract class Client {
    /**
     * Resource of the Api
     * @var string
     */
    protected $resource = '';
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \Magento\Framework\HTTP\ZendClient
     */
    private $httpClient;
    /**
     * Client constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\HTTP\ZendClient $httpClient
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\HTTP\ZendClient $httpClient
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->httpClient = $httpClient;
    }
    /**
     * @param string $configPath
     * @return string
     */
    private function getConfigValue($configPath) {
        return $this->scopeConfig->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORES);
    }
    /**
     * Get the Consumer Key from the system config
     * @return string
     */
    public function getConsumerKey() {
        return $this->getConfigValue(HelperDirector::CONFIG_CONSUMER_KEY);
    }
    /**
     * Get the Consumer Secret from the system config
     * @return string
     */
    public function getConsumerSecret() {
        return $this->getConfigValue(HelperDirector::CONFIG_CONSUMER_SECRET);
    }

    /**
     * Get the Base Url
     * @return string
     */
    public function getBaseUrl(){
        return $this->getConfigValue(HelperDirector::CONFIG_BASE_URL);
    }

    /**
     * Get the client
     * @return \Zend_Oauth_Client
     * @throws \Zend_Oauth_Exception
     */
    public function getClient() {
        $oauthOptions = array(
            'consumerKey'       => $this->getConsumerKey(),
            'consumerSecret'    => $this->getConsumerSecret()
        );
        $client = new \Zend_Oauth_Client($oauthOptions);
        $token = new \Zend_Oauth_Token_Access();
        $client->setToken($token);
        return $client;
    }
    /**
     * Send the HTTP request and return an HTTP response array
     *
     * @param string $url
     * @param array $data
     * @param string $method
     * @return array
     * @throws \Exception
     */
    public function request($url, $data = array(), $method = \Zend_Oauth_Client::GET)
    {
        $client = $this->getClient();
        $client->setConfig(['timeout' => 120]);
        $client->setUri($url);
        $client->setMethod($method);
        if ($method == \Zend_Oauth_Client::POST) {
            $client->setParameterPost($data);
        } elseif (in_array($method, array(\Zend_Oauth_Client::PUT, \Zend_Oauth_Client::DELETE))) {
            // Fix: items[0] -> items[]
            $rawData = http_build_query($data);
            $rawData = preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', $rawData);
            $client->setRawData($rawData, 'application/x-www-form-urlencoded');
        } else {
            $client->setParameterGet($data);
        }
        $response = $client->request();
        $result = json_decode($response->getBody(), true);
        return $result;
    }
    /**
     * Return API Resource
     *
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Return Full Resource URL
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->getBaseUrl(). '/' . $this->getResource();
    }
    /**
     * Retrieve All Lists
     * @param array $params
     * @return array
     */
    public function fetch(array $params = [])
    {
        try {
            return $this->request($this->getUrl());
        } catch (\Exception $e) {
            return null;
        }
    }
    /**
     * Update Contact
     *
     * @param array $data
     * @return array
     */
    public function update(array $data)
    {
        try {
            return $this->request($this->getUrl(), $data, \Zend_Oauth_Client::PUT);
        } catch (\Exception $e) {
            return null;
        }
    }
    /**
     * Create New Free Param
     *
     * @param array $data
     * @return array
     */
    public function create(array $data)
    {
        try {
            return $this->request($this->getUrl(), $data, \Zend_Oauth_Client::POST);
        } catch (\Exception $e) {
            return null;
        }
    }
    /**
     * Delete Free Param
     *
     * @param array $data
     * @return array
     */
    public function delete(array $data)
    {
        try {
            return $this->request($this->getUrl(), $data, \Zend_Oauth_Client::DELETE);
        } catch (\Exception $e) {
            return null;
        }
    }
}