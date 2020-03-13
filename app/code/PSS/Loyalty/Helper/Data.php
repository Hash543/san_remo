<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 *
 * This package designed for Magento COMMUNITY edition
 * PSS Digital does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * PSS Digital does not provide extension support in case of * incorrect edition usage.
 *
 * @author    PSS Digital Team
 * @category  PSS
 * @package   PSS_Loyalty
 * @copyright Copyright (c) 2019 PSS (https://www.pss-ti.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace PSS\Loyalty\Helper;

use Magento\Customer\Model\Customer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\WebsiteFactory;

class Data extends AbstractHelper {
    const WEBSITE_CODE = "loyalty_website";
    const WEBSITE_NAME = "San Remo Fidelización";

    const STORE_CODE = "loyalty_store";
    const STORE_NAME = "San Remo Fidelización - Tienda";

    const STOREVIEW_CODE = "fidelizacion";
    const STOREVIEW_NAME = "San Remo Fidelización - Vista de Tienda";

    const ATTRIBUTE_SET_NAME = "Fidelización";

    const THEME_NAME = 'Pearl/pss_loyalty';

    const CURRENCY_CODE = 'SRP';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfigInterface;

    /**
     * @var WebsiteFactory
     */
    private $websiteFactory;

    /**
     * @var GroupFactory
     */
    private $groupFactory;

    /**
     * @var StoreFactory
     */
    private $storeFactory;

    /**
     * Data constructor.
     *
     * @param Context                $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface  $storeManager
     * @param ScopeConfigInterface   $scopeConfigInterface
     * @param WebsiteFactory         $websiteFactory
     * @param GroupFactory           $groupFactory
     * @param StoreFactory           $storeFactory
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfigInterface,
        WebsiteFactory $websiteFactory,
        GroupFactory $groupFactory,
        StoreFactory $storeFactory
    ) {
        $this->scopeConfigInterface = $scopeConfigInterface;
        $this->websiteFactory = $websiteFactory;
        $this->groupFactory = $groupFactory;
        $this->storeFactory = $storeFactory;

        parent::__construct($context);
    }

    public function getConfig($config = null) {
        if(!$config) {
            return null;
        }

        return $this->scopeConfigInterface->getValue($config);
    }

    public function getWebsite($code = self::WEBSITE_CODE) {
        /** @var \Magento\Store\Model\Website $website */
        $website = $this->websiteFactory->create();
        $website->load($code);

        return $website;
    }

    public function getStore($code = self::STORE_CODE) {
        /** @var \Magento\Store\Model\Website $website */
        $store = $this->groupFactory->create();
        $store->load($code);

        return $store;
    }

    public function getStoreView($code = self::STOREVIEW_CODE) {
        /** @var \Magento\Store\Model\Website $website */
        $storeview = $this->storeFactory->create();
        $storeview->load($code);

        return $storeview;
    }

    public function getOrderAttributes() {
        $attrOrder = [
            'delegation' => Table::TYPE_TEXT,
            'customer_code' => Table::TYPE_TEXT,
            'payment_method_2' => Table::TYPE_TEXT,
            'grand_total_2' => Table::TYPE_DECIMAL,
        ];

        $attrItem = [
            'promocion_aplicada' => Table::TYPE_TEXT,
        ];

        $data = [
            ['table' => 'order', 'attributes' => $attrOrder],
            ['table' => 'order_item', 'attributes' => $attrItem],
            ['table' => 'invoice', 'attributes' => $attrOrder],
            ['table' => 'invoice_item', 'attributes' => $attrItem],
            ['table' => 'creditmemo', 'attributes' => $attrOrder],
            ['table' => 'creditmemo_item', 'attributes' => $attrItem],
            ['table' => 'quote', 'attributes' => $attrOrder],
            ['table' => 'quote_item', 'attributes' => $attrItem],
        ];

        return $data;
    }

    /**
     * @return array
     */
    public function getCustomerAttributesOld() {
        $attributeList = [
            'id_crm' => [
                'type' => 'varchar',
                'label' => 'ID CRM',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 200,
                'system' => 0,
            ],
            'id_erp' => [
                'type' => 'int',
                'label' => 'ID ERP',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 205,
                'system' => 0,
            ],
            'card_code' => [
                'type' => 'varchar',
                'label' => 'Código tarjeta',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 210,
                'system' => 0,
            ],
            'origin_crm' => [
                'type' => 'varchar',
                'label' => 'Tienda Origen',
                'input' => 'text',
                'required' => false,
                'default' => 'TV',
                'visible' => true,
                'user_defined' => false,
                'position' => 215,
                'system' => 0,
            ],
            'update_crm' => [
                'label' => 'Fecha actualización CRM',
                'type' => Table::TYPE_DATETIME,
                'input' => 'date',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 220,
                'system' => 0,
            ],
            'creation' => [
                'label' => 'Fecha RGPD',
                'type' => Table::TYPE_DATETIME,
                'input' => 'date',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 225,
                'system' => 0,
            ],
            'list_id' => [
                'label' => 'Lista asociada',
                'type' => 'int',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 230,
                'system' => 0,
            ],
            'conversion_euro_point' => [
                'label' => 'Valor conversión/€',
                'type' => Table::TYPE_DECIMAL,
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 235,
                'system' => 0,
            ],
            'fecha_alta_crm' => [
                'label' => 'Fecha de creación CRM',
                'type' => Table::TYPE_DATETIME,
                'input' => 'date',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 240,
                'system' => 0,
            ],
            'fecha_baja_crm' => [
                'label' => 'Fecha de baja CRM',
                'type' => Table::TYPE_DATETIME,
                'input' => 'date',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 245,
                'system' => 0,
            ],
            'comm_lang' => [
                'type' => 'int',
                'label' => 'Idioma de comunicacion',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 250,
                'system' => 0,
            ],
            'wants_publicity' => [
                'type' => 'int',
                'label' => 'Desea publicidad',
                'input' => 'boolean',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 255,
                'system' => 0,
            ],
            'is_employee' => [
                'type' => 'int',
                'label' => 'Es empleado',
                'input' => 'boolean',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 260,
                'system' => 0,
            ],
            /*'fecha_inicio_puntos' => [
                'label' => 'Fecha de inicio de puntos',
                'type' => Table::TYPE_DATETIME,
                'input' => 'date',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 265,
                'system' => 0,
            ],
            'fecha_fin_puntos' => [
                'label' => 'Fecha de finalización de puntos',
                'type' => Table::TYPE_DATETIME,
                'input' => 'date',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 270,
                'system' => 0,
            ],*/
            'id_last_purchase' => [
                'type' => 'int',
                'label' => 'ID ultima compra',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 275,
                'system' => 0,
            ],

            'lastname1' => [
                'label' => 'Apellido 1',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 300,
                'system' => false,
                'visible_on_front' => false,
            ],
            'lastname2' => [
                'label' => 'Apellido 2',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 305,
                'system' => false,
                'visible_on_front' => false,
            ],
            'stairs' => [
                'label' => 'Escalera',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 315,
                'system' => false,
                'visible_on_front' => false,
            ],
            'floor' => [
                'label' => 'Piso',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 320,
                'system' => false,
                'visible_on_front' => false,
            ],
            'door' => [
                'label' => 'Puerta',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 325,
                'system' => false,
                'visible_on_front' => false,
            ],
            'address_email' => [
                'label' => 'Email Dirección',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 330,
                'system' => false,
                'visible_on_front' => false,
            ],
            'phone2' => [
                'label' => 'Telefono 2 (Movil)',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 335,
                'system' => false,
                'visible_on_front' => false,
            ],
        ];

        $entityAttributes = [
            Customer::ENTITY => $attributeList,
            'customer_address' => $attributeList,
        ];

        return $entityAttributes;
    }

    /**
     * @return array
     */
    public function getCustomerAttributes() {
        $addressCustomerAttributesSchema = [
            'street' => [
                'label' => 'Calle',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 0,
                'system' => false,
                'visible_on_front' => false,
            ],
            'floor' => [
                'label' => 'Piso',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 5,
                'system' => false,
                'visible_on_front' => false,
            ],
            'stairs' => [
                'label' => 'Escalera',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 10,
                'system' => false,
                'visible_on_front' => false,
            ],
            'door' => [
                'label' => 'Puerta',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 15,
                'system' => false,
                'visible_on_front' => false,
            ],
            'postcode' => [
                'label' => 'Código Postal',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 20,
                'system' => false,
                'visible_on_front' => false,
            ],
            'city' => [
                'label' => 'Ciudad',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 25,
                'system' => false,
                'visible_on_front' => false,
            ],
            'region' => [
                'label' => 'Estado/Provincia',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 30,
                'system' => false,
                'visible_on_front' => false,
                'backend' => \Magento\Customer\Model\ResourceModel\Address\Attribute\Backend\Region::class,
            ],
            'telephone' => [
                'label' => 'Teléfono',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 35,
                'system' => false,
                'visible_on_front' => false,
            ],
            'telephone_mobile' => [
                'label' => 'Teléfono (Movil)',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 40,
                'system' => false,
                'visible_on_front' => false,
            ],
            'address_email' => [
                'label' => 'Email',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 45,
                'system' => false,
                'visible_on_front' => false,
            ],
        ];

        $addressCustomerAttributesBilling = [];
        $addressCustomerAttributesShipping = [];
        foreach($addressCustomerAttributesSchema as $attrCode => $attrData) {
            // Attributes set to Billing
            $addressCustomerAttributesBilling['billing_' . $attrCode] = $attrData;
            $addressCustomerAttributesBilling['billing_' . $attrCode]['label'] = "Facturación - " . $addressCustomerAttributesBilling['billing_' . $attrCode]['label'];
            $addressCustomerAttributesBilling['billing_' . $attrCode]['position'] = 300 + $addressCustomerAttributesBilling['billing_' . $attrCode]['position'];

            // Attributes set to Shipping
            $addressCustomerAttributesShipping['shipping_' . $attrCode] = $attrData;
            $addressCustomerAttributesShipping['shipping_' . $attrCode]['label'] = "Envío - " . $addressCustomerAttributesShipping['shipping_' . $attrCode]['label'];
            $addressCustomerAttributesShipping['shipping_' . $attrCode]['position'] = 400 + $addressCustomerAttributesShipping['shipping_' . $attrCode]['position'];
        }

        $attributeListCustomer = [
            'lastname1' => [
                'label' => 'Apellido 1',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 200,
                'system' => false,
                'visible_on_front' => false,
            ],
            'lastname2' => [
                'label' => 'Apellido 2',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 201,
                'system' => false,
                'visible_on_front' => false,
            ],
            'id_crm' => [
                'type' => 'varchar',
                'label' => 'ID CRM',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 202,
                'system' => 0,
            ],
            'id_erp' => [
                'type' => 'int',
                'label' => 'ID ERP',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 205,
                'system' => 0,
            ],
            'card_code' => [
                'type' => 'varchar',
                'label' => 'Código tarjeta',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 210,
                'system' => 0,
            ],
            'origin_crm' => [
                'type' => 'varchar',
                'label' => 'Tienda Origen',
                'input' => 'text',
                'required' => false,
                'default' => 'TV',
                'visible' => true,
                'user_defined' => false,
                'position' => 215,
                'system' => 0,
            ],
            'update_crm' => [
                'label' => 'Fecha actualización CRM',
                'type' => Table::TYPE_DATETIME,
                'input' => 'date',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 220,
                'system' => 0,
            ],
            'creation' => [
                'label' => 'Fecha RGPD',
                'type' => Table::TYPE_DATETIME,
                'input' => 'date',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 225,
                'system' => 0,
            ],
            'list_id' => [
                'label' => 'Lista asociada',
                'type' => 'int',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 230,
                'system' => 0,
            ],
            'conversion_euro_point' => [
                'label' => 'Valor conversión/€',
                'type' => Table::TYPE_DECIMAL,
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 235,
                'system' => 0,
            ],
            'fecha_alta_crm' => [
                'label' => 'Fecha de creación CRM',
                'type' => Table::TYPE_DATETIME,
                'input' => 'date',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 240,
                'system' => 0,
            ],
            'fecha_baja_crm' => [
                'label' => 'Fecha de baja CRM',
                'type' => Table::TYPE_DATETIME,
                'input' => 'date',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 245,
                'system' => 0,
            ],
            'comm_lang' => [
                'type' => 'int',
                'label' => 'Idioma de comunicacion',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 250,
                'system' => 0,
            ],
            'wants_publicity' => [
                'type' => 'int',
                'label' => 'Desea publicidad',
                'input' => 'boolean',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'position' => 255,
                'system' => 0,
            ],
            'is_employee' => [
                'type' => 'int',
                'label' => 'Es empleado',
                'input' => 'boolean',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 260,
                'system' => 0,
            ],
            /*'fecha_inicio_puntos' => [
                'label' => 'Fecha de inicio de puntos',
                'type' => Table::TYPE_DATETIME,
                'input' => 'date',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 265,
                'system' => 0,
            ],
            'fecha_fin_puntos' => [
                'label' => 'Fecha de finalización de puntos',
                'type' => Table::TYPE_DATETIME,
                'input' => 'date',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 270,
                'system' => 0,
            ],*/
            'id_last_purchase' => [
                'type' => 'int',
                'label' => 'ID ultima compra',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 275,
                'system' => 0,
            ],
        ];

        $attributeListAddress = [
            'floor' => [
                'label' => 'Piso',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 100,
                'system' => false,
                'visible_on_front' => false,
            ],
            'stairs' => [
                'label' => 'Escalera',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 105,
                'system' => false,
                'visible_on_front' => false,
            ],
            'door' => [
                'label' => 'Puerta',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 110,
                'system' => false,
                'visible_on_front' => false,
            ],
            'telephone_mobile' => [
                'label' => 'Telefono (Movil)',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 115,
                'system' => false,
                'visible_on_front' => false,
            ],
            'address_email' => [
                'label' => 'Email Dirección',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 120,
                'system' => false,
                'visible_on_front' => false,
            ],
        ];

        $entityAttributes = [
            Customer::ENTITY => array_merge($attributeListCustomer, $addressCustomerAttributesBilling, $addressCustomerAttributesShipping),
            'customer_address' => $attributeListAddress,
        ];

        return $entityAttributes;
    }

    public function getCustomerAttributesAddress() {
        $addressCustomerAttributesSchema = [
            'firstname' => [
                'label' => 'Nombre',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 0,
                'system' => false,
                'visible_on_front' => false,
            ],
            'lastname1' => [
                'label' => 'Apellido 1',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 5,
                'system' => false,
                'visible_on_front' => false,
            ],
            'lastname2' => [
                'label' => 'Apellido 2',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 10,
                'system' => false,
                'visible_on_front' => false,
            ],
            'street' => [
                'label' => 'Calle',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 15,
                'system' => false,
                'visible_on_front' => false,
            ],
            'floor' => [
                'label' => 'Piso',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 20,
                'system' => false,
                'visible_on_front' => false,
            ],
            'stairs' => [
                'label' => 'Escalera',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 25,
                'system' => false,
                'visible_on_front' => false,
            ],
            'door' => [
                'label' => 'Puerta',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 30,
                'system' => false,
                'visible_on_front' => false,
            ],
            'postcode' => [
                'label' => 'Código Postal',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 35,
                'system' => false,
                'visible_on_front' => false,
            ],
            'city' => [
                'label' => 'Ciudad',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 40,
                'system' => false,
                'visible_on_front' => false,
            ],
            'region' => [
                'label' => 'Estado/Provincia',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 45,
                'system' => false,
                'visible_on_front' => false,
                'backend' => \Magento\Customer\Model\ResourceModel\Address\Attribute\Backend\Region::class,
            ],
            'telephone' => [
                'label' => 'Teléfono',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 50,
                'system' => false,
                'visible_on_front' => false,
            ],
            'telephone_mobile' => [
                'label' => 'Teléfono (Movil)',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 55,
                'system' => false,
                'visible_on_front' => false,
            ],
            'address_email' => [
                'label' => 'Email',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 60,
                'system' => false,
                'visible_on_front' => false,
            ],
        ];

        $addressCustomerAttributesBilling = [];
        $addressCustomerAttributesShipping = [];
        foreach($addressCustomerAttributesSchema as $attrCode => $attrData) {
            // Attributes set to Billing
            $addressCustomerAttributesBilling['billing_' . $attrCode] = $attrData;
            $addressCustomerAttributesBilling['billing_' . $attrCode]['label'] = "Facturación - " . $addressCustomerAttributesBilling['billing_' . $attrCode]['label'];
            $addressCustomerAttributesBilling['billing_' . $attrCode]['position'] = 300 + $addressCustomerAttributesBilling['billing_' . $attrCode]['position'];

            // Attributes set to Shipping
            $addressCustomerAttributesShipping['shipping_' . $attrCode] = $attrData;
            $addressCustomerAttributesShipping['shipping_' . $attrCode]['label'] = "Envío - " . $addressCustomerAttributesShipping['shipping_' . $attrCode]['label'];
            $addressCustomerAttributesShipping['shipping_' . $attrCode]['position'] = 400 + $addressCustomerAttributesShipping['shipping_' . $attrCode]['position'];
        }

        $attributeListAddress = [
            'lastname1' => [
                'label' => 'Apellido 1',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 100,
                'system' => false,
                'visible_on_front' => false,
            ],
            'lastname2' => [
                'label' => 'Apellido 2',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 105,
                'system' => false,
                'visible_on_front' => false,
            ],
            'floor' => [
                'label' => 'Piso',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 110,
                'system' => false,
                'visible_on_front' => false,
            ],
            'stairs' => [
                'label' => 'Escalera',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 115,
                'system' => false,
                'visible_on_front' => false,
            ],
            'door' => [
                'label' => 'Puerta',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 120,
                'system' => false,
                'visible_on_front' => false,
            ],
            'telephone_mobile' => [
                'label' => 'Telefono (Movil)',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 125,
                'system' => false,
                'visible_on_front' => false,
            ],
            'address_email' => [
                'label' => 'Email Dirección',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'position' => 130,
                'system' => false,
                'visible_on_front' => false,
            ],
        ];

        $entityAttributes = [
            Customer::ENTITY => array_merge($addressCustomerAttributesBilling, $addressCustomerAttributesShipping),
            'customer_address' => $attributeListAddress,
        ];

        return $entityAttributes;
    }
}