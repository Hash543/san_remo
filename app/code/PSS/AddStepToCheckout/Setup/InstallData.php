<?php

namespace PSS\AddStepToCheckout\Setup;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\Data\BlockInterfaceFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;
    /**
     * @var BlockInterfaceFactory
     */
    private $blockInterfaceFactory;

    public function __construct(
        BlockRepositoryInterface $blockRepository,
        BlockInterfaceFactory $blockInterfaceFactory
    ) {
        $this->blockRepository = $blockRepository;
        $this->blockInterfaceFactory = $blockInterfaceFactory;
    }

    /**
     * Installs data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->createItemsBlock();
    }

    /**
     * Create a CMS block
     */
    public function createItemsBlock()
    {
        /** @var BlockInterface $itemsBlock */
        $itemsBlock = $this->blockInterfaceFactory->create();
        $itemsBlock->setIdentifier('items_custom_cart')
            ->setTitle('Items custom cart')
            ->setContent('<div class="items-custom-cart">{{block class="Magento\Checkout\Block\Cart\Grid" name="items-cart" template="PSS_AddStepToCheckout::cart/item/items.phtml" }}</div>')
            ->setIsActive(1)
            ->setData('stores', [0]);
        
        $this->blockRepository->save($itemsBlock);
    }
}