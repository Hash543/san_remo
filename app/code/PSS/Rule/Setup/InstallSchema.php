<?php
/**
 * @author Israel Yasis
 */
namespace PSS\Rule\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface {

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $setup->startSetup();
        $connection = $setup->getConnection();
        $salesRuleTable = $setup->getTable('salesrule');
        $salesRuleCoupon = $setup->getTable('salesrule_coupon');
        if($connection->isTableExists($salesRuleTable) == true) {
            $columns = [
                \PSS\Rule\Helper\Data::CRM_ID => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 100,
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'CRM Id',
                ],
                \PSS\Rule\Helper\Data::ERP_ID => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 100,
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'ERP Id',
                ],
                'banner' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'Banner',
                ],
                'url_banner' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'Url Banner',
                ],
                'marketing_list' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '1M',
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'Marketing Lists',
                ],
            ];
            foreach ($columns as $name => $definition) {
                $connection->addColumn($salesRuleTable, $name, $definition);
            }
        }
        if($connection->isTableExists($salesRuleCoupon)) {
            $columns = [
                \PSS\Rule\Helper\DATA::CUSTOMER_ID => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'default' => 0,
                    'comment' => 'Client Id',
                ],
                \PSS\Rule\Helper\DATA::ID_TICKET_ORIGIN => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'Id Ticket Origin',
                ],
                \PSS\Rule\Helper\DATA::ID_TICKET_DESTINY => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'Id Ticket Destiny',
                ],
            ];
            foreach ($columns as $name => $definition) {
                $connection->addColumn($salesRuleCoupon, $name, $definition);
            }
        }
        $setup->endSetup();
    }
}
