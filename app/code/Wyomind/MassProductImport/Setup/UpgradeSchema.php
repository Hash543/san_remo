<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Upgrade schema for Simple Google Shopping
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    )
    {


        $installer = $setup;

        if (version_compare($context->getVersion(), '1.3.0') < 0) {
            $installer = $setup;
            $installer->startSetup();


            $tableName = $installer->getTable('massproductimport_profiles');
            // webservice
            $setup->getConnection()->addColumn(
                $tableName,
                'product_removal',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, "default" => 0, "comment" => 'Product removal option']
            );
            $setup->getConnection()->addColumn(
                $tableName,
                'create_configurable_onthefly',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, "default" => 0, "comment" => 'Create Configurable Product on the fly']
            );


            $installer->endSetup();
        }
        if (version_compare($context->getVersion(), '1.6.0') < 0) {
            $installer = $setup;
            $installer->startSetup();


            $tableName = $installer->getTable('massproductimport_profiles');
            // webservice
            $setup->getConnection()->addColumn(
                $tableName,
                'xml_column_mapping',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, "comment" => 'Xml columns order']
            );
            $setup->getConnection()->addColumn(
                $tableName,
                'preserve_xml_column_mapping',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, "default" => 0, "comment" => 'Preserve the xml column order']
            );

            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.7.0') < 0) {
            $installer = $setup;
            $installer->startSetup();


            $tableName = $installer->getTable('massproductimport_profiles');

            $setup->getConnection()->dropColumn(
                $tableName,
                'category_mapping'
            );
            $setup->getConnection()->addColumn(
                $tableName,
                'create_category_onthefly',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, "default" => 0, "comment" => 'Allow categories to be created on the fly']
            );
            $setup->getConnection()->addColumn(
                $tableName,
                'category_is_active',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, "default" => 0, "comment" => 'Default value  for is_active']
            );
            $setup->getConnection()->addColumn(
                $tableName,
                'category_include_in_menu',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, "default" => 0, "comment" => 'Default value for include in menu']
            );
            $setup->getConnection()->addColumn(
                $tableName,
                'category_parent_id',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 11, 'nullable' => false, "default" => 0, "comment" => 'Default parent category']
            );

            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '2.2.0') < 0) {
            $installer = $setup;
            $installer->startSetup();
            $tableName = $installer->getTable('massproductimport_profiles');
            $setup->getConnection()->addColumn(
                $tableName,
                'dropbox_token',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => 300, 'nullable' => false, "comment" => 'Dropbox token']
            );
            $setup->getConnection()->addColumn(
                $tableName,
                'line_filter',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => 300, 'nullable' => false, "comment" => 'Line filter']
            );
            $setup->getConnection()->addColumn(
                $tableName,
                'has_header',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, "default" => 0, "comment" => 'Has header']
            );
            $setup->getConnection()->addColumn(
                $tableName,
                'tree_detection',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, "default" => 0, "comment" => 'Tree detection']
            );
            $setup->getConnection()->addColumn(
                $tableName,
                'product_target',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, "default" => 0, "comment" => 'Action type when product removal is enabled']
            );

            $tableName = $setup->getTable('catalog_product_entity');
            $setup->getConnection()->addColumn(
                $tableName,
                'created_by',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 11, 'nullable' => true, "default" => null, "comment" => 'Created by Mass Product Import profiles id']
            );
            $setup->getConnection()->addColumn(
                $tableName,
                'updated_by',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 11, 'nullable' => true, "default" => null, "comment" => 'Created by Mass Product Import profiles id']
            );
            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '2.3.0') < 0) {
            $installer = $setup;
            $installer->startSetup();
            $tableName = $installer->getTable('massproductimport_profiles');
            $setup->getConnection()->addColumn(
                $tableName,
                'post_process_action',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, 'default' => 0, "comment" => 'Post process action']
            );
            $setup->getConnection()->addColumn(
                $tableName,
                'post_process_move_folder',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => 300, 'nullable' => true, "comment" => 'Post process: move folder']
            );

            $installer->endSetup();
        }
        if (version_compare($context->getVersion(), '3.2.2') < 0) {
            $installer = $setup;
            $installer->startSetup();

            $tableName = $installer->getTable('massproductimport_profiles');

            $setup->getConnection()->addColumn(
                $tableName,
                'post_process_indexers',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, 'default' => 1, "comment" => 'Run indexes after import']
            );


            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '3.2.2.1') < 0) {
            $installer = $setup;
            $installer->startSetup();
            $tableName = $installer->getTable('massproductimport_profiles');

            $setup->getConnection()->addColumn(
                $tableName,
                'identifier_script',
                ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'length' => 900, 'nullable' => true, "comment" => 'Script for the identifier']
            );

            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '4.0.0') < 0) {
            $installer = $setup;
            $installer->startSetup();
            $tableName = $installer->getTable('massproductimport_profiles');

            $setup->getConnection()->dropColumn(
                $tableName,
                'auto_set_total'
            );

            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '4.3.0') < 0) {
            $installer = $setup;
            $installer->startSetup();
            $tableName = $installer->getTable('massproductimport_profiles');


            $setup->getConnection()->addColumn(
                $tableName, 'is_magento_export', ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, 'default' => 2, "comment" => 'Magento export file']
            );

            $installer->endSetup();
        }
        if (version_compare($context->getVersion(), '4.3.0') < 0) {
            $installer = $setup;
            $installer->startSetup();
            $tableName = $installer->getTable('massproductimport_profiles');


            $setup->getConnection()->addColumn(
                $tableName, 'is_magento_export', ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 'length' => 1, 'nullable' => false, 'default' => 2, "comment" => 'Magento export file']
            );

            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '4.4.0') < 0) {
            $installer = $setup;
            $installer->startSetup();
            $tableName = $installer->getTable('massproductimport_profiles');




            $installer->endSetup();
        }
    }
}
