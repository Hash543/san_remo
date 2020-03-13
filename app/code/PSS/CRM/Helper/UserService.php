<?php
/**
 *  @author Xavier Sanz <xsanz@pss.com>
 *  @copyright Copyright (c) 2017 PSS (http://www.pss.com)
 *  @package PSS
 */

namespace PSS\CRM\Helper;

use Magento\Customer\Api\Data\CustomerInterface;

class UserService
{

    private $dataHelper;

    const CRM_USER = 'crm/user_service/';

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;
    /**
     * @var \PSS\CRM\Logger\Logger
     */
    protected $logger;
    /**
     * UserService constructor.
     * @param Data $dataHelper
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \PSS\CRM\Logger\Logger $logger
     */
    public function __construct(
        \PSS\CRM\Helper\Data $dataHelper,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \PSS\CRM\Logger\Logger $logger
    ) {
        $this->dataHelper = $dataHelper;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
    }

    public function getWSDLUrl() {
        return $this->dataHelper->getConfig($this::CRM_USER . 'wsdl');
    }

    public function getSSLVerify() {
        return $this->dataHelper->getConfig($this::CRM_USER . 'verify_ssl');
    }

    public function getAuthenticate() {
        return $this->dataHelper->getConfig($this::CRM_USER . 'authenticate');
    }

    public function getUser() {
        return $this->dataHelper->getConfig($this::CRM_USER . 'user');
    }

    public function getPassword() {
        return $this->dataHelper->getConfig($this::CRM_USER . 'password');
    }

    public function getPISource() {
        return $this->dataHelper->getConfig($this::CRM_USER . 'processinfo_source');
    }

    public function getPIOrigin() {
        return $this->dataHelper->getConfig($this::CRM_USER . 'processinfo_origin');
    }

    public function getPIUser() {
        return $this->dataHelper->getConfig($this::CRM_USER . 'processinfo_user');
    }

    public function getDebugXML() {
        return $this->dataHelper->getConfig($this::CRM_USER . 'debugXML');
    }

    public function getUseQueue() {
        return $this->dataHelper->getConfig($this::CRM_USER . 'useQueue');
    }

    public function getDebug() {
        return $this->dataHelper->getConfig($this::CRM_USER . 'debug');
    }

    public function getDebugEmail() {
        return $this->dataHelper->getConfig($this::CRM_USER . 'email');
    }

    public function encryptPassword($password) {
        return $this->dataHelper->encryptPassword($password);
    }

    /**
     * @param CustomerInterface $customer
     * @param array $soapArray
     * @return CustomerInterface
     */
    public function updateCustomer(\Magento\Customer\Api\Data\CustomerInterface $customer, $soapArray = [])
    {

        if (count($soapArray) == 0) {
            return $customer;
        }

        $customer->setFirstname($this->check('string', $soapArray['a:nombreCliente']));
        $customer->setCustomAttribute('conversion_euro_point', $this->check('string', $soapArray['a:ValorPuntos']));
        $customer->setCustomAttribute('shipping_street', $this->check('string', $soapArray['a:calleEnvio']));
        $customer->setCustomAttribute('is_employee', $this->check('bool', $soapArray['a:esEmpleado']));
        $customer->setCustomAttribute('billing_street', $this->check('string', $soapArray['a:calleFacturacion']));
        $customer->setTaxvat($this->check('string', $soapArray['a:cifCliente']));
        $customer->setCustomAttribute('shipping_city', $this->check('string', $soapArray['a:ciudadEnvio']));
        $customer->setCustomAttribute('billing_city', $this->check('string', $soapArray['a:ciudadFacturacion']));
        $customer->setCustomAttribute('id_crm', $this->check('string', $soapArray['a:codigoClienteCRM']));
        $customer->setCustomAttribute('id_erp', $this->check('string', $soapArray['a:codigoClienteErp']));
        $customer->setCustomAttribute('shipping_postcode', $this->check('string', $soapArray['a:codigoPostalEnvio']));
        $customer->setCustomAttribute('billing_postcode', $this->check('string', $soapArray['a:codigoPostalFacturacion']));
        $customer->setCustomAttribute('card_code', $this->check('string', $soapArray['a:codigoTarjeta']));
        $customer->setCustomAttribute('shipping_address_email', $this->check('string', $soapArray['a:correoElectronicoEnvio']));
        $customer->setCustomAttribute('billing_address_email', $this->check('string', $soapArray['a:correoElectronicoFacturacion']));

        $customer->setCustomAttribute('shipping_stairs', $this->check('string', $soapArray['a:escaleraEnvio']));
        $customer->setCustomAttribute('billing_stairs', $this->check('string', $soapArray['a:escaleraFacturacion']));
        $customer->setCustomAttribute('fecha_alta_crm', $this->check('date', $soapArray['a:fechaAlta']));
        $customer->setCustomAttribute('fecha_baja_crm', $this->check('date', $soapArray['a:fechaBaja']));
        $customer->setCustomAttribute('creation', $this->check('date', $soapArray['a:fechaRGPD']));
        $customer->setCustomAttribute('comm_lang', $this->check('string', $soapArray['a:idiomaDeComunicacion']));
        $customer->setCustomAttribute('list_id', $this->check('string', $soapArray['a:listaMarketing']));
        $customer->setCustomAttribute('shipping_telephone_mobile', $this->check('string', $soapArray['a:movilEnvio']));
        $customer->setCustomAttribute('billing_telephone_mobile', $this->check('string', $soapArray['a:movilFacturacion']));
        $customer->setCustomAttribute('shipping_floor', $this->check('string', $soapArray['a:pisoEnvio']));
        $customer->setCustomAttribute('billing_floor', $this->check('string', $soapArray['a:pisoFacturacion']));
        $customer->setCustomAttribute('lastname1', $this->check('string', $soapArray['a:primerApellidoCliente']));
        $customer->setCustomAttribute('wants_publicity', $this->check('bool', $soapArray['a:publicidad']));
        $customer->setCustomAttribute('lastname2', $this->check('string', $soapArray['a:segundoApellidoCliente']));
        $customer->setCustomAttribute('gender', $this->check('string', $soapArray['a:sexo']));
        $customer->setCustomAttribute('shipping_telephone', $this->check('string', $soapArray['a:telefonoEnvio']));
        $customer->setCustomAttribute('billing_telephone', $this->check('string', $soapArray['a:telefonoFacturacion']));
        $customer->setCustomAttribute('shipment_type', $this->check('string', $soapArray['a:tipoDireccionEnvio']));
        $customer->setCustomAttribute('id_last_purchase', $this->check('string', $soapArray['a:ultimaCompra']));

        return $customer;

    }


    public function check($type = 'string', $value = null)
    {
        if (is_array($value)) {
            return '';
        } else if ($type == 'date') {

            return is_null($value) ? '' : $value;

        } else if ($type == 'bool') {

            return isset($value) && $value == 'True' ? true : false;

        } else {
            return is_null($value) ? '' : $value;
        }
    }

}