<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */

namespace Amasty\Promo\Block;

/**
 * Class Banner
 *
 * @author Artem Brunevski
 */

use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Template;

class Banner extends Template
{
    /**
     * Getting shopping rules modes
     */
    const MODE_PRODUCT = 'product';
    const MODE_CART = 'cart';

    /** @var Product */
    protected $product = null;

    /** @var \Magento\Checkout\Model\Session  */
    protected $checkoutSession;

    /** @var \Magento\Framework\Registry|null  */
    protected $coreRegistry = null;

    /** @var \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory  */
    protected $ruleCollectionFactory;

    /** @var  \Magento\SalesRule\Model\ResourceModel\Rule\Collection */
    protected $ruleCollection;

    /** @var  \Magento\SalesRule\Model\ResourceModel\Rule\Collection [] */
    private static $validRules = null;

    /** @var \Amasty\Promo\Helper\Config\Proxy  */
    protected $config;

    /** @var \Magento\Quote\Model\QuoteFactory  */
    protected $quoteFactory;

    /** @var \Magento\Catalog\Model\ProductFactory  */
    protected $productFactory;

    /** @var \Magento\Quote\Model\Quote\Item\Factory  */
    protected $quoteItemFactory;

    /** @var \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory  */
    protected $ruleConfigCollectionFactory;

    /** @var \Magento\Catalog\Model\Template\Filter\Factory  */
    protected $templateFilterFactory;

    /** @var \Magento\Framework\Filter\Template  */
    protected $pageTemplateProcessor;

    /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory  */
    protected $productCollectionFactory;

    /** @var \Magento\Catalog\Helper\Image  */
    protected $helperImage;
    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializerBase;

