<?php
/**
 * @author Israel Yasis
 */
namespace PSS\Rule\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface {
    /**
     * @var \Magento\Quote\Setup\QuoteSetupFactory
     */
    private $quoteSetupFactory;
    /**
     * @var \Magento\Sales\Setup\SalesSetupFactory
     */
    private $salesSetupFactory;
    /**
     * UpgradeData constructor.
     * @param \Magento\Quote\Setup\QuoteSetupFactory $quoteSetupFactory
     * @param \Magento\Sales\Setup\SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        \Magento\Quote\Setup\QuoteSetupFactory $quoteSetupFactory,
        \Magento\Sales\Setup\SalesSetupFactory $salesSetupFactory
    ) {
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        $setup->startSetup();
        $connection  = $setup->getConnection();
        if(version_compare($context->getVersion(), '1.0.1', '<')) {
             /** @var \Magento\Quote\Setup\QuoteSetup $quoteSetup */
            $quoteSetup = $this->quoteSetupFactory->create(['resourceName' => 'quote_setup', 'setup' => $setup]);

            /** @var \Magento\Sales\Setup\SalesSetup $salesSetup */
            $salesSetup = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $setup]);
            $attributes = [
                \PSS\Rule\Helper\Data::ATTRIBUTE_LIST_MARKETING => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT]
            ];
            foreach ($attributes as $attributeCode => $attributeParams) {
                $quoteSetup->addAttribute('quote', $attributeCode, $attributeParams);
                $salesSetup->addAttribute('order', $attributeCode, $attributeParams);
                $salesSetup->addAttribute('invoice', $attributeCode, $attributeParams);
                $salesSetup->addAttribute('creditmemo', $attributeCode, $attributeParams);
            }
        }
        /**if(version_compare($context->getVersion(), '1.0.2', '<')) {
            $salesRuleCoupon = $setup->getTable('salesrule_coupon');
            $connection->addColumn($salesRuleCoupon, \PSS\Rule\Helper\Data::ATTRIBUTE_LIST_MARKETING , [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                'length' => null,
                'nullable' => true,
                'default' => null,
                'comment' => 'Start Date'
            ]);
        }*/
        $setup->endSetup();
    }
}