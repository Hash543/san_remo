<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 *
 * This package designed for Magento COMMUNITY edition
 * PSS Digital does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * PSS Digital does not provide extension support in case of * incorrect edition usage.
 *
 * @author PSS Digital Team
 * @category PSS
 * @package PSS_WordPress
 * @copyright Copyright (c) 2018 PSS (https://www.pss-ti.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace PSS\WordPress\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallSchema implements InstallSchemaInterface {

    const TABLE_WORDPRESS_ASSOCIATION_TYPE = 'wordpress_association_type';
    const TABLE_WORDPRESS_ASSOCIATION = 'wordpress_association';
    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context){
        $installer = $setup;
        $installer->startSetup();
        /** Install the Association Wordpress Type Table */
        if(!$installer->getConnection()->isTableExists(self::TABLE_WORDPRESS_ASSOCIATION_TYPE)){
            $tableName = $installer->getTable(self::TABLE_WORDPRESS_ASSOCIATION_TYPE);
            $table = $installer->getConnection()->newTable($tableName);
            $table->addColumn(
                'type_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Association Type Id'
            )->addColumn(
                'object',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                16,
                [],
                'Object'
            )->addColumn(
                'wordpress_object',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                16,
                [],
                'Wordpress Object'
            );
            $installer->getConnection()->createTable($table);
        }

        /** Install the Association Wordpress Table */
        if (!$installer->getConnection()->isTableExists(self::TABLE_WORDPRESS_ASSOCIATION)) {
            $tableName = $installer->getTable(self::TABLE_WORDPRESS_ASSOCIATION);
            $table = $installer->getConnection()->newTable($tableName);
            $table->addColumn(
                'assoc_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Association id'
            )->addColumn(
                'type_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Type Id'
            )->addColumn(
                'object_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Object Id'
            )->addColumn(
                'wordpress_object_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Wordpress Object Id'
            )->addColumn(
                'position',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Position'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Store Id'
            )->addForeignKey(
                $installer->getFkName(
                    self::TABLE_WORDPRESS_ASSOCIATION,
                    'type_id',
                    self::TABLE_WORDPRESS_ASSOCIATION_TYPE,
                    'type_id'),
                'type_id',
                $installer->getTable(self::TABLE_WORDPRESS_ASSOCIATION_TYPE),
                'type_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(self::TABLE_WORDPRESS_ASSOCIATION, 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }
}