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
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 * @package Mageplaza\RewardPoints\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $connection = $setup->getConnection();

        if (version_compare($context->getVersion(), '1.1.1', '<')) {
            /** Update Reward Transaction Table */
            $transactionTableName = $setup->getTable('mageplaza_reward_transaction');
            $connection->addColumn($transactionTableName, 'extra_content', [
                'type'     => Table::TYPE_TEXT,
                'length'   => 255,
                'nullable' => true,
                'comment'  => 'Additional Data',
                'after'    => 'expire_email_sent'
            ]);
            $connection->addColumn($transactionTableName, 'point_remaining', [
                'type'     => Table::TYPE_INTEGER,
                'nullable' => false,
                'default'  => 0,
                'comment'  => 'Remaining earned points (order earning point transaction)',
                'after'    => 'point_amount'
            ]);
            $connection->addColumn($transactionTableName, 'point_used', [
                'type'     => Table::TYPE_INTEGER,
                'nullable' => false,
                'default'  => 0,
                'comment'  => 'Point already used (expired transaction)',
                'after'    => 'point_remaining'
            ]);
            $connection->dropColumn($transactionTableName, 'comment');
            $connection->dropColumn($transactionTableName, 'customer_email');
            $connection->dropColumn($transactionTableName, 'notice');
            $connection->dropColumn($transactionTableName, 'point_balance');
            $connection->dropColumn($transactionTableName, 'point_spent');
            $connection->dropColumn($transactionTableName, 'is_locked');
            $connection->dropColumn($transactionTableName, 'lock_changed_date');
            $connection->dropColumn($transactionTableName, 'notification_email');
        }

        if (version_compare($context->getVersion(), '1.2.1', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order_grid'),
                'mp_reward_earn',
                ['type' => Table::TYPE_DECIMAL, 'nullable' => true, 'comment' => 'MP reward earned']
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order_grid'),
                'mp_reward_spent',
                ['type' => Table::TYPE_DECIMAL, 'nullable' => true, 'comment' => 'MP reward spent']
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('customer_grid_flat'),
                'mp_point_balance',
                ['type' => Table::TYPE_DECIMAL, 'nullable' => true, 'comment' => 'MP point balance']
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('mageplaza_reward_customer'),
                'is_active',
                ['type'     => Table::TYPE_BOOLEAN,
                 'nullable' => false,
                 'default'  => 1,
                 'comment'  => 'Customer Reward Enable'
                ]
            );
        }

        $setup->endSetup();
    }
}
