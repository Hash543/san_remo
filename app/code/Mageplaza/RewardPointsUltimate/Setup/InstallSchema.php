<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;

/**
 * Class InstallSchema
 * @package Mageplaza\RewardPointsUltimate\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    const MAGEPLAZA_REWARD_BEHAVIOR_TABLE                       = 'mageplaza_reward_behavior';
    const MAGEPLAZA_REWARD_REWARD_BEHAVIOR_WEBSITE_TABLE        = 'mageplaza_reward_behavior_website';
    const MAGEPLAZA_REWARD_REWARD_BEHAVIOR_CUSTOMER_GROUP_TABLE = 'mageplaza_reward_behavior_customer_group';
    const MAGEPLAZA_REWARD_REWARD_INVITATION_TABLE              = 'mageplaza_reward_invitation';
    const MAGEPLAZA_REWARD_REFER_TABLE                          = 'mageplaza_reward_refer';
    const MAGEPLAZA_REWARD_REWARD_REFER_WEBSITE_TABLE           = 'mageplaza_reward_refer_website';
    const MAGEPLAZA_REWARD_REWARD_REFER_CUSTOMER_GROUP_TABLE    = 'mageplaza_reward_refer_customer_group';
    const MAGEPLAZA_REWARD_REWARD_REFERRAL_GROUP_TABLE          = 'mageplaza_reward_referral_group';

    /**
     * @var QuoteSetupFactory
     */
    protected $quoteSetupFactory;

    /**
     * @var SalesSetupFactory
     */
    protected $salesSetupFactory;

    /**
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        QuoteSetupFactory $quoteSetupFactory,
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
    }

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
         * Mageplaza Reward behavior
         */
        if (!$installer->tableExists(self::MAGEPLAZA_REWARD_BEHAVIOR_TABLE)) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable(self::MAGEPLAZA_REWARD_BEHAVIOR_TABLE))
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
                ->addColumn('is_active', Table::TYPE_SMALLINT, null, [
                    'nullable' => false,
                    'default'  => '0',
                ], 'Is Active')
                ->addColumn('sort_order', Table::TYPE_INTEGER, null, [
                    'unsigned' => true,
                    'nullable' => false,
                    'default'  => '0',
                ], 'Sort Order')
                ->addColumn('point_action', Table::TYPE_TEXT, 255, [], 'Point Action')
                ->addColumn('min_words', Table::TYPE_INTEGER, 11, [
                    'unsigned' => true,
                    'nullable' => false,
                ], 'Minimum number of words in the review')
                ->addColumn(
                    'is_purchased',
                    Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Only those who purchased product can earn points'
                )
                ->addColumn(
                    'is_enabled_email',
                    Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Enable email sent to Customers for their birthdays'
                )
                ->addColumn('sender', Table::TYPE_TEXT, 255, [], 'Sender')
                ->addColumn(
                    'email_template',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Email template sent to customers for their birthdays'
                )
                ->addColumn('min_interval', Table::TYPE_INTEGER, null, [
                    'unsigned' => true,
                    'nullable' => false,
                ], 'Minimum interval between Likes')
                ->addColumn('action', Table::TYPE_TEXT, 255, [], 'Action')
                ->addColumn('fb_app_id', Table::TYPE_TEXT, 255, [], 'Facebook app id')
                ->addColumn('point_amount', Table::TYPE_INTEGER, null, [
                    'unsigned' => true,
                    'nullable' => false,
                    'default'  => '0',
                ], 'Point Amount')
                ->addColumn('max_point', Table::TYPE_INTEGER, null, [
                    'unsigned' => true,
                    'nullable' => false,
                    'default'  => '0',
                ], 'Max Earn')
                ->addColumn('max_point_period', Table::TYPE_TEXT, 255, [], 'Max point earn period')
                ->addIndex(
                    $installer->getIdxName(
                        self::MAGEPLAZA_REWARD_BEHAVIOR_TABLE,
                        ['is_active', 'sort_order', 'to_date', 'from_date']
                    ),
                    ['is_active', 'sort_order', 'to_date', 'from_date']
                )
                ->setComment('Mageplaza Reward Behavior');
            $connection->createTable($table);
        }

        /**
         * Create table 'mageplaza_reward_behavior_website' if not exists. This table will be used instead of
         * column website_ids of main catalog rules table
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable(self::MAGEPLAZA_REWARD_REWARD_BEHAVIOR_WEBSITE_TABLE)
        )->addColumn(
            'rule_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Rule Id'
        )->addColumn(
            'website_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Website Id'
        )->addIndex(
            $installer->getIdxName(self::MAGEPLAZA_REWARD_REWARD_BEHAVIOR_WEBSITE_TABLE, ['website_id']),
            ['website_id']
        )->addForeignKey(
            $installer->getFkName(
                self::MAGEPLAZA_REWARD_REWARD_BEHAVIOR_WEBSITE_TABLE,
                'rule_id',
                self::MAGEPLAZA_REWARD_BEHAVIOR_TABLE,
                'rule_id'
            ),
            'rule_id',
            $installer->getTable(self::MAGEPLAZA_REWARD_BEHAVIOR_TABLE),
            'rule_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                self::MAGEPLAZA_REWARD_REWARD_BEHAVIOR_WEBSITE_TABLE,
                'website_id',
                'store_website',
                'website_id'
            ),
            'website_id',
            $installer->getTable('store_website'),
            'website_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Mageplaza Reward Behavior Website Table'
        );

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'mageplaza_reward_behavior_customer_group' if not exists. This table will be used instead of
         * column customer_group_ids of main catalog rules table
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::MAGEPLAZA_REWARD_REWARD_BEHAVIOR_CUSTOMER_GROUP_TABLE))
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
            ->addIndex(
                $installer->getIdxName(self::MAGEPLAZA_REWARD_REWARD_BEHAVIOR_CUSTOMER_GROUP_TABLE, ['rule_id']),
                ['rule_id']
            )
            ->addIndex(
                $installer->getIdxName(
                    self::MAGEPLAZA_REWARD_REWARD_BEHAVIOR_CUSTOMER_GROUP_TABLE,
                    ['customer_group_id']
                ),
                ['customer_group_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    self::MAGEPLAZA_REWARD_REWARD_BEHAVIOR_CUSTOMER_GROUP_TABLE,
                    'rule_id',
                    self::MAGEPLAZA_REWARD_BEHAVIOR_TABLE,
                    'rule_id'
                ),
                'rule_id',
                $installer->getTable(self::MAGEPLAZA_REWARD_BEHAVIOR_TABLE),
                'rule_id',
                Table::ACTION_CASCADE,
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    self::MAGEPLAZA_REWARD_REWARD_BEHAVIOR_CUSTOMER_GROUP_TABLE,
                    'customer_group_id',
                    'customer_group',
                    'customer_group_id'
                ),
                'customer_group_id',
                $installer->getTable('customer_group'),
                'customer_group_id',
                Table::ACTION_CASCADE,
                Table::ACTION_CASCADE
            )
            ->setComment('Mageplaza Reward Behavior Customer Groups');
        $connection->createTable($table);

        /**
         * Mageplaza Reward Invitation
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::MAGEPLAZA_REWARD_REWARD_INVITATION_TABLE))
            ->addColumn('invitation_id', Table::TYPE_INTEGER, null, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true
            ], 'ID')
            ->addColumn('referral_email', Table::TYPE_TEXT, 255, [], 'Referral Email')
            ->addColumn('invited_email', Table::TYPE_TEXT, 255, [], 'Invited Email')
            ->addColumn('referral_earn', Table::TYPE_INTEGER, null, [
                'unsigned' => true,
                'nullable' => false,
            ], 'Referral Earn')
            ->addColumn('invited_earn', Table::TYPE_INTEGER, null, [
                'unsigned' => true,
                'nullable' => false,
            ], 'Invited Earn')
            ->addColumn('invited_discount', Table::TYPE_DECIMAL, '12,4', [
                'unsigned' => true,
                'nullable' => false,
            ], 'Invited Discount')
            ->addColumn('store_id', Table::TYPE_TEXT, '255', [], 'Stores View')
            ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, ['default' => Table::TIMESTAMP_INIT], 'Created At')
            ->setComment('Mageplaza Reward Invitation');
        $connection->createTable($table);

        /**
         * Mageplaza Reward Refer rule
         */
        if (!$installer->tableExists(self::MAGEPLAZA_REWARD_REFER_TABLE)) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable(self::MAGEPLAZA_REWARD_REFER_TABLE))
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
                ->addColumn('is_active', Table::TYPE_SMALLINT, null, [
                    'nullable' => false,
                    'default'  => '0',
                ], 'Is Active')
                ->addColumn('sort_order', Table::TYPE_INTEGER, null, [
                    'unsigned' => true,
                    'nullable' => false,
                    'default'  => '0',
                ], 'Sort Order')
                ->addColumn('conditions_serialized', Table::TYPE_TEXT, '2M', [], 'Conditions Serialized')
                ->addColumn('actions_serialized', Table::TYPE_TEXT, '2M', [], 'Actions Serialized')
                ->addColumn('stop_rules_processing', Table::TYPE_SMALLINT, null, [
                    'nullable' => false,
                    'default'  => '1',
                ], 'Stop Rules Processing')
                ->addColumn('customer_action', Table::TYPE_TEXT, 255, [], 'Customer Action')
                ->addColumn('customer_points', Table::TYPE_INTEGER, 11, [
                    'unsigned' => true,
                    'nullable' => false,
                ], 'Customer Points')
                ->addColumn('customer_money_step', Table::TYPE_INTEGER, 11, [
                    'unsigned' => true,
                    'nullable' => false,
                ], 'Customer Money Step')
                ->addColumn('customer_discount', Table::TYPE_INTEGER, null, [
                    'nullable' => false,
                    'unsigned' => true,
                ], 'Customer Discount Type')
                ->addColumn('customer_apply_to_shipping', Table::TYPE_SMALLINT, null, [
                    'nullable' => false,
                    'default'  => '0',
                ], 'Customer Apply To Shipping')
                ->addColumn('referral_type', Table::TYPE_TEXT, 255, [], 'Referral Type')
                ->addColumn('referral_points', Table::TYPE_INTEGER, 11, [
                    'unsigned' => true,
                    'nullable' => false,
                ], 'Referral Points')
                ->addColumn('referral_money_step', Table::TYPE_INTEGER, 11, [
                    'unsigned' => true,
                    'nullable' => false,
                ], 'Referral Money Step')
                ->addColumn('referral_apply_to_shipping', Table::TYPE_SMALLINT, null, [
                    'nullable' => false,
                    'default'  => '0',
                ], 'Referral Apply To Shipping')
                ->addIndex(
                    $installer->getIdxName(
                        self::MAGEPLAZA_REWARD_REFER_TABLE,
                        ['is_active', 'sort_order', 'to_date', 'from_date']
                    ),
                    ['is_active', 'sort_order', 'to_date', 'from_date']
                )
                ->setComment('Mageplaza Reward Refer');
            $connection->createTable($table);
        }

        /**
         * Mageplaza Reward Refer Website
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable(self::MAGEPLAZA_REWARD_REWARD_REFER_WEBSITE_TABLE)
        )->addColumn(
            'rule_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Rule Id'
        )->addColumn(
            'website_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Website Id'
        )->addIndex(
            $installer->getIdxName(self::MAGEPLAZA_REWARD_REWARD_REFER_WEBSITE_TABLE, ['website_id']),
            ['website_id']
        )->addForeignKey(
            $installer->getFkName(
                self::MAGEPLAZA_REWARD_REWARD_REFER_WEBSITE_TABLE,
                'rule_id',
                self::MAGEPLAZA_REWARD_REFER_TABLE,
                'rule_id'
            ),
            'rule_id',
            $installer->getTable(self::MAGEPLAZA_REWARD_REFER_TABLE),
            'rule_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                self::MAGEPLAZA_REWARD_REWARD_REFER_WEBSITE_TABLE,
                'website_id',
                'store_website',
                'website_id'
            ),
            'website_id',
            $installer->getTable('store_website'),
            'website_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Mageplaza Reward Refer Website Table'
        );

        $connection->createTable($table);

        /**
         * Mageplaza Reward Refer Customer Group
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::MAGEPLAZA_REWARD_REWARD_REFER_CUSTOMER_GROUP_TABLE))
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
            ->addIndex(
                $installer->getIdxName(self::MAGEPLAZA_REWARD_REWARD_REFER_CUSTOMER_GROUP_TABLE, ['rule_id']),
                ['rule_id']
            )
            ->addIndex(
                $installer->getIdxName(self::MAGEPLAZA_REWARD_REWARD_REFER_CUSTOMER_GROUP_TABLE, ['customer_group_id']),
                ['customer_group_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    self::MAGEPLAZA_REWARD_REWARD_REFER_CUSTOMER_GROUP_TABLE,
                    'rule_id',
                    self::MAGEPLAZA_REWARD_REFER_TABLE,
                    'rule_id'
                ),
                'rule_id',
                $installer->getTable(self::MAGEPLAZA_REWARD_REFER_TABLE),
                'rule_id',
                Table::ACTION_CASCADE,
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    self::MAGEPLAZA_REWARD_REWARD_REFER_CUSTOMER_GROUP_TABLE,
                    'customer_group_id',
                    'customer_group',
                    'customer_group_id'
                ),
                'customer_group_id',
                $installer->getTable('customer_group'),
                'customer_group_id',
                Table::ACTION_CASCADE,
                Table::ACTION_CASCADE
            )
            ->setComment('Mageplaza Reward Refer Customer Groups');
        $connection->createTable($table);

        /**
         * Mageplaza Reward Referral Group
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::MAGEPLAZA_REWARD_REWARD_REFERRAL_GROUP_TABLE))
            ->addColumn('rule_id', Table::TYPE_INTEGER, null, [
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true
            ], 'Rule Id')
            ->addColumn(
                'referral_group_id',
                $customerGroupIdType,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Referral Group Id'
            )
            ->addIndex(
                $installer->getIdxName(self::MAGEPLAZA_REWARD_REWARD_REFERRAL_GROUP_TABLE, ['rule_id']),
                ['rule_id']
            )
            ->addIndex(
                $installer->getIdxName(self::MAGEPLAZA_REWARD_REWARD_REFERRAL_GROUP_TABLE, ['referral_group_id']),
                ['referral_group_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    self::MAGEPLAZA_REWARD_REWARD_REFERRAL_GROUP_TABLE,
                    'rule_id',
                    self::MAGEPLAZA_REWARD_REFER_TABLE,
                    'rule_id'
                ),
                'rule_id',
                $installer->getTable(self::MAGEPLAZA_REWARD_REFER_TABLE),
                'rule_id',
                Table::ACTION_CASCADE,
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    self::MAGEPLAZA_REWARD_REWARD_REFERRAL_GROUP_TABLE,
                    'referral_group_id',
                    'customer_group',
                    'customer_group_id'
                ),
                'referral_group_id',
                $installer->getTable('customer_group'),
                'customer_group_id',
                Table::ACTION_CASCADE,
                Table::ACTION_CASCADE
            )
            ->setComment('Mageplaza Reward Referral Groups');
        $connection->createTable($table);
        $installer->endSetup();
    }
}
