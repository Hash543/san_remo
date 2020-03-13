<?php

namespace Alfa9\StoreInfo\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.Generic.CodeAnalysis.UnusedFunctionParameter)
     */

    // @codingStandardsIgnoreStart
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $tableName = $installer->getTable('alfa9_storeinfo_store');
        if (!$installer->tableExists($tableName)) {
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'storeinfo_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'Storeinfo ID'
                )->addColumn(
                    'store_id',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                    ],
                    'Store ID'
                )->addColumn(
                    'status', // = enable
                    Table::TYPE_SMALLINT,
                    null,
                    [
                        'nullable' => false,
                        'default' => '1',
                    ],
                    'Status'
                )->addColumn(
                    'id_sr',
                    Table::TYPE_INTEGER,
                    null,
                    [],
                    'ERP Id'
                )->addColumn(
                    'name',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false,],
                    'Name'
                )->addColumn(
                    'link', // maybe remove later
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Link'
                )->addColumn(
                    'address',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false,],
                    'Store Address'
                )->addColumn(
                    'postcode',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Postcode'
                )->addColumn(
                    'city',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'City'
                )->addColumn(
                    'region', // province
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Region'
                )->addColumn(
                    'country', // maybe remove later
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Country'
                )->addColumn(
                    'phone',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Phone'
                )->addColumn(
                    'email',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Email'
                )->addColumn(
                    'email_order',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Email Order'
                )->addColumn(
                    'latitude',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Latitude'
                )->addColumn(
                    'longitude',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Longitude'
                )->addColumn(
                    'schedule',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Schedule Timetable'
                )->addColumn(
                    'description', // WYSIWYG
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Description'
                )->addColumn(
                    'services', // WYSIWYG
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Services'
                )->addColumn(
                    'image',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Image'
                )->addColumn(
                    'image2',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Image 2'
                )->addColumn(
                    'image3',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Image 3'
                )->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'Created At'
                )->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'Updated At'
                )->setComment('List of stores');
                /** ->addColumn(
                    'details_image', // maybe remove later
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Details Image'
                )->addColumn(
                    'intro', // maybe remove later
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Intro'
                )->addColumn(
                    'station', // maybe remove later
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Nearest Station'
                )->addColumn(
                    'distance', // maybe remove later
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Distance'
                )->addColumn(
                    'link', // maybe remove later
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Link'
                )->addColumn(
                    'external_link', // maybe remove later
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'External Link'
                )*/


            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addIndex(
                $installer->getTable('alfa9_storeinfo_store'),
                $setup->getIdxName(
                    $installer->getTable('alfa9_storeinfo_store'),
                    ['name'],
                    AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                [
                    'name',
                    'address',
                    'city',
                    'country',
                    'region',
                    'email',
                    'postcode',
                    'phone',
                    'latitude',
                    'longitude'
                ],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            );

            $installer->endSetup();

        }
    }
}
