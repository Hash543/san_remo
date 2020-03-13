<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 *
 * This package designed for Magento COMMUNITY edition
 * PSS Digital does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * PSS Digital does not provide extension support in case of * incorrect edition usage.
 *
 * @author    PSS Digital Team
 * @category  PSS
 * @package   PSS_Loyalty
 * @copyright Copyright (c) 2019 PSS (https://www.pss-ti.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace PSS\Loyalty\Setup;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\ResourceModel\Group;
use Magento\Store\Model\ResourceModel\Store;
use Magento\Store\Model\ResourceModel\Website;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\WebsiteFactory;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\PageFactory;
use PSS\Loyalty\Helper\Data as LoyaltyHelper;

class InstallSchema implements InstallSchemaInterface {
    /**
     * @var LoyaltyHelper
     */
    private $helper;

    /**
     * @var WebsiteFactory
     */
    private $websiteFactory;

    /**
     * @var Website
     */
    private $websiteResourceModel;

    /**
     * @var StoreFactory
     */
    private $storeFactory;

    /**
     * @var GroupFactory
     */
    private $groupFactory;

    /**
     * @var Group
     */
    private $groupResourceModel;

    /**
     * @var Store
     */
    private $storeResourceModel;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * InstallSchema constructor.
     *
     * @param LoyaltyHelper    $helper
     * @param WebsiteFactory   $websiteFactory
     * @param Website          $websiteResourceModel
     * @param Store            $storeResourceModel
     * @param Group            $groupResourceModel
     * @param StoreFactory     $storeFactory
     * @param GroupFactory     $groupFactory
     * @param ManagerInterface $eventManager
     * @param BlockFactory     $blockFactory
     * @param PageFactory      $pageFactory
     */
    public function __construct(
        LoyaltyHelper $helper,
        WebsiteFactory $websiteFactory,
        Website $websiteResourceModel,
        Store $storeResourceModel,
        Group $groupResourceModel,
        StoreFactory $storeFactory,
        GroupFactory $groupFactory,
        ManagerInterface $eventManager,
        BlockFactory $blockFactory,
        PageFactory $pageFactory
    ) {
        $this->helper = $helper;
        $this->websiteFactory = $websiteFactory;
        $this->websiteResourceModel = $websiteResourceModel;
        $this->storeFactory = $storeFactory;
        $this->groupFactory = $groupFactory;
        $this->groupResourceModel = $groupResourceModel;
        $this->storeResourceModel = $storeResourceModel;
        $this->eventManager = $eventManager;
        $this->blockFactory = $blockFactory;
        $this->pageFactory = $pageFactory;
    }

    /**
     * Generates a new website, and his stores and views
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function createWebsite() {
        /** @var  \Magento\Store\Model\Store $store */
        $store = $this->storeFactory->create();
        $store->load(LoyaltyHelper::STOREVIEW_CODE);
        if(!$store->getId()) {
            /** @var \Magento\Store\Model\Website $website */
            $website = $this->websiteFactory->create();
            $website->load(LoyaltyHelper::WEBSITE_CODE);
            if(!$website->getId()) {
                $website->setCode(LoyaltyHelper::WEBSITE_CODE);
                $website->setName(LoyaltyHelper::WEBSITE_NAME);
                $website->setDefaultGroupId(3);
                $this->websiteResourceModel->save($website);
            }

            if($website->getId()) {
                /** @var \Magento\Store\Model\Group $group */
                $group = $this->groupFactory->create();
                $group->setWebsiteId($website->getWebsiteId());
                $group->setCode(LoyaltyHelper::STORE_CODE);
                $group->setName(LoyaltyHelper::STORE_NAME);
                $group->setRootCategoryId(2);
                $group->setDefaultStoreId(3);
                $this->groupResourceModel->save($group);
            }

            $group = $this->groupFactory->create();
            $group->load(LoyaltyHelper::STORE_NAME, 'name');
            $store->setCode(LoyaltyHelper::STOREVIEW_CODE);
            $store->setName(LoyaltyHelper::STOREVIEW_NAME);
            $store->setWebsite($website);
            $store->setGroupId($group->getId());
            $store->setData('is_active', '1');
            $this->storeResourceModel->save($store);
            // Trigger event to insert some data to the sales_sequence_meta table (fix bug place order in checkout)
            $this->eventManager->dispatch('store_add', ['store' => $store]);
        }
    }

    /**
     * Changes the scope to all CMS pages and blocks
     */
    public function cmsAssignToDefault() {
        $defaultStores = ["0"];

        $blocks = $this->blockFactory->create()->getCollection();
        foreach($blocks as $block) {
            $block->setStoreId($defaultStores);
            $block->save();
        }

        $pages = $this->pageFactory->create()->getCollection();
        foreach($pages as $page) {
            $page->setStoreId($defaultStores);
            $page->save();
        }
    }

    /**
     * Installs data for a module
     *
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $setup->startSetup();

        $this->createWebsite($setup);
        $this->cmsAssignToDefault();

        $setup->endSetup();
    }
}