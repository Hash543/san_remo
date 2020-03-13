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

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Store\Model\ResourceModel\Website;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Model\Config as ThemeConfig;
use Magento\Theme\Model\ResourceModel\Theme\CollectionFactory as ThemeCollection;
use Magento\UrlRewrite\Model\UrlRewrite;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use PSS\Loyalty\Helper\Data as LoyaltyHelper;
use Magento\Store\Model\ResourceModel\Store;

class UpgradeData implements UpgradeDataInterface {
    /**
     * @var LoyaltyHelper
     */
    private $helper;

    /**
     * @var EavSetupFactory
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
     * @var ThemeCollection
     */
    protected $themeCollection;

    /**
     * @var ThemeConfig
     */
    protected $themeConfig;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var Category
     */
    protected $category;

    /**
     * @var ThemeConfig
     */
    protected $categoryFactory;

    /**
     * @var ThemeConfig
     */
    protected $categoryRepositoryInterface;

    /**
     * @var UrlRewrite
     */
    protected $urlRewrite;

    /**
     * @var UrlRewriteFactory
     */
    protected $urlRewriteFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Website
     */
    private $websiteResourceModel;

    /**
     * @var Website
     */
    private $storeviewResourceModel;

    /**
     * UpgradeData constructor.
     *
     * @param LoyaltyHelper               $helper
     * @param EavSetupFactory             $eavSetupFactory
     * @param QuoteSetupFactory           $quoteSetupFactory
     * @param SalesSetupFactory           $salesSetupFactory
     * @param ThemeCollection             $themeCollection
     * @param ThemeConfig                 $themeConfig
     * @param EavConfig                   $eavConfig
     * @param State                       $state
     * @param Category                    $category
     * @param CategoryFactory             $categoryFactory
     * @param CategoryRepositoryInterface $categoryRepositoryInterface
     * @param UrlRewrite                  $urlRewrite
     * @param UrlRewriteFactory           $urlRewriteFactory
     * @param Config                      $config
     * @param StoreManagerInterface       $storeManager
     * @param Website                     $websiteResourceModel
     * @param Store                       $storeviewResourceModel
     */
    public function __construct(
        LoyaltyHelper $helper,
        EavSetupFactory $eavSetupFactory,
        QuoteSetupFactory $quoteSetupFactory,
        SalesSetupFactory $salesSetupFactory,
        ThemeCollection $themeCollection,
        ThemeConfig $themeConfig,
        EavConfig $eavConfig,
        State $state,
        Category $category,
        CategoryFactory $categoryFactory,
        CategoryRepositoryInterface $categoryRepositoryInterface,
        UrlRewrite $urlRewrite,
        UrlRewriteFactory $urlRewriteFactory,
        Config $config,
        StoreManagerInterface $storeManager,
        Website $websiteResourceModel,
        Store $storeviewResourceModel
    ) {
        $this->helper = $helper;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
        $this->themeCollection = $themeCollection;
        $this->themeConfig = $themeConfig;
        $this->eavConfig = $eavConfig;
        $this->state = $state;
        $this->category = $category;
        $this->categoryFactory = $categoryFactory;
        $this->categoryRepositoryInterface = $categoryRepositoryInterface;
        $this->urlRewrite = $urlRewrite;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->websiteResourceModel = $websiteResourceModel;
        $this->storeviewResourceModel = $storeviewResourceModel;
    }

