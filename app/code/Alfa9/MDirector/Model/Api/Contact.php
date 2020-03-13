<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\MDirector\Model\Api;

class Contact extends Client {
    /**
     * @override
     */
    protected $resource = 'api_contact';

    /**
     * {@inheritdoc}
     */
    public function fetch(array $data = [])
    {
        try {
            return $this->request($this->getUrl(), $data, \Zend_Oauth_Client::GET);
        } catch (\Exception $e) {
            return null;
        }
    }

}
