<?php
/**
 * @author Israel Yasis
 */
namespace PSS\Paypal\Setup;

use Magento\Quote\Setup\QuoteSetup;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetup;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
/**
 * Class InstallData
 * @package PSS\Paypal\Setup
 */
class InstallData implements \Magento\Framework\Setup\InstallDataInterface {
    /**
     * Sales setup factory
     *
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * Quote setup factory
     *
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;

    /**
     * Init
     *
     * @param SalesSetupFactory $salesSetupFactory
     * @param QuoteSetupFactory $quoteSetupFactory
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory,
        QuoteSetupFactory $quoteSetupFactory
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
    }
    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var QuoteSetup $quoteSetup */
        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);
        /** @var SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
        $quoteSetup->addAttribute('quote', 'paypal_fee_amount', ['type' => 'decimal']);
        $quoteSetup->addAttribute('quote', 'base_paypal_fee_amount', ['type' => 'decimal']);

        $salesSetup->addAttribute('order', 'paypal_fee_amount', ['type' => 'decimal']);
        $salesSetup->addAttribute('order', 'base_paypal_fee_amount', ['type' => 'decimal']);

        $salesSetup->addAttribute('invoice', 'paypal_fee_amount', ['type' => 'decimal']);
        $salesSetup->addAttribute('invoice', 'base_paypal_fee_amount', ['type' => 'decimal']);

        $salesSetup->addAttribute('creditmemo', 'paypal_fee_amount', ['type' => 'decimal']);
        $salesSetup->addAttribute('creditmemo', 'base_paypal_fee_amount', ['type' => 'decimal']);
    }
}