<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\Checkout\Setup;

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
            'comment_order' => ['type' => Table::TYPE_TEXT],
            'delivery_date' => ['type' => Table::TYPE_DATETIME],
        ];

        foreach ($attributes as $attributeCode => $attributeParams) {
            $quoteInstaller->addAttribute('quote', $attributeCode, $attributeParams);
            $salesInstaller->addAttribute('order', $attributeCode, $attributeParams);
            $salesInstaller->addAttribute('invoice', $attributeCode, $attributeParams);
            $salesInstaller->addAttribute('creditmemo', $attributeCode, $attributeParams);
        }
        $installer->endSetup();
    }
}