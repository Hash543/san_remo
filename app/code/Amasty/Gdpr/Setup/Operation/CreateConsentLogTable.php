<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Gdpr\Api\Data\WithConsentInterface;

class CreateConsentLogTable
{
    const TABLE_NAME = 'amasty_gdpr_consent_log';

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
                WithConsentInterface::ID,
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
                WithConsentInterface::CUSTOMER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Customer Id'
            )->addColumn(
                WithConsentInterface::DATE_CONSENTED,
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default'  => Table::TIMESTAMP_INIT
                ],
                'Date of consent'
            )->addColumn(
                WithConsentInterface::POLICY_VERSION,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ],
                'Policy Version'
            )->addForeignKey(
                $setup->getFkName(
                    $table,
                    WithConsentInterface::CUSTOMER_ID,
                    $customerTable,
                    'entity_id'
                ),
                WithConsentInterface::CUSTOMER_ID,
                $customerTable,
                'entity_id',
                Table::ACTION_CASCADE
            );
    }
}
