<?php
/**
 * Copyright © 2016 Firebear Studio. All rights reserved.
 */

namespace Alfa9\StoreInfo\Setup;

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
                    $installer->getTable('alfa9_storeinfo_store'),
                    'shipping_time',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'default' => NULL,
                        'comment' => 'Shipping Time'
                    ]
                );
        }

        $installer->endSetup();
    }
}
