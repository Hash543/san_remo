<?php
/**
 * Copyright © 2016 Firebear Studio. All rights reserved.
 */

namespace Firebear\ConfigurableProducts\Setup;

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
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (!$context->getVersion() || version_compare($context->getVersion(), '1.3.0') < 0) {
            /**
             * Create table 'catalog_product_super_link'
             */
            $table = $installer->getConnection()
                ->newTable($installer->getTable('icp_catalog_product_default_super_link'))
                ->addColumn(
                    'link_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Link ID'
                )
                ->addColumn(
                    'product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Product ID'
                )
                ->addColumn(
                    'parent_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Parent ID'
                )
                ->addIndex(
                    $installer->getIdxName('catalog_product_super_link', ['parent_id']),
                    ['parent_id']
                )
                ->addIndex(
                    $installer->getIdxName(
                        'icp_catalog_product_default_super_link',
                        ['product_id', 'parent_id'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['product_id', 'parent_id'],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'icp_catalog_product_default_super_link',
                        'product_id',
                        'catalog_product_entity',
                        'entity_id'
                    ),
                    'product_id',
                    $installer->getTable('catalog_product_entity'),
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'icp_catalog_product_default_super_link',
                        'parent_id',
                        'catalog_product_entity',
                        'entity_id'
                    ),
                    'parent_id',
                    $installer->getTable('catalog_product_entity'),
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->setComment('Firebear ICP Product Default Super Link Table');

            $installer->getConnection()->createTable($table);
        }
        if (!$context->getVersion() || version_compare($context->getVersion(), '1.4.3') < 0) {
             $table = $installer->getConnection()
                ->newTable($installer->getTable('icp_product_attributes'))
                  ->addColumn(
                    'item_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Link ID'
                )->addColumn(
                    'product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Product ID'
                )->addColumn(
                    'color_code',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['unsigned' => true, 'nullable' => true],
                    'Color Code'
                )->addColumn(
                    'size_code',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['unsigned' => true, 'nullable' => true],
                    'Size Code'
                )
                ->setComment('Firebear ICP Product Custom Options Table');
            $installer->getConnection()->createTable($table);
        }
        if (!$context->getVersion() || version_compare($context->getVersion(), '1.4.6') < 0) {
            $installer->getConnection()
                ->addColumn(
                    $installer->getTable('icp_product_attributes'),
                    'linked_attributes',
                    ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'default' => '0',
                        'comment' => 'Linked attributes ids']
                );
        }

        $installer->endSetup();
    }
}