    /**
     * Assigns Theme to the Loyalty website
     *
     * @return void
     */
    protected function assignTheme() {
        $themes = $this->themeCollection->create()->loadRegisteredThemes();
        $website = $this->helper->getWebsite();

        foreach($themes as $theme) {
            if($theme->getCode() == LoyaltyHelper::THEME_NAME) {
                $this->themeConfig->assignToStore(
                    $theme,
                    [$website->getId()],
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT
                );
            }
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param array                    $entityAttributes
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function createAttributes(ModuleDataSetupInterface $setup, array $entityAttributes) {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        foreach($entityAttributes as $entityType => $attributes) {
            foreach($attributes as $attributeCode => $attributeData) {
                $eavSetup->removeAttribute($entityType, $attributeCode);
                $eavSetup->addAttribute($entityType, $attributeCode, $attributeData);

                $attribute = $this->eavConfig->getAttribute($entityType, $attributeCode);

                if($entityType == 'customer_address') {
                    $used_in_forms = ['adminhtml_customer_address'];
                    $attribute->setData('used_in_forms', $used_in_forms);
                } else {
                    // more used_in_forms ["adminhtml_checkout", "adminhtml_customer", "adminhtml_customer_address", "checkout_register", "customer_account_create", "customer_account_edit", "customer_address_edit", "customer_register_address"]
                    $used_in_forms = ($attributeData['user_defined']) ? ['adminhtml_customer', 'customer_account_create', 'customer_account_edit'] : ['adminhtml_customer'];

                    $attribute->setData('used_in_forms', $used_in_forms);
                }

                $attribute->save();
            }
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param array                    $entityAttributes
     */
    public function deleteAttributes(ModuleDataSetupInterface $setup, array $entityAttributes) {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        foreach($entityAttributes as $entityType => $attributes) {
            foreach($attributes as $attributeCode => $attributeData) {
                $eavSetup->removeAttribute($entityType, $attributeCode);
            }
        }
    }

    /**
     *
     */
    public function editAttributeSet() {

    }

    /**
     * Creates new category under Promociones
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function createCategory() {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        $loyaltyWebsite = $this->helper->getWebsite();

        $cate = $this->category->getCollection()->addAttributeToFilter('name', LoyaltyHelper::ATTRIBUTE_SET_NAME)->getFirstItem();

        // If category does not exists already, create it
        if(!$cate->getId()) {
            $category = $this->categoryFactory->create();

            $category->setName(LoyaltyHelper::ATTRIBUTE_SET_NAME);
            $category->setParentId(1036); // 1036: Category "Promociones"
            $category->setIsActive(true);
            $category->setCustomAttributes([
                'description' => LoyaltyHelper::ATTRIBUTE_SET_NAME,
            ]);

            $this->categoryRepositoryInterface->save($category);

            // Change the url path (don't add the parent category)
            $categoryId = $category->getEntityId();
            $urlRewrite = $this->urlRewrite->getCollection()
                                           ->addFieldToFilter('entity_type', 'category')
                                           ->addFieldToFilter('entity_id', $categoryId);

            $categoryUrlRewrite = $urlRewrite->getFirstItem();
            if($categoryUrlRewrite->getId()) {
                $categoryUrlRewrite->setRequestPath(LoyaltyHelper::WEBSITE_CODE . ".html");
                $categoryUrlRewrite->save();

                // Create redirect for Loyalty website
                $urlRewriteModel = $this->urlRewriteFactory->create();
                $urlRewriteModel->setIdPath($categoryUrlRewrite->getUrlRewriteId() + 1);
                $urlRewriteModel->setEntityType($categoryUrlRewrite->getEntityType());
                $urlRewriteModel->setEntityID($categoryUrlRewrite->getEntityId());
                $urlRewriteModel->setRequestPath($categoryUrlRewrite->getRequestPath());
                $urlRewriteModel->setTargetPath($categoryUrlRewrite->getTargetPath());
                $urlRewriteModel->setStoreId($loyaltyWebsite->getId());
                $urlRewriteModel->setMetadata($categoryUrlRewrite->getMetadata());

                $urlRewriteModel->save();
            }
        }
    }

    /**
     * @param $code
     *
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    protected function changeWebsiteCode($code) {
        // Changes the code of the website
        $website = $this->helper->getWebsite($code);

        if($website->getId()) {
            $website->setCode(LoyaltyHelper::WEBSITE_CODE);
            $this->websiteResourceModel->save($website);
        }
    }

    /**
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    protected function changeStoreViewCode() {
        // Changes the code of the default storeview
        $storeviewDefault = $this->helper->getStoreView('default');
        if($storeviewDefault->getId()) {
            $storeviewDefault->setCode('es');
            $this->storeviewResourceModel->save($storeviewDefault);
        }

        // Changes the code of the loyalti storeview
        $storeviewDefault = $this->helper->getStoreView('loyalty_storeview');
        if($storeviewDefault->getId()) {
            $storeviewDefault->setCode(LoyaltyHelper::STOREVIEW_CODE);
            $this->storeviewResourceModel->save($storeviewDefault);
        }
    }

    /**
     * Adds the SRP currency to Loyalty website
     */
    protected function assignWebsiteCurrency() {
        $website = $this->helper->getWebsite();
        $this->config->saveConfig('currency/options/default', LoyaltyHelper::CURRENCY_CODE, 'websites', $website->getId());
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        $setup->startSetup();

        if(version_compare($context->getVersion(), '1.0.1', '<')) {
            /* PRODUCT ATTRIBUTES */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'price_euro');
            /**
             * Add attributes to the eav/attribute
             */
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'price_euro',
                [
                    'label' => 'Precio en Euros',
                    'attribute_set' => LoyaltyHelper::ATTRIBUTE_SET_NAME,
                    'type' => 'decimal',
                    'backend' => 'Magento\Catalog\Model\Product\Attribute\Backend\Price',
                    'input' => 'price',
                    'required' => false,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                ]
            );
        }

        if(version_compare($context->getVersion(), '1.0.2', '<')) {
            /* ORDER ATTRIBUTES */
            /** @var \Magento\Quote\Setup\QuoteSetup $quoteInstaller */
            $quoteSetup = $this->quoteSetupFactory->create(['resourceName' => 'quote_setup', 'setup' => $setup]);

            /** @var \Magento\Sales\Setup\SalesSetup $salesInstaller */
            $salesSetup = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $setup]);

            foreach($this->helper->getOrderAttributes() as $item) {
                if(in_array($item['table'], ['quote', 'quote_item'])) {
                    foreach($item['attributes'] as $attributeCode => $attributeType) {
                        $quoteSetup->removeAttribute('order', $attributeCode);
                        $quoteSetup->addAttribute(
                            $item['table'],
                            $attributeCode,
                            ['type' => $attributeType, 'visible' => false, 'nullable' => true]
                        );
                    }
                } else {
                    foreach($item['attributes'] as $attributeCode => $attributeType) {
                        $salesSetup->removeAttribute('order', $attributeCode);
                        $salesSetup->addAttribute(
                            $item['table'],
                            $attributeCode,
                            ['type' => $attributeType, 'visible' => false, 'nullable' => true]
                        );
                    }
                }
            }
        }

        if(version_compare($context->getVersion(), '1.0.3', '<')) {
            $this->assignTheme();
        }

        if(version_compare($context->getVersion(), '1.0.4', '<')) {
            $this->createAttributes($setup, $this->helper->getCustomerAttributesOld());
        }

        if(version_compare($context->getVersion(), '1.0.5', '<')) {
            $this->deleteAttributes($setup, $this->helper->getCustomerAttributesOld());
            $this->createAttributes($setup, $this->helper->getCustomerAttributes());
        }

        if(version_compare($context->getVersion(), '1.0.6', '<')) {
            $this->deleteAttributes($setup, $this->helper->getCustomerAttributesAddress());
            $this->createAttributes($setup, $this->helper->getCustomerAttributesAddress());
        }

        if(version_compare($context->getVersion(), '1.0.7', '<')) {
            /** @var \Magento\Quote\Setup\QuoteSetup $quoteSetup */
            $quoteSetup = $this->quoteSetupFactory->create(['resourceName' => 'quote_setup', 'setup' => $setup]);

            /** @var \Magento\Sales\Setup\SalesSetup $salesSetup */
            $salesSetup = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $setup]);
            $attributes = [
                'calculate_earning_points' => ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL]
            ];

            foreach ($attributes as $attributeCode => $attributeParams) {
                $quoteSetup->addAttribute('quote', $attributeCode, $attributeParams);
                $salesSetup->addAttribute('order', $attributeCode, $attributeParams);
                $salesSetup->addAttribute('invoice', $attributeCode, $attributeParams);
                $salesSetup->addAttribute('creditmemo', $attributeCode, $attributeParams);
            }
            $this->createCategory();
            $this->changeWebsiteCode('loyalty');
            $this->assignWebsiteCurrency();
        }
        if(version_compare($context->getVersion(), '1.0.8', '<')) {
            $this->deleteAttributes($setup, [\Magento\Customer\Model\Customer::ENTITY => ['list_id']]);
            $this->createAttributes($setup, [\Magento\Customer\Model\Customer::ENTITY => [
                    'list_id' => [
                        'label' => 'Lista Asociada Marketing',
                        'type' => 'varchar',
                        'input' => 'text',
                        'required' => false,
                        'visible' => true,
                        'user_defined' => false,
                        'position' => 255,
                        'system' => 0,
                    ]
                ]
            ]);
            $this->changeWebsiteCode('fidelizacion');
            $this->changeStoreViewCode();
        }
        $setup->endSetup();
    }
}