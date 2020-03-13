<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Gdpr\Api\Data\DeleteRequestInterface;

class CreateDeleteRequestTable
{
    const TABLE_NAME = 'amasty_gdpr_delete_request';

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
                'Amasty GDPR Delete Request Table'
            )->addColumn(
                DeleteRequestInterface::ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ],
                'Request Id'
            )->addColumn(
                DeleteRequestInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default'  => Table::TIMESTAMP_INIT
                ],
                'Created At'
            )->addColumn(
                DeleteRequestInterface::CUSTOMER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Customer Id'
            )->addForeignKey(
                $setup->getFkName(
                    $table,
                    DeleteRequestInterface::CUSTOMER_ID,
                    $customerTable,
                    'entity_id'
                ),
                DeleteRequestInterface::CUSTOMER_ID,
                $customerTable,
                'entity_id',
                Table::ACTION_CASCADE
            );
    }
}
