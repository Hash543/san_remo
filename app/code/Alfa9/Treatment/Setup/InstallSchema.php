<?php

namespace Alfa9\Treatment\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use \Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $table = $setup->getConnection()->newTable(
            $setup->getTable('a9_treatment_days')
        )->addColumn(
            'treatment_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Custom Id'
        )->addColumn(
            'customer_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true],
            'Customer id'
        )->addColumn(
            'customer_email',
            Table::TYPE_TEXT,
            128,
            [],
            'Customer Email'
        )->addColumn(
            'product_sku',
            Table::TYPE_TEXT,
            64,
            [],
            'Product sku'
        )->addColumn(
            'delivery_days',
            Table::TYPE_DATE,
            null,
            [],
            'Delivery days'
        )->setComment(
            'Treatment Table'
        );
        $setup->getConnection()->createTable($table);

        $setup->endSetup();
    }
}