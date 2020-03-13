<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPoints
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPoints\Setup;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Filesystem;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;
use Mageplaza\RewardPoints\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Class UpgradeData
 * @package Mageplaza\RewardPoints\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var SalesSetupFactory
     */
    protected $salesSetupFactory;

    /**
     * @var QuoteSetupFactory
     */
    protected $quoteSetupFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * UpgradeData constructor.
     *
     * @param SalesSetupFactory $salesSetupFactory
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory,
        QuoteSetupFactory $quoteSetupFactory,
        LoggerInterface $logger
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     *
     * @throws \Exception
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            /** @var \Magento\Sales\Setup\SalesSetup $salesInstaller */
            $salesInstaller = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $setup]);
            $salesInstaller->addAttribute(
                'order',
                'mp_reward_earn_after_invoice',
                ['type' => Table::TYPE_INTEGER, 'visible' => false]
            );
        }

        if (version_compare($context->getVersion(), '1.2.0', '<')) {
            $this->copyIconDefault();
            $this->updateTransactionData($installer);
            $this->updateSalesRewardColumns($installer);
        }

        if (version_compare($context->getVersion(), '1.2.1', '<')) {
            $sales_order_grid = $setup->getTable('sales_order_grid');
            $sales_order = $setup->getTable('sales_order');

            $select = $installer->getConnection()->select();
            $select->from($sales_order);
            $select->reset(\Zend_Db_Select::COLUMNS);
            $select->columns(['mp_reward_spent', 'mp_reward_earn']);
            $select->where("{$sales_order_grid}.entity_id = {$sales_order}.entity_id");
            $query = $installer->getConnection()->updateFromSelect($select, $sales_order_grid);

            $installer->getConnection()->query($query);
        }

        $installer->endSetup();
    }

    /**
     * @param ModuleDataSetupInterface $installer
     */
    protected function updateSalesRewardColumns($installer)
    {
        try {
            $quoteColumnsDelete = [
                ['table' => 'quote_address', 'column' => 'mp_reward_earn'],
                ['table' => 'quote_address', 'column' => 'mp_reward_spent'],
                ['table' => 'quote_address', 'column' => 'mp_reward_base_discount'],
                ['table' => 'quote_address', 'column' => 'mp_reward_discount'],
                ['table' => 'quote_item', 'column' => 'mp_reward_earn'],
                ['table' => 'quote_item', 'column' => 'mp_reward_spent'],
                ['table' => 'quote_item', 'column' => 'mp_reward_base_discount'],
                ['table' => 'quote_item', 'column' => 'mp_reward_discount'],
                ['table' => 'quote', 'column' => 'mp_reward_shipping_earn'],
                ['table' => 'quote', 'column' => 'mp_reward_shipping_spent'],
                ['table' => 'quote', 'column' => 'mp_reward_shipping_base_discount'],
                ['table' => 'quote', 'column' => 'mp_reward_shipping_discount'],
                ['table' => 'sales_order', 'column' => 'mp_reward_earn_invoiced'],
                ['table' => 'sales_order', 'column' => 'mp_reward_spent_invoiced'],
                ['table' => 'sales_order', 'column' => 'mp_reward_discount_invoiced'],
                ['table' => 'sales_order', 'column' => 'mp_reward_base_discount_invoiced'],
                ['table' => 'sales_order', 'column' => 'mp_reward_shipping_earn_invoiced'],
                ['table' => 'sales_order', 'column' => 'mp_reward_shipping_spent_invoiced'],
                ['table' => 'sales_order', 'column' => 'mp_reward_shipping_discount_invoiced'],
                ['table' => 'sales_order', 'column' => 'mp_reward_shipping_base_discount_invoiced'],
                ['table' => 'sales_order', 'column' => 'mp_reward_earn_refunded'],
                ['table' => 'sales_order', 'column' => 'mp_reward_spent_refunded'],
                ['table' => 'sales_order', 'column' => 'mp_reward_discount_refunded'],
                ['table' => 'sales_order', 'column' => 'mp_reward_base_discount_refunded'],
                ['table' => 'sales_order', 'column' => 'mp_reward_shipping_earn_refunded'],
                ['table' => 'sales_order', 'column' => 'mp_reward_shipping_spent_refunded'],
                ['table' => 'sales_order', 'column' => 'mp_reward_shipping_discount_refunded'],
                ['table' => 'sales_order', 'column' => 'mp_reward_shipping_base_discount_refunded'],
            ];
            foreach ($quoteColumnsDelete as $item) {
                $installer->getConnection()->dropColumn(
                    $installer->getTable($item['table']),
                    $item['column']
                );
            }

            $columnsUpdateType = [
                ['table' => 'quote', 'column' => 'mp_reward_earn'],
                ['table' => 'quote', 'column' => 'mp_reward_spent'],
                ['table' => 'sales_order', 'column' => 'mp_reward_earn'],
                ['table' => 'sales_order', 'column' => 'mp_reward_spent'],
                ['table' => 'sales_order_item', 'column' => 'mp_reward_earn'],
                ['table' => 'sales_order_item', 'column' => 'mp_reward_spent'],
                ['table' => 'sales_invoice', 'column' => 'mp_reward_earn'],
                ['table' => 'sales_invoice', 'column' => 'mp_reward_spent'],
                ['table' => 'sales_invoice_item', 'column' => 'mp_reward_earn'],
                ['table' => 'sales_invoice_item', 'column' => 'mp_reward_spent'],
                ['table' => 'sales_creditmemo', 'column' => 'mp_reward_earn'],
                ['table' => 'sales_creditmemo', 'column' => 'mp_reward_spent'],
                ['table' => 'sales_creditmemo_item', 'column' => 'mp_reward_earn'],
                ['table' => 'sales_creditmemo_item', 'column' => 'mp_reward_spent'],
                ['table' => 'sales_order', 'column' => 'mp_reward_shipping_earn'],
                ['table' => 'sales_order', 'column' => 'mp_reward_shipping_spent'],
            ];
            foreach ($columnsUpdateType as $item) {
                $installer->getConnection()->modifyColumn(
                    $installer->getTable($item['table']),
                    $item['column'],
                    ['type' => Table::TYPE_INTEGER, 'visible' => false]
                );
            }

            /** @var \Magento\Quote\Setup\QuoteSetup $quoteInstaller */
            $quoteInstaller = $this->quoteSetupFactory->create([
                'resourceName' => 'quote_setup',
                'setup'        => $installer
            ]);
            $quoteInstaller->addAttribute(
                'quote',
                'mp_reward_applied',
                ['type' => Table::TYPE_TEXT, 'length' => 32, 'visible' => false]
            );
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * Copy icon default to media path
     */
    protected function copyIconDefault()
    {
        try {
            /** @var Filesystem\Directory\WriteInterface $mediaDirectory */
            $mediaDirectory = ObjectManager::getInstance()->get(\Magento\Framework\Filesystem::class)
                ->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);

            $mediaDirectory->create('mageplaza/rewardpoints/default');
            $targetPath = $mediaDirectory->getAbsolutePath('mageplaza/rewardpoints/default/point.png');

            $DS = DIRECTORY_SEPARATOR;
            $oriPath = dirname(__DIR__) . $DS . 'view' . $DS . 'frontend' . $DS . 'web' . $DS . 'images' . $DS . 'default' . $DS . 'point.png';

            $mediaDirectory->getDriver()->copy($oriPath, $targetPath);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * Update transaction with old data to new logic
     *
     * @param ModuleDataSetupInterface $installer
     *
     * @throws \Exception
     */
    protected function updateTransactionData($installer)
    {
        $connection = $installer->getConnection();
        $connection->beginTransaction();
        try {
            $table = $installer->getTable('mageplaza_reward_transaction');
            $connection->update(
                $table,
                ['action_code' => Data::ACTION_ADMIN],
                $connection->quoteInto('action_code = ?', 'Admin Updated')
            );
            $connection->update(
                $table,
                ['action_code' => Data::ACTION_EARNING_ORDER],
                $connection->quoteInto('action_code = ?', 'Order Earning')
            );
            $connection->update(
                $table,
                ['action_code' => Data::ACTION_SPENDING_ORDER],
                $connection->quoteInto('action_code = ?', 'Order Spending')
            );
            $connection->update(
                $table,
                ['action_code' => Data::ACTION_EARNING_REFUND],
                $connection->quoteInto('action_code = ?', 'Creditmemo Earning')
            );
            $connection->update(
                $table,
                ['action_code' => Data::ACTION_EARNING_REFUND],
                $connection->quoteInto('action_code = ?', 'Creditmemo Spending')
            );
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }
}
