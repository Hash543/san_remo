<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsPro
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsPro\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @package Mageplaza\RewardPointsPro\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    const CATALOGRULE_TABLE                  = 'mageplaza_reward_catalogrule';
    const CATALOGRULE_PRODUCT_TABLE          = 'mageplaza_reward_catalogrule_product';
    const CATALOGRULE_WEBSITE_TABLE          = 'mageplaza_reward_catalogrule_website';
    const CATALOGRULE_CUSTOMER_GROUP_TABLE   = 'mageplaza_reward_catalogrule_customer_group';
    const SHOPPING_CART_TABLE                = 'mageplaza_reward_shopping_cart';
    const SHOPPING_CART_WEBSITE_TABLE        = 'mageplaza_reward_shopping_cart_website';
    const SHOPPING_CART_CUSTOMER_GROUP_TABLE = 'mageplaza_reward_shopping_cart_customer_group';
    const SHOPPING_CART_LABEL_TABLE          = 'mageplaza_reward_shopping_cart_label';

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();

        $customerGroupTable = $setup->getConnection()->describeTable($setup->getTable('customer_group'));
        $customerGroupIdType = $customerGroupTable['customer_group_id']['DATA_TYPE'] == 'int'
            ? Table::TYPE_INTEGER : $customerGroupTable['customer_group_id']['DATA_TYPE'];

        /**
         * Mageplaza Reward Catalog Rule
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::CATALOGRULE_TABLE))
            ->addColumn('rule_id', Table::TYPE_INTEGER, null, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true,
            ], 'Rule Id')
            ->addColumn('name', Table::TYPE_TEXT, 255, [], 'Name')
            ->addColumn('description', Table::TYPE_TEXT, '64k', [], 'Description')
            ->addColumn('from_date', Table::TYPE_DATE, null, [], 'From Date')
            ->addColumn('to_date', Table::TYPE_DATE, null, [], 'To Date')
            ->addColumn('is_active', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '0',], 'Is Active')
            ->addColumn('conditions_serialized', Table::TYPE_TEXT, '2M', [], 'Conditions Serialized')
            ->addColumn('actions_serialized', Table::TYPE_TEXT, '2M', [], 'Actions Serialized')
            ->addColumn(
                'stop_rules_processing',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '1',],
                'Stop Rules Processing'
            )
            ->addColumn(
                'sort_order',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0',],
                'Sort Order'
            )
            ->addColumn('action', Table::TYPE_TEXT, 32, ['nullable' => false, 'default' => 'fixed',], 'Simple Action')
            ->addColumn(
                'discount_style',
                Table::TYPE_TEXT,
                25,
                ['nullable' => false, 'default' => 'fixed',],
                'Discount Style'
            )
            ->addColumn('point_amount', Table::TYPE_INTEGER, 11, ['nullable' => false, 'default' => 0,], 'Point Amount')
            ->addColumn(
                'discount_amount',
                Table::TYPE_INTEGER,
                11,
                ['nullable' => false, 'default' => 0,],
                'Discount Amount'
            )
            ->addColumn('money_step', Table::TYPE_INTEGER, 11, ['nullable' => false, 'default' => 0,], 'Money Step')
            ->addColumn('max_points', Table::TYPE_INTEGER, 11, ['nullable' => false, 'default' => 0,], 'Max Points')
            ->addColumn('priority', Table::TYPE_INTEGER, 11, ['nullable' => true, 'default' => 0,], 'Priority')
            ->addIndex(
                $installer->getIdxName(self::CATALOGRULE_TABLE, ['is_active', 'sort_order', 'to_date', 'from_date']),
                ['is_active', 'sort_order', 'to_date', 'from_date']
            )
            ->setComment('Mageplaza Reward Catalog Rule');
        $connection->createTable($table);

        /**
         * Mageplaza Reward Catalog Rule Product
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::CATALOGRULE_PRODUCT_TABLE))
            ->addColumn('rule_product_id', Table::TYPE_INTEGER, null, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true
            ], 'Rule Product Id')
            ->addColumn(
                'rule_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Rule Id'
            )
            ->addColumn(
                'from_time',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'From Time'
            )
            ->addColumn(
                'to_time',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'To time'
            )
            ->addColumn(
                'customer_group_id',
                $customerGroupIdType,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
                'Customer Group Id'
            )
            ->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Product Id'
            )
            ->addColumn('rule_type', Table::TYPE_SMALLINT, 6, ['unsigned' => true, 'nullable' => false], 'Rule Type')
            ->addColumn('action', Table::TYPE_TEXT, 32, ['default' => 'fixed'], 'Simple Action')
            ->addColumn('discount_style', Table::TYPE_TEXT, 32, ['default' => 'fixed'], 'Discount Style')
            ->addColumn(
                'point_amount',
                Table::TYPE_INTEGER,
                11,
                ['nullable' => false, 'default' => '0'],
                'Point Amount'
            )
            ->addColumn(
                'discount_amount',
                Table::TYPE_DECIMAL,
                [12, 4],
                ['nullable' => false, 'default' => '0'],
                'Discount Amount'
            )
            ->addColumn(
                'action_stop',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '0'],
                'Action Stop'
            )
            ->addColumn(
                'sort_order',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Sort Order'
            )
            ->addColumn(
                'website_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Website Id'
            )
            ->addColumn(
                'money_step',
                Table::TYPE_DECIMAL,
                [12, 4],
                ['nullable' => false, 'default' => '0'],
                'Point Amount'
            )
            ->addColumn('max_points', Table::TYPE_INTEGER, 11, ['unsigned' => true, 'nullable' => false], 'Max Points')
            ->addIndex(
                $installer->getIdxName(
                    self::CATALOGRULE_PRODUCT_TABLE,
                    ['rule_id', 'from_time', 'to_time', 'website_id', 'customer_group_id', 'product_id', 'sort_order'],
                    true
                ),
                ['rule_id', 'from_time', 'to_time', 'website_id', 'customer_group_id', 'product_id', 'sort_order'],
                ['type' => 'unique']
            )
            ->addIndex($installer->getIdxName(self::CATALOGRULE_PRODUCT_TABLE, ['rule_id']), ['rule_id'])
            ->addIndex(
                $installer->getIdxName(self::CATALOGRULE_PRODUCT_TABLE, ['customer_group_id']),
                ['customer_group_id']
            )
            ->addIndex($installer->getIdxName(self::CATALOGRULE_PRODUCT_TABLE, ['website_id']), ['website_id'])
            ->addIndex($installer->getIdxName(self::CATALOGRULE_PRODUCT_TABLE, ['from_time']), ['from_time'])
            ->addIndex($installer->getIdxName(self::CATALOGRULE_PRODUCT_TABLE, ['to_time']), ['to_time'])
            ->addIndex($installer->getIdxName(self::CATALOGRULE_PRODUCT_TABLE, ['product_id']), ['product_id'])
            ->addForeignKey(
                $installer->getFkName(
                    self::CATALOGRULE_PRODUCT_TABLE,
                    'product_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    self::CATALOGRULE_PRODUCT_TABLE,
                    'customer_group_id',
                    'customer_group',
                    'customer_group_id'
                ),
                'customer_group_id',
                $installer->getTable('customer_group'),
                'customer_group_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    self::CATALOGRULE_PRODUCT_TABLE,
                    'rule_id',
                    self::CATALOGRULE_PRODUCT_TABLE,
                    'rule_id'
                ),
                'rule_id',
                $installer->getTable(self::CATALOGRULE_TABLE),
                'rule_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(self::CATALOGRULE_PRODUCT_TABLE, 'website_id', 'store_website', 'website_id'),
                'website_id',
                $installer->getTable('store_website'),
                'website_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Mageplaza Reward Catalog Rule Product');

        $connection->createTable($table);

        /**
         * Mageplaza Reward Catalog Rule Website
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::CATALOGRULE_WEBSITE_TABLE))
            ->addColumn('rule_id', Table::TYPE_INTEGER, null, [
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true
            ], 'Rule Id')
            ->addColumn(
                'website_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Website Id'
            )
            ->addIndex($installer->getIdxName(self::CATALOGRULE_WEBSITE_TABLE, ['rule_id']), ['rule_id'])
            ->addIndex($installer->getIdxName(self::CATALOGRULE_WEBSITE_TABLE, ['website_id']), ['website_id'])
            ->addForeignKey(
                $installer->getFkName(self::CATALOGRULE_WEBSITE_TABLE, 'rule_id', self::CATALOGRULE_TABLE, 'rule_id'),
                'rule_id',
                $installer->getTable(self::CATALOGRULE_TABLE),
                'rule_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(self::CATALOGRULE_WEBSITE_TABLE, 'website_id', 'store_website', 'website_id'),
                'website_id',
                $installer->getTable('store_website'),
                'website_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Mageplaza Reward Catalog Rules Websites');

        $connection->createTable($table);

        /**
         * Mageplaza Reward Catalog Rule Customer Group
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::CATALOGRULE_CUSTOMER_GROUP_TABLE))
            ->addColumn('rule_id', Table::TYPE_INTEGER, null, [
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true
            ], 'Rule Id')
            ->addColumn(
                'customer_group_id',
                $customerGroupIdType,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Customer Group Id'
            )
            ->addIndex($installer->getIdxName(self::CATALOGRULE_CUSTOMER_GROUP_TABLE, ['rule_id']), ['rule_id'])
            ->addIndex(
                $installer->getIdxName(self::CATALOGRULE_CUSTOMER_GROUP_TABLE, ['customer_group_id']),
                ['customer_group_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    self::CATALOGRULE_CUSTOMER_GROUP_TABLE,
                    'rule_id',
                    self::CATALOGRULE_TABLE,
                    'rule_id'
                ),
                'rule_id',
                $installer->getTable(self::CATALOGRULE_TABLE),
                'rule_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    self::CATALOGRULE_CUSTOMER_GROUP_TABLE,
                    'customer_group_id',
                    'customer_group',
                    'customer_group_id'
                ),
                'customer_group_id',
                $installer->getTable('customer_group'),
                'customer_group_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Mageplaza Reward Catalog Rules Customer Groups');
        $connection->createTable($table);

        /**
         * Mageplaza Reward Shopping Cart
         */
        if (!$installer->tableExists(self::SHOPPING_CART_TABLE)) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable(self::SHOPPING_CART_TABLE))
                ->addColumn('rule_id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true,
                ], 'Rule Id')
                ->addColumn(
                    'rule_type',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => '1'],
                    'Rule Type'
                )
                ->addColumn('name', Table::TYPE_TEXT, 255, [], 'Name')
                ->addColumn('description', Table::TYPE_TEXT, '64k', [], 'Description')
                ->addColumn('from_date', Table::TYPE_DATE, null, [], 'From Date')
                ->addColumn('to_date', Table::TYPE_DATE, null, [], 'To Date')
                ->addColumn(
                    'is_active',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Is Active'
                )
                ->addColumn('conditions_serialized', Table::TYPE_TEXT, '2M', [], 'Conditions Serialized')
                ->addColumn('actions_serialized', Table::TYPE_TEXT, '2M', [], 'Actions Serialized')
                ->addColumn(
                    'stop_rules_processing',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => '1'],
                    'Stop Rules Processing'
                )
                ->addColumn(
                    'sort_order',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Sort Order'
                )
                ->addColumn(
                    'is_advanced',
                    Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '1',],
                    'Is Advanced'
                )
                ->addColumn('product_ids', Table::TYPE_TEXT, '64k', [], 'Product Ids')
                ->addColumn('action', Table::TYPE_TEXT, 32, ['nullable' => false, 'default' => 'fixed'], 'Action')
                ->addColumn(
                    'point_amount',
                    Table::TYPE_INTEGER,
                    11,
                    ['unsigned' => true, 'nullable' => false],
                    'Point Amount'
                )
                ->addColumn(
                    'money_step',
                    Table::TYPE_INTEGER,
                    11,
                    ['unsigned' => true, 'nullable' => false],
                    'Money Step'
                )
                ->addColumn(
                    'qty_step',
                    Table::TYPE_INTEGER,
                    11,
                    ['unsigned' => true, 'nullable' => false],
                    'Quantity Step'
                )
                ->addColumn(
                    'max_points',
                    Table::TYPE_INTEGER,
                    11,
                    ['unsigned' => true, 'nullable' => false],
                    'Max Points'
                )
                ->addColumn(
                    'discount_style',
                    Table::TYPE_TEXT,
                    32,
                    ['nullable' => false, 'default' => 'fixed'],
                    'Discount Style'
                )
                ->addColumn(
                    'discount_amount',
                    Table::TYPE_INTEGER,
                    11,
                    ['nullable' => false, 'default' => '0'],
                    'Discount Amount'
                )
                ->addIndex(
                    $installer->getIdxName(
                        self::SHOPPING_CART_TABLE,
                        ['is_active', 'sort_order', 'to_date', 'from_date']
                    ),
                    ['is_active', 'sort_order', 'to_date', 'from_date']
                )
                ->setComment('Mageplaza Reward Shopping Cart');

            $connection->createTable($table);
        }

        /**
         * Create table 'mageplaza_reward_shopping_cart_website' if not exists. This table will be used instead of
         * column website_ids of main catalog rules table
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::SHOPPING_CART_WEBSITE_TABLE))
            ->addColumn('rule_id', Table::TYPE_INTEGER, null, [
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true
            ], 'Rule Id')
            ->addColumn(
                'website_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Website Id'
            )
            ->addIndex($installer->getIdxName(self::SHOPPING_CART_WEBSITE_TABLE, ['website_id']), ['website_id'])
            ->addForeignKey(
                $installer->getFkName(
                    self::SHOPPING_CART_WEBSITE_TABLE,
                    'rule_id',
                    self::SHOPPING_CART_TABLE,
                    'rule_id'
                ),
                'rule_id',
                $installer->getTable(self::SHOPPING_CART_TABLE),
                'rule_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(self::SHOPPING_CART_WEBSITE_TABLE, 'website_id', 'store_website', 'website_id'),
                'website_id',
                $installer->getTable('store_website'),
                'website_id',
                Table::ACTION_CASCADE
            )
            ->setComment(
                'Mageplaza Reward Shopping Cart Website Table'
            );

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'mageplaza_reward_shopping_cart_customer_group' if not exists. This table will be used instead of
         * column customer_group_ids of main catalog rules table
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::SHOPPING_CART_CUSTOMER_GROUP_TABLE))
            ->addColumn('rule_id', Table::TYPE_INTEGER, null, [
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true
            ], 'Rule Id')
            ->addColumn(
                'customer_group_id',
                $customerGroupIdType,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Customer Group Id'
            )
            ->addIndex($installer->getIdxName(self::SHOPPING_CART_CUSTOMER_GROUP_TABLE, ['rule_id']), ['rule_id'])
            ->addIndex(
                $installer->getIdxName(self::SHOPPING_CART_CUSTOMER_GROUP_TABLE, ['customer_group_id']),
                ['customer_group_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    self::SHOPPING_CART_CUSTOMER_GROUP_TABLE,
                    'rule_id',
                    self::SHOPPING_CART_TABLE,
                    'rule_id'
                ),
                'rule_id',
                $installer->getTable(self::SHOPPING_CART_TABLE),
                'rule_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    self::SHOPPING_CART_CUSTOMER_GROUP_TABLE,
                    'customer_group_id',
                    'customer_group',
                    'customer_group_id'
                ),
                'customer_group_id',
                $installer->getTable('customer_group'),
                'customer_group_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Mageplaza Reward Shopping Cart Rules Customer Groups');

        $connection->createTable($table);

        /**
         * Mageplaza Reward Shopping Cart Label
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::SHOPPING_CART_LABEL_TABLE))
            ->addColumn('label_id', Table::TYPE_INTEGER, null, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true
            ], 'Label Id')
            ->addColumn('rule_id', Table::TYPE_INTEGER, null, ['unsigned' => true, 'nullable' => false], 'Rule Id')
            ->addColumn('store_id', Table::TYPE_SMALLINT, null, ['unsigned' => true, 'nullable' => false], 'Store Id')
            ->addColumn('label', Table::TYPE_TEXT, 255, [], 'Label')
            ->addIndex(
                $installer->getIdxName(
                    'salesrule_label',
                    ['rule_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['rule_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex($installer->getIdxName(self::SHOPPING_CART_LABEL_TABLE, ['store_id']), ['store_id'])
            ->addForeignKey(
                $installer->getFkName(self::SHOPPING_CART_LABEL_TABLE, 'rule_id', self::SHOPPING_CART_TABLE, 'rule_id'),
                'rule_id',
                $installer->getTable(self::SHOPPING_CART_TABLE),
                'rule_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(self::SHOPPING_CART_LABEL_TABLE, 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )->setComment('Mageplaza Reward Shopping Cart Label');

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
