<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\GiftProduct\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Framework\DB\Ddl\Table;

class InstallData implements InstallDataInterface{

    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;

    /**
     * InstallData constructor.
     * @param SalesSetupFactory $salesSetupFactory
     * @param QuoteSetupFactory $quoteSetupFactory
     */
    public function __construct( SalesSetupFactory $salesSetupFactory, QuoteSetupFactory $quoteSetupFactory ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        /** @var \Magento\Quote\Setup\QuoteSetup $quoteInstaller */
        $quoteInstaller = $this->quoteSetupFactory->create(['resourceName' => 'quote_setup', 'setup' => $setup]);
        /** @var \Magento\Sales\Setup\SalesSetup $salesInstaller */
        $salesInstaller = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $setup]);
        $installer->startSetup();
        $attributes = [
            'is_gift' => ['type' => Table::TYPE_BOOLEAN],
            'gift_price' => ['type' => Table::TYPE_DECIMAL],
        ];

        foreach ($attributes as $attributeCode => $attributeParams) {
            $quoteInstaller->addAttribute('quote_item', $attributeCode, $attributeParams);
            $salesInstaller->addAttribute('order_item', $attributeCode, $attributeParams);
            $salesInstaller->addAttribute('invoice_item', $attributeCode, $attributeParams);
            $salesInstaller->addAttribute('creditmemo_item', $attributeCode, $attributeParams);
        }
        $installer->endSetup();
    }
}