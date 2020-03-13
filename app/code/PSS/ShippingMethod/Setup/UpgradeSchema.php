<?php
/**
 * @author Israel Yasis
 */
namespace PSS\ShippingMethod\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class Upgrade
 * @package PSS\ShippingMethod\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface {

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();
        /** Add a new column to stock express */
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $tableName = $setup->getTable('amasty_table_method');
            if ($installer->getConnection()->isTableExists($tableName) == true) {
                $installer->getConnection()->addColumn(
                    $tableName,
                    'stock_express',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                        'length'   => null,
                        'nullable' => true,
                        'default'  => false,
                        'comment' => 'Apply to Stock Express'
                    ]
                );
            }
        }
        $installer->endSetup();
    }
}