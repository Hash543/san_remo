<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Setup;

use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Downloadable\Model\Product\Type as DownloadableType;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;

/**
 * Class InstallData
 * @package Mageplaza\RewardPointsUltimate\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var QuoteSetupFactory
     */
    protected $quoteSetupFactory;

    /**
     * @var SalesSetupFactory
     */
    protected $salesSetupFactory;

    /**
     * InstallData constructor.
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        QuoteSetupFactory $quoteSetupFactory,
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var \Magento\Quote\Setup\QuoteSetup $quoteInstaller */
        $quoteInstaller = $this->quoteSetupFactory->create(['resourceName' => 'quote_setup', 'setup' => $setup]);

        /** @var \Magento\Sales\Setup\SalesSetup $salesInstaller */
        $salesInstaller = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $setup]);

        $quoteInstaller->addAttribute(
            'quote_item',
            'mp_reward_sell_points',
            ['type' => Table::TYPE_INTEGER, 'visible' => false]
        );
        $salesInstaller->addAttribute(
            'order_item',
            'mp_reward_sell_points',
            ['type' => Table::TYPE_INTEGER, 'visible' => false]
        );
        $salesInstaller->addAttribute(
            'invoice_item',
            'mp_reward_sell_points',
            ['type' => Table::TYPE_INTEGER, 'visible' => false]
        );
        $salesInstaller->addAttribute(
            'creditmemo_item',
            'mp_reward_sell_points',
            ['type' => Table::TYPE_INTEGER, 'visible' => false]
        );
        $quoteInstaller->addAttribute(
            'quote',
            'mp_reward_invited_base_discount',
            ['type' => Table::TYPE_DECIMAL, 'visible' => false]
        );
        $quoteInstaller->addAttribute(
            'quote',
            'mp_reward_invited_discount',
            ['type' => Table::TYPE_DECIMAL, 'visible' => false]
        );
        $quoteInstaller->addAttribute(
            'quote',
            'mp_reward_shipping_invited_base_discount',
            ['type' => Table::TYPE_DECIMAL, 'visible' => false]
        );
        $quoteInstaller->addAttribute(
            'quote_item',
            'mp_reward_invited_discount',
            ['type' => Table::TYPE_DECIMAL, 'visible' => false]
        );
        $quoteInstaller->addAttribute(
            'quote_item',
            'mp_reward_invited_base_discount',
            ['type' => Table::TYPE_DECIMAL, 'visible' => false]
        );
        $quoteInstaller->addAttribute(
            'quote',
            'mp_reward_referral_earn',
            ['type' => Table::TYPE_INTEGER, 'visible' => false]
        );
        $quoteInstaller->addAttribute(
            'quote',
            'mp_reward_referral_id',
            ['type' => Table::TYPE_INTEGER, 'visible' => false]
        );
        $salesInstaller->addAttribute(
            'order',
            'mp_reward_referral_earn',
            ['type' => Table::TYPE_INTEGER, 'visible' => false]
        );
        $salesInstaller->addAttribute(
            'order',
            'mp_reward_referral_id',
            ['type' => Table::TYPE_INTEGER, 'visible' => false]
        );
        $salesInstaller->addAttribute(
            'order',
            'mp_reward_referral_earn',
            ['type' => Table::TYPE_INTEGER, 'visible' => false]
        );
        $salesInstaller->addAttribute(
            'order_item',
            'mp_reward_referral_earn',
            ['type' => Table::TYPE_INTEGER, 'visible' => false]
        );
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $productTypes = [
            Type::TYPE_SIMPLE,
            Type::TYPE_VIRTUAL,
            DownloadableType::TYPE_DOWNLOADABLE,
            Configurable::TYPE_CODE
        ];
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'mp_reward_sell_product',
            [
                'type'                    => 'int',
                'backend'                 => '',
                'frontend'                => '',
                'label'                   => 'Reward points',
                'input'                   => 'text',
                'class'                   => '',
                'source'                  => '',
                'global'                  => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible'                 => true,
                'required'                => false,
                'user_defined'            => false,
                'default'                 => 0,
                'searchable'              => true,
                'filterable'              => true,
                'comparable'              => false,
                'visible_on_front'        => false,
                'used_in_product_listing' => true,
                'is_used_in_grid'         => true,
                'is_visible_in_grid'      => true,
                'unique'                  => false,
                'apply_to'                => join(',', $productTypes)
            ]
        );
    }
}
