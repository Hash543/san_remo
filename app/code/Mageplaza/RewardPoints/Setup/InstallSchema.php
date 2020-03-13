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
 * @package     Mageplaza_RewardPoints
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPoints\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @package Mageplaza\RewardPoints\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
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

        if (!$installer->tableExists('mageplaza_reward_rate')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_reward_rate'))
                ->addColumn(
                    'rate_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
                    'Template Id'
                )
                ->addColumn('website_ids', Table::TYPE_TEXT, 255, ['nullable' => false], 'Website Ids')
                ->addColumn('customer_group_ids', Table::TYPE_TEXT, 64, ['nullable' => false], 'Customer Group Ids')
                ->addColumn(
                    'direction',
                    Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'default' => '1'],
                    'Direction'
                )
                ->addColumn('points', Table::TYPE_INTEGER, null, ['nullable => false'], 'Points')
                ->addColumn('money', Table::TYPE_DECIMAL, '12,4', ['default' => '0.0000'], 'Money')
                ->addColumn('priority', Table::TYPE_INTEGER, null, [], 'Priority')
                ->setComment('Mageplaza Reward Rate');
            $connection->createTable($table);
        }

        if (!$installer->tableExists('mageplaza_reward_customer')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_reward_customer'))
                ->addColumn(
                    'reward_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
                    'Reward Id'
                )
                ->addColumn(
                    'customer_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Customer Id'
                )
                ->addColumn('point_balance', Table::TYPE_INTEGER, null, ['nullable' => false], 'Point Balance')
                ->addColumn('point_spent', Table::TYPE_INTEGER, null, ['nullable' => false], 'Point spent')
                ->addColumn(
                    'notification_update',
                    Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Notification Update'
                )
                ->addColumn(
                    'notification_expire',
                    Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Notification Expire'
                )
                ->addForeignKey(
                    $installer->getFkName('mageplaza_reward_customer', 'customer_id', 'customer_entity', 'entity_id'),
                    'customer_id',
                    $installer->getTable('customer_entity'),
                    'entity_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('Mageplaza Reward Customer');
            $connection->createTable($table);
        }

        if (!$installer->tableExists('mageplaza_reward_transaction')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_reward_transaction'))
                ->addColumn(
                    'transaction_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
                    'Reward Id'
                )
                ->addColumn(
                    'reward_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Reward Id'
                )
                ->addColumn('customer_id', Table::TYPE_INTEGER, null, ['nullable' => false], 'Customer Id')
                ->addColumn('action_code', Table::TYPE_TEXT, 255, ['nullable' => false], 'Action Code')
                ->addColumn(
                    'action_type',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Action Type'
                )
                ->addColumn('store_id', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '0'], 'Store Id')
                ->addColumn('point_amount', Table::TYPE_INTEGER, null, ['nullable' => false], 'Point Balance')
                ->addColumn('status', Table::TYPE_TEXT, 32, [], 'Status')
                ->addColumn('order_id', Table::TYPE_INTEGER, null, ['nullable' => false], 'Order Id')
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['default' => Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    'expiration_date',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['default' => Table::TIMESTAMP_INIT],
                    'Expiration Date'
                )
                ->addColumn('expire_email_sent', Table::TYPE_INTEGER, null, ['nullable' => false], 'Expire Email Sent')
                ->addColumn('customer_email', Table::TYPE_TEXT, 255, ['nullable' => false], 'Customer Email')
                ->addColumn('point_spent', Table::TYPE_INTEGER, null, ['nullable' => false], 'Points already used')
                ->addColumn('point_balance', Table::TYPE_INTEGER, null, ['nullable' => false], 'Point Balance')
                ->addColumn('is_locked', Table::TYPE_BOOLEAN, null, ['nullable' => false, 'default' => 0], 'Is Locked')
                ->addColumn(
                    'lock_changed_date',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'lock_changed_date'
                )
                ->addColumn(
                    'notification_email',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'notification_email'
                )
                ->addColumn('comment', Table::TYPE_TEXT, 255, ['nullable' => false], 'Notice')
                ->addColumn('notice', Table::TYPE_TEXT, 255, [], 'Notice')
                ->addForeignKey(
                    $installer->getFkName(
                        'mageplaza_reward_transaction',
                        'reward_id',
                        'mageplaza_reward_customer',
                        'reward_id'
                    ),
                    'reward_id',
                    $installer->getTable('mageplaza_reward_customer'),
                    'reward_id',
                    Table::ACTION_CASCADE
                )
                ->setComment('Mageplaza Reward Transaction');
            $connection->createTable($table);
        }
        $installer->endSetup();
    }
}
