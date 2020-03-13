<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Setup\Operation;

use Amasty\Gdpr\Api\Data\ActionLogInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateActionLogTable
{
    const TABLE_NAME = 'amasty_gdpr_action_log';

    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->createTable(
            $this->createTable($setup)
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @return Table
     * @throws \Zend_Db_Exception
     */
    private function createTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable(self::TABLE_NAME);
        $customerTable = $setup->getTable('customer_entity');

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty GDPR Table with consent customers'
            )->addColumn(
                ActionLogInterface::ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ],
                'Id'
            )->addColumn(
                ActionLogInterface::CUSTOMER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => true
                ],
                'Customer Id'
            )->addColumn(
                ActionLogInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default'  => Table::TIMESTAMP_INIT
                ],
                'Date of logging'
            )->addColumn(
                ActionLogInterface::IP,
                Table::TYPE_TEXT,
                127,
                [
                    'nullable' => false
                ],
                'Remote Ip Address'
            )->addColumn(
                ActionLogInterface::ACTION,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ],
                'Performed Action'
            )->addForeignKey(
                $setup->getFkName(
                    $table,
                    ActionLogInterface::CUSTOMER_ID,
                    $customerTable,
                    'entity_id'
                ),
                ActionLogInterface::CUSTOMER_ID,
                $customerTable,
                'entity_id',
                Table::ACTION_CASCADE
            );
    }
}
