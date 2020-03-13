<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */

namespace Amasty\Promo\Setup;

/**
 * Class UpgradeSchema
 *
 * @author Artem Brunevski
 */

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->addBannersToRule($installer);
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $this->updateImageFields($installer);
        }

        if (version_compare($context->getVersion(), '1.3.0', '<')) {
            $this->addPromoItemsDiscount($installer);
        }

        if (version_compare($context->getVersion(), '1.3.1', '<')) {
            $this->addControlTaxShippingSettings($installer);
        }

        $installer->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addBannersToRule(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_ampromo_rule'),
            'top_banner_image',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => '',
                'length' => 255,
                'comment' => 'Banner Image'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_ampromo_rule'),
            'top_banner_alt',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => '',
                'length' => 255,
                'comment' => 'Banner Alt'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_ampromo_rule'),
            'top_banner_on_hover_text',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => '',
                'length' => 255,
                'comment' => 'Banner On Hover Text'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_ampromo_rule'),
            'top_banner_link',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => '',
                'length' => 255,
                'comment' => 'Banner Link'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_ampromo_rule'),
            'top_banner_show_gift_images',
            [
                'type' => Table::TYPE_BOOLEAN,
                'nullable' => false,
                'default' => false,
                'comment' => 'Show Gift Images'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_ampromo_rule'),
            'top_banner_description',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => '',
                'length' => '64k',
                'comment' => 'Banner Description'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_ampromo_rule'),
            'after_product_banner_image',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => '',
                'length' => 255,
                'comment' => 'Banner Image'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_ampromo_rule'),
            'after_product_banner_alt',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => '',
                'length' => 255,
                'comment' => 'Banner Alt'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_ampromo_rule'),
            'after_product_banner_on_hover_text',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => '',
                'length' => 255,
                'comment' => 'Banner On Hover Text'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_ampromo_rule'),
            'after_product_banner_link',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => '',
                'length' => 255,
                'comment' => 'Banner Link'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_ampromo_rule'),
            'after_product_banner_show_gift_images',
            [
                'type' => Table::TYPE_BOOLEAN,
                'nullable' => false,
                'default' => false,
                'comment' => 'Show Gift Images'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_ampromo_rule'),
            'after_product_banner_description',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => '',
                'length' => '64k',
                'comment' => 'Banner Description'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_ampromo_rule'),
            'label_image',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => '',
                'length' => 255,
                'comment' => 'Label Image'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_ampromo_rule'),
            'label_image_alt',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => '',
                'length' => 255,
                'comment' => 'Label Image Alt'
            ]
        );

    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function updateImageFields(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->changeColumn(
            $setup->getTable('amasty_ampromo_rule'),
            'top_banner_image',
            'top_banner_image',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => '',
                'length' => '128k',
                'comment' => 'Banner Image'
            ]
        );

        $setup->getConnection()->changeColumn(
            $setup->getTable('amasty_ampromo_rule'),
            'after_product_banner_image',
            'after_product_banner_image',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => '',
                'length' => '128k',
                'comment' => 'Banner Image'
            ]
        );

        $setup->getConnection()->changeColumn(
            $setup->getTable('amasty_ampromo_rule'),
            'label_image',
            'label_image',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => '',
                'length' => '128k',
                'comment' => 'Label Image'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addPromoItemsDiscount(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_ampromo_rule'),
            'items_discount',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => '',
                'length' => 255,
                'comment' => 'Promo Items Discount'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_ampromo_rule'),
            'minimal_items_price',
            [
                'type' => Table::TYPE_FLOAT,
                'default' => null,
                'nullable' => true,
                'comment' => 'Minimal Price'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    public function addControlTaxShippingSettings(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_ampromo_rule'),
            'apply_tax',
            [
                'type' => Table::TYPE_BOOLEAN,
                'default' => 0,
                'nullable' => false,
                'comment' => 'Apply tax on original price of promo items added for free'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('amasty_ampromo_rule'),
            'apply_shipping',
            [
                'type' => Table::TYPE_BOOLEAN,
                'default' => 0,
                'nullable' => false,
                'comment' => 'Apply shipping on promo items added for free'
            ]
        );
    }
}
