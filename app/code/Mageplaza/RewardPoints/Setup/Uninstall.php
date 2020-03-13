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

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;

/**
 * Class Uninstall
 * @package Mageplaza\RewardPoints\Setup
 */
class Uninstall implements UninstallInterface
{
    /**
     * @var QuoteSetupFactory
     */
    protected $quoteSetupFactory;

    /**
     * @var SalesSetupFactory
     */
    protected $salesSetupFactory;

    /**
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        QuoteSetupFactory $quoteSetupFactory,
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if ($installer->tableExists('mageplaza_reward_rate')) {
            $installer->getConnection()->dropTable($installer->getTable('mageplaza_reward_rate'));
        }
        if ($installer->tableExists('mageplaza_reward_customer')) {
            $installer->getConnection()->dropTable($installer->getTable('mageplaza_reward_customer'));
        }
        if ($installer->tableExists('mageplaza_reward_transaction')) {
            $installer->getConnection()->dropTable($installer->getTable('mageplaza_reward_transaction'));
        }

        /** @var \Magento\Quote\Setup\QuoteSetup $quoteInstaller */
        $quoteInstaller = $this->quoteSetupFactory->create(['resourceName' => 'quote_setup', 'setup' => $setup]);

        /** @var \Magento\Sales\Setup\SalesSetup $salesInstaller */
        $salesInstaller = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $setup]);

        $data = [
            ['table' => 'order', 'column' => 'mp_reward_earn'],
            ['table' => 'order', 'column' => 'mp_reward_spent'],
            ['table' => 'order', 'column' => 'mp_reward_base_discount'],
            ['table' => 'order', 'column' => 'mp_reward_discount'],
            ['table' => 'order', 'column' => 'mp_reward_shipping_earn_invoiced'],
            ['table' => 'order', 'column' => 'mp_reward_shipping_spent_invoiced'],
            ['table' => 'order', 'column' => 'mp_reward_shipping_base_discount_invoiced'],
            ['table' => 'order', 'column' => 'mp_reward_shipping_discount_invoiced'],
            ['table' => 'order', 'column' => 'mp_reward_shipping_earn_refunded'],
            ['table' => 'order', 'column' => 'mp_reward_shipping_spent_refunded'],
            ['table' => 'order', 'column' => 'mp_reward_shipping_base_discount_refunded'],
            ['table' => 'order', 'column' => 'mp_reward_shipping_discount_refunded'],
            ['table' => 'order', 'column' => 'mp_reward_earn_refunded'],
            ['table' => 'order', 'column' => 'mp_reward_spent_refunded'],
            ['table' => 'order', 'column' => 'mp_reward_earn_invoiced'],
            ['table' => 'order', 'column' => 'mp_reward_spent_invoiced'],
            ['table' => 'order', 'column' => 'mp_reward_base_discount_invoiced'],
            ['table' => 'order', 'column' => 'mp_reward_discount_invoiced'],
            ['table' => 'order', 'column' => 'mp_reward_base_discount_refunded'],
            ['table' => 'order', 'column' => 'mp_reward_discount_refunded'],
            ['table' => 'order_item', 'column' => 'mp_reward_earn'],
            ['table' => 'order_item', 'column' => 'mp_reward_spent'],
            ['table' => 'order_item', 'column' => 'mp_reward_base_discount'],
            ['table' => 'order_item', 'column' => 'mp_reward_discount'],
            ['table' => 'invoice', 'column' => 'mp_reward_earn'],
            ['table' => 'invoice', 'column' => 'mp_reward_spent'],
            ['table' => 'invoice', 'column' => 'mp_reward_base_discount'],
            ['table' => 'invoice', 'column' => 'mp_reward_discount'],
            ['table' => 'invoice', 'column' => 'mp_reward_shipping_earn'],
            ['table' => 'invoice', 'column' => 'mp_reward_shipping_spent'],
            ['table' => 'invoice', 'column' => 'mp_reward_shipping_base_discount'],
            ['table' => 'invoice', 'column' => 'mp_reward_shipping_discount'],
            ['table' => 'invoice_item', 'column' => 'mp_reward_earn'],
            ['table' => 'invoice_item', 'column' => 'mp_reward_spent'],
            ['table' => 'invoice_item', 'column' => 'mp_reward_base_discount'],
            ['table' => 'invoice_item', 'column' => 'mp_reward_discount'],
            ['table' => 'creditmemo', 'column' => 'mp_reward_earn'],
            ['table' => 'creditmemo', 'column' => 'mp_reward_spent'],
            ['table' => 'creditmemo', 'column' => 'mp_reward_base_discount'],
            ['table' => 'creditmemo', 'column' => 'mp_reward_discount'],
            ['table' => 'creditmemo', 'column' => 'mp_reward_shipping_earn'],
            ['table' => 'creditmemo', 'column' => 'mp_reward_shipping_spent'],
            ['table' => 'creditmemo', 'column' => 'mp_reward_shipping_base_discount'],
            ['table' => 'creditmemo', 'column' => 'mp_reward_shipping_discount'],
            ['table' => 'creditmemo_item', 'column' => 'mp_reward_earn'],
            ['table' => 'creditmemo_item', 'column' => 'mp_reward_spent'],
            ['table' => 'creditmemo_item', 'column' => 'mp_reward_base_discount'],
            ['table' => 'creditmemo_item', 'column' => 'mp_reward_discount'],
            ['table' => 'quote', 'column' => 'mp_reward_earn'],
            ['table' => 'quote', 'column' => 'mp_reward_spent'],
            ['table' => 'quote', 'column' => 'mp_reward_base_discount'],
            ['table' => 'quote', 'column' => 'mp_reward_discount'],
            ['table' => 'quote_address', 'column' => 'mp_reward_earn'],
            ['table' => 'quote_address', 'column' => 'mp_reward_spent'],
            ['table' => 'quote_address', 'column' => 'mp_reward_base_discount'],
            ['table' => 'quote_address', 'column' => 'mp_reward_discount'],
            ['table' => 'quote_item', 'column' => 'mp_reward_earn'],
            ['table' => 'quote_item', 'column' => 'mp_reward_spent'],
            ['table' => 'quote_item', 'column' => 'mp_reward_base_discount'],
            ['table' => 'quote_item', 'column' => 'mp_reward_discount'],
            ['table' => 'order', 'column' => 'mp_reward_shipping_earn'],
            ['table' => 'order', 'column' => 'mp_reward_shipping_spent'],
            ['table' => 'order', 'column' => 'mp_reward_shipping_base_discount'],
            ['table' => 'order', 'column' => 'mp_reward_shipping_discount'],
            ['table' => 'quote', 'column' => 'mp_reward_shipping_earn'],
            ['table' => 'quote', 'column' => 'mp_reward_shipping_spent'],
            ['table' => 'quote', 'column' => 'mp_reward_shipping_base_discount'],
            ['table' => 'quote', 'column' => 'mp_reward_shipping_discount']
        ];

        foreach ($data as $item) {
            if (in_array($item['table'], ['quote', 'quote_item', 'quote_address'])) {
                $quoteInstaller->removeAttribute($item['table'], $item['column']);
            } else {
                $salesInstaller->removeAttribute($item['table'], $item['column']);
            }
        }

        $installer->endSetup();
    }
}
