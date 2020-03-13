<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Setup\Operation;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Amasty\Gdpr\Api\Data\WithConsentInterface;

class UpgradeTo130
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $consentLogTable = $setup->getTable(CreateConsentLogTable::TABLE_NAME);
        $websiteTable = $setup->getTable('store_website');

        $setup->getConnection()->addColumn(
            $consentLogTable,
            WithConsentInterface::GOT_FROM,
            [
                'type' => Table::TYPE_TEXT,
                'size' => 20,
                'default' => null,
                'nullable' => true,
                'comment' => 'Place where got consent'
            ]
        );

        $setup->getConnection()->addColumn(
            $consentLogTable,
            WithConsentInterface::WEBSITE_ID,
            [
                'type' => Table::TYPE_SMALLINT,
                'size' => null,
                'default' => null,
                'nullable' => true,
                'unsigned' => true,
                'comment' => 'Website ID'
            ]
        );

        $setup->getConnection()->addColumn(
            $consentLogTable,
            WithConsentInterface::IP,
            [
                'type' => Table::TYPE_TEXT,
                'size' => 127,
                'default' => null,
                'nullable' => true,
                'comment' => 'Remote IP Address'
            ]
        );

        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                $consentLogTable,
                WithConsentInterface::WEBSITE_ID,
                $websiteTable,
                'website_id'
            ),
            $consentLogTable,
            WithConsentInterface::WEBSITE_ID,
            $websiteTable,
            'website_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
        );
    }
}
