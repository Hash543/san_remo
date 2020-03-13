<?php
/**
 * @author Israel Yasis
 */
namespace PSS\PaymentPoints\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;
use PSS\PaymentPoints\Helper\Data as HelperData;

class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
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
     * UpgradeData constructor.
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
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        $setup->startSetup();
        $connection = $setup->getConnection();
        if(version_compare($context->getVersion(), '1.0.1', '<')) {
            $quoteTable = $setup->getTable('quote');
            if($connection->tableColumnExists($quoteTable, HelperData::ATTRIBUTE_CALCULATE_POINTS)) {
                $connection->dropColumn($quoteTable, HelperData::ATTRIBUTE_CALCULATE_POINTS);
            }
            $orderTable = $setup->getTable('sales_order');
            if($connection->tableColumnExists($orderTable, HelperData::ATTRIBUTE_CALCULATE_POINTS)) {
                $connection->dropColumn($orderTable, HelperData::ATTRIBUTE_CALCULATE_POINTS);
            }
            $invoiceTable = $setup->getTable('sales_invoice');
            if($connection->tableColumnExists($invoiceTable, HelperData::ATTRIBUTE_CALCULATE_POINTS)) {
                $connection->dropColumn($invoiceTable, HelperData::ATTRIBUTE_CALCULATE_POINTS);
            }
            $creditMemoTable = $setup->getTable('sales_creditmemo');
            if($connection->tableColumnExists($creditMemoTable, HelperData::ATTRIBUTE_CALCULATE_POINTS)) {
                $connection->dropColumn($creditMemoTable, HelperData::ATTRIBUTE_CALCULATE_POINTS);
            }
            /**
             * @var \Magento\Quote\Setup\QuoteSetup $quoteSetup
             * @var \Magento\Sales\Setup\SalesSetup $salesSetup
             */
            $quoteSetup = $this->quoteSetupFactory->create();
            $salesSetup = $this->salesSetupFactory->create();
            $attributes = [
                HelperData::ATTRIBUTE_CALCULATE_POINTS => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
            ];
            foreach ($attributes as $attributeCode => $attributeParams) {
                $quoteSetup->addAttribute('quote', $attributeCode, $attributeParams);
                $salesSetup->addAttribute('order', $attributeCode, $attributeParams);
                $salesSetup->addAttribute('invoice', $attributeCode, $attributeParams);
                $salesSetup->addAttribute('creditmemo', $attributeCode, $attributeParams);
            }
        }
        $setup->endSetup();
    }
}