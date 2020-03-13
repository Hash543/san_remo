<?php

namespace Alfa9\StorePickup\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {

        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();
        if ($connection->tableColumnExists('sales_order', 'order_state_pickup') === false) {
            $connection
                ->addColumn(
                    $setup->getTable('sales_order'),
                    'order_state_pickup',
                    [
                        'type' => Table::TYPE_DATETIME,
                        'default' => Null,
                        'comment' => 'Order state pickup'
                    ]
                );
        }
        $installer->endSetup();
    }
}