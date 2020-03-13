<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PromoBanners
 */

namespace Amasty\PromoBanners\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;


class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {

            $setup->getConnection()->addColumn(
                $setup->getTable('amasty_banner_rule'),
                'after_n_product_row',
                [
                    'type'     => Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'comment'  => 'Show Banner After N Products Row'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('amasty_banner_rule'),
                'n_product_width',
                [
                    'type'     => Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'comment'  => 'N Product Width'
                ]
            );
        }

        $setup->endSetup();
    }
}
