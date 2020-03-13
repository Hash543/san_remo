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

namespace PSS\Loyalty\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Config;
use Magento\Customer\Model\Customer;
use PSS\Loyalty\Helper\Data as LoyaltyHelper;

class InstallData implements InstallDataInterface {
    /**
     * @var LoyaltyHelper
     */
    private $helper;

    /**
     * @var CollectionFactory 
     */
    private $attributeSetCollection;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * InstallData constructor.
     *
     * @param LoyaltyHelper        $helper
     * @param CollectionFactory    $attributeSetCollection
     * @param AttributeSetFactory  $attributeSetFactory
     * @param CategorySetupFactory $categorySetupFactory
     * @param EavSetupFactory      $eavSetupFactory
     * @param Config               $eavConfig
     */
    public function __construct(
        LoyaltyHelper $helper,
        CollectionFactory $attributeSetCollection,
        AttributeSetFactory $attributeSetFactory,
        CategorySetupFactory $categorySetupFactory,
        EavSetupFactory $eavSetupFactory,
        Config $eavConfig
    ) {
        $this->helper = $helper;
        $this->attributeSetCollection = $attributeSetCollection;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->categorySetupFactory = $categorySetupFactory;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
    }

    public function getAttributeSetId($attributeSetName) {
        $attributeSetCollection = $this->attributeSetCollection->create()
                                                               ->addFieldToSelect('attribute_set_id')
                                                               ->addFieldToFilter('attribute_set_name', $attributeSetName)
                                                               ->getFirstItem()
                                                               ->toArray();

        $attributeSetId = (int)implode($attributeSetCollection);

        return $attributeSetId;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createAttributeSet(ModuleDataSetupInterface $setup) {
        $attributeSet = $this->attributeSetFactory->create();

        if(!$this->getAttributeSetId(LoyaltyHelper::ATTRIBUTE_SET_NAME)) {
            $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
            $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
            $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);
            $data = [
                'attribute_set_name' => LoyaltyHelper::ATTRIBUTE_SET_NAME,
                'entity_type_id' => $entityTypeId,
                'sort_order' => 200,
            ];
            $attributeSet->setData($data);
            $attributeSet->validate();
            $attributeSet->save();
            $attributeSet->initFromSkeleton($attributeSetId);
            $attributeSet->save();
        }
    }

    protected function createAttributes(array $entityAttributes, ModuleDataSetupInterface $setup) {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        foreach($entityAttributes as $entityType => $attributes) {
            foreach($attributes as $attributeCode => $attributeData) {
                $eavSetup->removeAttribute($entityType, $attributeCode);
                $eavSetup->addAttribute($entityType, $attributeCode, $attributeData);

                $attribute = $this->eavConfig->getAttribute($entityType, $attributeCode);

                if($entityType == 'customer_address') {
                    $attribute->setData('used_in_forms', ['adminhtml_customer_address']);
                }else {
                    // more used_in_forms ['adminhtml_checkout','adminhtml_customer','adminhtml_customer_address','customer_account_edit','customer_address_edit','customer_register_address']
                    $attribute->setData('used_in_forms', ['adminhtml_customer']);
                }

                $attribute->save();
            }
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createCustomerAttributes(ModuleDataSetupInterface $setup) {
        $entityAttributes = [
            Customer::ENTITY => [
                'id_crm' => [
                    'type' => 'int',
                    'label' => 'ID CRM',
                    'input' => 'text',
                    'required' => false,
                    'visible' => true,
                    'user_defined' => false,
                    'position' => 200,
                    'system' => 0,
                ],
                'origin_crm' => [
                    'type' => 'varchar',
                    'label' => 'Tienda Origen',
                    'input' => 'text',
                    'required' => false,
                    'visible' => true,
                    'user_defined' => false,
                    'position' => 210,
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
                'id_erp' => [
                    'type' => 'int',
                    'label' => 'ID ERP',
                    'input' => 'text',
                    'required' => false,
                    'visible' => true,
                    'user_defined' => false,
                    'position' => 200,
                    'system' => 0,
                ],
                'creation' => [
                    'label' => 'Fecha RGPD',
                    'type' => Table::TYPE_DATETIME,
                    'input' => 'date',
                    'required' => false,
                    'visible' => true,
                    'user_defined' => false,
                    'position' => 230,
                    'system' => 0,
                ],
                'list_id' => [
                    'label' => 'Lista asociada',
                    'type' => Table::TYPE_TEXT,
                    'input' => 'text',
                    'required' => false,
                    'visible' => true,
                    'user_defined' => false,
                    'position' => 240,
                    'system' => 0,
                ],
                'conversion_euro_point' => [
                    'label' => 'Valor conversión/€',
                    'type' => Table::TYPE_DECIMAL,
                    'input' => 'text',
                    'required' => false,
                    'visible' => true,
                    'user_defined' => false,
                    'position' => 250,
                    'system' => 0,
                ],
            ],
            'customer_address' => [
                'lastname1' => [
                    'label' => 'Apellido 1',
                    'type' => 'varchar',
                    'input' => 'text',
                    'required' => false,
                    'visible' => true,
                    'user_defined' => false,
                    'position' => 310,
                    'system'=> false,
                    'visible_on_front' => false,
                ],
                'lastname2' => [
                    'label' => 'Apellido 2',
                    'type' => 'varchar',
                    'input' => 'text',
                    'required' => false,
                    'visible' => true,
                    'user_defined' => false,
                    'position' => 310,
                    'system'=> false,
                    'visible_on_front' => false,
                ],
                'stairs' => [
                    'label' => 'Escalera',
                    'type' => 'varchar',
                    'input' => 'text',
                    'required' => false,
                    'visible' => true,
                    'user_defined' => false,
                    'position' => 310,
                    'system'=> false,
                    'visible_on_front' => false,
                ],
                'floor' => [
                    'label' => 'Piso',
                    'type' => 'varchar',
                    'input' => 'text',
                    'required' => false,
                    'visible' => true,
                    'user_defined' => false,
                    'position' => 310,
                    'system'=> false,
                    'visible_on_front' => false,
                ],
                'door' => [
                    'label' => 'Puerta',
                    'type' => 'varchar',
                    'input' => 'text',
                    'required' => false,
                    'visible' => true,
                    'user_defined' => false,
                    'position' => 310,
                    'system'=> false,
                    'visible_on_front' => false,
                ],
                'phone2' => [
                    'label' => 'Telefono 2 (Movil)',
                    'type' => 'varchar',
                    'input' => 'text',
                    'required' => false,
                    'visible' => true,
                    'user_defined' => false,
                    'position' => 310,
                    'system'=> false,
                    'visible_on_front' => false,
                ],
            ],
        ];

        $this->createAttributes($entityAttributes, $setup);
    }

    /**
     * Installs data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        $setup->startSetup();

        $this->createAttributeSet($setup);
        $this->createCustomerAttributes($setup);

        $setup->endSetup();
    }
}