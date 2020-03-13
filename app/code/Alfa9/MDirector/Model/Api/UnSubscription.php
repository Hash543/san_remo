<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\MDirector\Model\Api;

class UnSubscription extends Client {

    /**
     * @override
     */
    protected $resource = 'api_unsubscription';
    /**
     * Retrieve All Lists
     *
     * @param array $data
     * @return array
     */
    public function fetch(array $data = [])
    {
        try {
            return $this->request($this->getUrl(), $data, \Zend_Oauth_Client::GET);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param $startDate
     * @param null $endDate
     * @param string $format
     * @return array
     * @throws \Exception
     */
    public function findByDate($startDate, $endDate = null, $format = 'json')
    {
        $data = array(
            'date'      => $startDate,
            'endDate'   => $endDate,
            'format'    => $format
        );
        $data = array_filter($data);
        return $this->request($this->getFindByDateUrl(), $data, \Zend_Oauth_Client::GET);
    }

    /**
     * @return string
     */
    public function getFindByDateUrl()
    {
        return $this->getUrl() . '_find-by-date';
    }
}

