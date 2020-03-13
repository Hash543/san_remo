<?php
/**
 * @author Cristian Sanclemente <csanclemente@alfa9.com>
 * @copyright Copyright (c) 2017 Alfa9 (http://www.alfa9.com)
 * @package Alfa9
 */

namespace PSS\CRM\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
class UpgradeSchema implements  UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context){
        $setup->startSetup();
        if (version_compare($context->getVersion(), '0.0.2') < 0) {
            $installer = $setup;
            $table = $installer->getConnection()->newTable(
                $installer->getTable('pss_crm_queue')
            )->addColumn(
                'pss_crm_queue_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [ 'identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true, ],
                'Entity ID'
            )->addColumn(
                'process_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [ 'nullable' => false, ],
                'Process Name'
            )->addColumn(
                'model',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [ 'nullable' => false, ],
                'Model Name'
            )->addColumn(
                'method',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [ 'nullable' => false, ],
                'Method'
            )->addColumn(
                'data',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [ 'nullable' => false, ],
                'Data'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [ 'nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT, ],
                'Created At'
            )->addColumn(
                'executed_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [ 'nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE, ],
                'Executed'
            )->addColumn(
                'result',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [ 'nullable' => false, ],
                'Result'
            );
            $installer->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '0.0.3') < 0) {

            if($setup->getConnection()->tableColumnExists($setup->getTable('pss_crm_queue'), 'process_status') === false) {

                $setup->getConnection()->addColumn(
                    $setup->getTable('pss_crm_queue'),
                    'process_status',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, // Or any other type
                        'nullable' => false,
                        'comment' => 'Item status',
                        'default' => '',
                        'after' => 'result'
                    ]
                );

            }

            if($setup->getConnection()->tableColumnExists($setup->getTable('pss_crm_queue'), 'process_message') === false){

                $setup->getConnection()->addColumn(
                    $setup->getTable('pss_crm_queue'),
                    'process_message',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, // Or any other type
                        'nullable' => false,
                        'comment' => 'Process message',
                        'default' => '',
                        'after' => 'process_status'
                    ]
                );

            }
            }

        $setup->endSetup();
    }
}