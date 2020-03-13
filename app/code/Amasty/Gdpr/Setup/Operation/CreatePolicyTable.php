<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Amasty\Gdpr\Api\Data\PolicyInterface;

class CreatePolicyTable
{
    const TABLE_NAME = 'amasty_gdpr_privacy_policy';

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
        $adminTable = $setup->getTable('admin_user');

        return $setup->getConnection()
            ->newTable(
                $table
            )->setComment(
                'Amasty GDPR Table with consent customers'
            )->addColumn(
                PolicyInterface::ID,
                Table::TYPE_SMALLINT,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true
                ],
                'Id'
            )->addColumn(
                PolicyInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default'  => Table::TIMESTAMP_INIT
                ],
                'Created At'
            )->addColumn(
                PolicyInterface::UPDATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default'  => Table::TIMESTAMP_INIT_UPDATE
                ],
                'Updated at'
            )->addColumn(
                PolicyInterface::POLICY_VERSION,
                Table::TYPE_TEXT,
                10,
                [
                    'nullable' => false
                ],
                'Policy Version'
            )->addColumn(
                PolicyInterface::CONTENT,
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => false
                ],
                'Policy Content'
            )->addColumn(
                PolicyInterface::LAST_EDITED_BY,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => true
                ],
                'Last Edited By'
            )->addColumn(
                PolicyInterface::COMMENT,
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ],
                'Comment'
            )->addColumn(
                PolicyInterface::STATUS,
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false
                ],
                'Status'
            )->addForeignKey(
                $setup->getFkName(
                    $table,
                    PolicyInterface::LAST_EDITED_BY,
                    $adminTable,
                    'user_id'
                ),
                PolicyInterface::LAST_EDITED_BY,
                $adminTable,
                'user_id',
                Table::ACTION_SET_NULL
            );
    }
}
