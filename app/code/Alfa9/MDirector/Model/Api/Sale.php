<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\MDirector\Model\Api;

class Sale extends Client {

    /**
     * @override
     */
    protected $resource = 'api_sale';
    /**
     * Create New Sale
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function notify(array $data)
    {
        return $this->request($this->getUrl(), $data, \Zend_Oauth_Client::POST);
    }
}