    /**
     * @param Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory
     * @param \Amasty\Promo\Helper\Config\Proxy $config
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Quote\Model\Quote\Item\Factory|\Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Amasty\Promo\Model\ResourceModel\Rule\CollectionFactory $ruleConfigCollectionFactory
     * @param \Magento\Catalog\Model\Template\Filter\Factory $templateFilterFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Helper\Image $helperImage
     * @param \Amasty\Base\Model\Serializer $serializerBase
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        \Amasty\Promo\Helper\Config\Proxy $config,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Amasty\Promo\Model\ResourceModel\Rule\CollectionFactory $ruleConfigCollectionFactory,
        \Magento\Catalog\Model\Template\Filter\Factory $templateFilterFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Helper\Image $helperImage,
        \Amasty\Base\Model\Serializer $serializerBase,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->checkoutSession = $checkoutSession;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->config = $config;
        $this->quoteFactory = $quoteFactory;
        $this->productFactory = $productFactory;
        $this->quoteItemFactory = $quoteItemFactory;
        $this->ruleConfigCollectionFactory = $ruleConfigCollectionFactory;
        $this->templateFilterFactory = $templateFilterFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->helperImage = $helperImage;
        parent::__construct($context, $data);
        $this->serializerBase = $serializerBase;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->product) {
            $this->product = $this->coreRegistry->registry('product');
        }
        return $this->product;
    }

    /**
     * @return array
     */
    protected function getProductBasedValidRuleIds()
    {
        $validRules = [];
        $currentQuote = $this->checkoutSession->getQuote();
        $quoteItem = $this->quoteItemFactory->create();
        $quoteItem->setProduct($this->getProduct());
        $quoteItem->setStoreId($currentQuote->getStoreId());
        $quoteItem->setIsVirtual(false);
        $quoteItem->setQuote($currentQuote);
        $quoteItem->setAllItems([$quoteItem]);
        
        /** @var \Magento\SalesRule\Model\Rule $rule */
        foreach ($this->getRulesCollection() as $rule) {
            if ($rule->validate($quoteItem) && $rule->getActions()->validate($quoteItem)) {
                $validRules[] = $rule->getId();
            }
        }

        return $validRules;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getCartBasedValidRuleIds()
    {
        $validRules = [];

        $product = $this->getProduct();
        if (!$product->isSalable()) {
            return;
        }
        $product = clone $product;

        if ($product->getTypeId() === 'configurable') {
            $childrenProducts = $product->getChildrenProducts();
            if (count($childrenProducts) > 0) {
                $product = end($childrenProducts);
                $product = $this->productFactory->load($product->getId());
            }
        }

        $currentQuote = $this->checkoutSession->getQuote();

        $afterQuote = $this->quoteFactory->create()
            ->merge($currentQuote);

        $afterQuote->addProduct($product);
        $afterQuote->collectTotals();

        $currentRules = [];

        /**
         * validate rules according to current quote
         */
        foreach ($this->getRulesCollection() as $rule) {
            foreach ($currentQuote->getItemsCollection() as $item) {
                if ($item->getProduct()->getId() == $this->getProduct()->getId()) {
                    if ($rule->validate($item) && $rule->getActions()->validate($item)) {
                        $currentRules[] = $rule->getId();
                    }
                }
            }
        }

        /**
         * match with quote after add current product
         */
        foreach ($this->getRulesCollection() as $rule) {
            if (!in_array($rule->getId(), $currentRules)) {
                foreach ($afterQuote->getItemsCollection() as $item) {
                    if ($item->getProduct()->getId() == $product->getId()) {
                        if ($rule->validate($item) && $rule->getActions()->validate($item)) {
                            $validRules[] = $rule->getId();
                        }
                    }
                }
            }
        }

        return $validRules;
    }

    /**
     * @return $this|\Magento\SalesRule\Model\ResourceModel\Rule\Collection
     */
    protected function getRulesCollection()
    {
        if ($this->ruleCollection === null) {
            $currentQuote = $this->checkoutSession->getQuote();

            $this->ruleCollection = $this->ruleCollectionFactory->create()
                ->setValidationFilter(
                    $currentQuote->getStore()->getWebsiteId(),
                    $currentQuote->getCustomerGroupId(),
                    $currentQuote->getCouponCode()
                )
                ->addFieldToFilter('simple_action', ['in' => ['ampromo_items', 'ampromo_product', 'ampromo_eachn']]);
        }
        return $this->ruleCollection;
    }

    /**
     * @return array|\Magento\SalesRule\Model\ResourceModel\Rule\Collection[]
     */
    public function getValidRules()
    {
        if (self::$validRules === null) {
            $validRulesIds = [];
            if ($this->config->getScopeValue('banners/mode') === self::MODE_PRODUCT) {
                $validRulesIds = $this->getProductBasedValidRuleIds();
            } else if ($this->config->getScopeValue('banners/mode') === self::MODE_CART) {
                $validRulesIds = $this->getCartBasedValidRuleIds();
            }

            if ($this->config->getScopeValue('banners/single') === '1' && $validRulesIds) {
                $validRulesIds = array_slice($validRulesIds, 0, 1);
            }

            self::$validRules = $this->ruleConfigCollectionFactory->create()
                ->addFieldToFilter('salesrule_id', ['in' => $validRulesIds]);
        }

        return self::$validRules;
    }

    /**
     * @param \Amasty\Promo\Model\Rule $validRule
     * @return mixed
     */
    public function getDescription(\Amasty\Promo\Model\Rule $validRule)
    {
        return $validRule->getData($this->getPosition() . '_banner_description');
    }

    /**
     * @param \Amasty\Promo\Model\Rule $validRule
     * @return mixed
     */
    public function getImage(\Amasty\Promo\Model\Rule $validRule)
    {
        $url = null;
        $image = $this->serializerBase->unserialize($validRule->getData($this->getPosition() . '_banner_image'));
        if (is_array($image) && count($image) > 0) {
            $image = end($image);
            $url = $image['url'];
        }
        return $url;
    }

    /**
     * @param \Amasty\Promo\Model\Rule $validRule
     * @return mixed
     */
    public function getAlt(\Amasty\Promo\Model\Rule $validRule)
    {
        return $validRule->getData($this->getPosition() . '_banner_alt');
    }

    /**
     * @param \Amasty\Promo\Model\Rule $validRule
     * @return mixed
     */
    public function getHoverText(\Amasty\Promo\Model\Rule $validRule)
    {
        return $validRule->getData($this->getPosition() . '_banner_on_hover_text');
    }

    /**
     * @param \Amasty\Promo\Model\Rule $validRule
     * @return mixed|string
     */
    public function getLink(\Amasty\Promo\Model\Rule $validRule)
    {
        return $validRule->getData($this->getPosition() . '_banner_link') ? $validRule->getData($this->getPosition() . '_banner_link') : "#";
    }

    /**
     * @param \Amasty\Promo\Model\Rule $validRule
     * @return bool
     */
    public function isShowGiftImages(\Amasty\Promo\Model\Rule $validRule)
    {
        return (int)$validRule->getData($this->getPosition() . '_banner_show_gift_images') === 1;
    }

    /**
     * @param \Amasty\Promo\Model\Rule $validRule
     * @return mixed
     */
    public function getLabelImage(\Amasty\Promo\Model\Rule $validRule)
    {
        $url = null;
        $image = $this->serializerBase->unserialize($validRule->getData('label_image'));
        if (is_array($image) && count($image) > 0) {
            $image = end($image);
            $url = $image['url'];
        }
        return $url;
    }

    /**
     * @param \Amasty\Promo\Model\Rule $validRule
     * @return mixed
     */
    public function getLabelImageAlt(\Amasty\Promo\Model\Rule $validRule)
    {
        return $validRule->getData('label_image_alt');
    }

    /**
     * @return \Magento\Framework\Filter\Template
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPageTemplateProcessor()
    {
        if (!$this->pageTemplateProcessor) {
            $this->pageTemplateProcessor = $this->templateFilterFactory->create('Magento\Catalog\Model\Template\Filter');
        }
        return $this->pageTemplateProcessor;
    }

    /**
     * @param \Amasty\Promo\Model\Rule $validRule
     * @return $this|array
     */
    public function getProducts(\Amasty\Promo\Model\Rule $validRule)
    {
        $products = [];
        $promoSku = $validRule->getSku();
        if (!empty($promoSku)) {
            $products = $this->productCollectionFactory->create()
                ->addFieldToFilter('sku', ['in' => explode(",", $promoSku)])
                ->addUrlRewrite()
                ->addAttributeToSelect(['name', 'thumbnail',
                    $this->getAttributeHeader(), $this->getAttributeDescription()]);
        }

        return $products;
    }

    /**
     * @return mixed
     */
    public function getAttributeHeader()
    {
        return $this->config->getScopeValue('gift_images/attribute_header');
    }

    /**
     * @return mixed
     */
    public function getAttributeDescription()
    {
        return $this->config->getScopeValue('gift_images/attribute_description');
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->config->getScopeValue('gift_images/gift_image_width');
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->config->getScopeValue('gift_images/gift_image_height');
    }

    /**
     * @return \Magento\Catalog\Helper\Image
     */
    public function getImageHelper()
    {
        return $this->helperImage;
    }
}