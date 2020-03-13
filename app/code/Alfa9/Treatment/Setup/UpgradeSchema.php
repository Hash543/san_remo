<?php
/**
 * Copyright © 2016 Firebear Studio. All rights reserved.
 */

namespace Alfa9\Treatment\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (!$context->getVersion() || version_compare($context->getVersion(), '1.0.1') < 0) {
            $installer->getConnection()
                ->addColumn(
                    $installer->getTable('a9_treatment_days'),
                    'email_poll_sent',
                    [
                        'type'     => Table::TYPE_BOOLEAN,
                        'length'   => null,
                        'nullable' => false,
                        'default'  => false,
                        'comment'  => 'Poll email has already been sent'
                    ]
                )->addColumn(
                    $installer->getTable('a9_treatment_days'),
                    'email_tips_sent',
                    [
                        'type'     => Table::TYPE_BOOLEAN,
                        'length'   => null,
                        'nullable' => false,
                        'default'  => false,
                        'comment'  => 'Tips email has already been sent'
                    ]
                )->addColumn(
                    $installer->getTable('a9_treatment_days'),
                    'email_reminder_sent',
                    [
                        'type'     => Table::TYPE_BOOLEAN,
                        'length'   => null,
                        'nullable' => false,
                        'default'  => false,
                        'comment'  => 'Reminder email has already been sent'
                    ]
                );
        }

        $installer->endSetup();
    }
}
