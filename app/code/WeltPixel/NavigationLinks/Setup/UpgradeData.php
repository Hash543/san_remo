<?php

namespace WeltPixel\NavigationLinks\Setup;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Upgrade Data script
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Category setup factory
     *
     * @var CategorySetupFactory
     */
    private $catalogSetupFactory;

    /**
     * Init
     *
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(CategorySetupFactory $categorySetupFactory)
    {
        $this->catalogSetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
	
	    /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
	    $catalogSetup = $this->catalogSetupFactory->create(['setup' => $setup]);
        
        if (version_compare($context->getVersion(), '1.0.1') < 0) {

            $catalogSetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, 'weltpixel_category_url_newtab', [
                'type' => 'int',
                'label' => 'Open Link In New Tab',
                'input' => 'select',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'required' => false,
                'sort_order' => 2,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group' => 'WeltPixel Options'
            ]);
        }
        
	    if (version_compare($context->getVersion(), '1.2.0') < 0) {
		
		    $catalogSetup->addAttribute(Category::ENTITY, 'weltpixel_mm_display_mode', [
				    'type' => 'varchar',
				    'label' => 'Display Mode',
				    'input' => 'select',
				    'default' => 'sectioned',
				    'required' => false,
				    'sort_order' => 1,
				    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
				    'group' => 'WeltPixel Mega Menu Options'
		    ]);
		
		    $catalogSetup->addAttribute(Category::ENTITY, 'weltpixel_mm_columns_number', [
				    'type' => 'text',
				    'label' => 'Number of columns in dropdown menu',
				    'input' => 'text',
				    'default' => '4',
				    'required' => false,
				    'sort_order' => 2,
				    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
				    'wysiwyg_enabled' => false,
				    'group' => 'WeltPixel Mega Menu Options',
		    ]);
		
		    $catalogSetup->addAttribute(Category::ENTITY, 'weltpixel_mm_column_width', [
				    'type' => 'text',
				    'label' => 'Column Width',
				    'input' => 'text',
				    'default' => 'auto',
				    'required' => false,
				    'sort_order' => 3,
				    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
				    'wysiwyg_enabled' => false,
				    'group' => 'WeltPixel Mega Menu Options',
		    ]);
	    }

        $setup->endSetup();
    }
}