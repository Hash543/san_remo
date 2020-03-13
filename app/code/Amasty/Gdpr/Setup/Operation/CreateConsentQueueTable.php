<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Setup\Operation;

use Amasty\Gdpr\Api\Data\ConsentQueueInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Amasty\Gdpr\Model\ConsentQueue;

class CreateConsentQueueTable
{
    const TABLE_NAME = 'amasty_gdpr_consent_queue';

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
                'Amasty GDPR Table with consent email queue'
            )->addColumn(
                ConsentQueueInterface::ID,
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
                ConsentQueueInterface::CUSTOMER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Customer Id'
            )->addColumn(
                ConsentQueueInterface::STATUS,
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false,
                    'default' => ConsentQueue::STATUS_PENDING
                ],
                'Status'
            )->addIndex(
                $setup->getIdxName(
                    $table,
                    [ConsentQueueInterface::CUSTOMER_ID],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                [ConsentQueueInterface::CUSTOMER_ID],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )->addForeignKey(
                $setup->getFkName(
                    $table,
                    ConsentQueueInterface::CUSTOMER_ID,
                    $customerTable,
                    'entity_id'
                ),
                ConsentQueueInterface::CUSTOMER_ID,
                $customerTable,
                'entity_id',
                Table::ACTION_CASCADE
            );
    }
}
