<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    Bss_HidePrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HidePrice\Helper;

use Magento\Framework\App\Action\Action;
use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const XML_PATH_ENABLED = 'bss_hide_price/general/enable';
    const XML_PATH_SELECTOR = 'bss_hide_price/general/selector';
    const XML_PATH_HIDE_PRICE_ACTION = 'bss_hide_price/hideprice_global/action';
    const XML_HIDE_PRICE_CATEGORIES = 'bss_hide_price/hideprice_global/categories';
    const XML_HIDE_PRICE_CUSTOMERS = 'bss_hide_price/hideprice_global/customers';
    const XML_PATH_HIDE_PRICE_TEXT = 'bss_hide_price/hideprice_global/text';
    const XML_PATH_HIDE_PRICE_URL = 'bss_hide_price/hideprice_global/hide_price_url';

    protected $_scopeConfig;
    protected $productRepository;
    protected $storeManagerInterface;
    protected $urlBuilder;
    protected $customerSession;
    protected $_productloader;
    protected $configurableData;
    protected $registry;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Bss\HidePrice\Model\Request $request
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Api\ProductRepositoryInterface $pr,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableData,
        \Magento\Customer\Model\Session $customerSession
    )
    {
        $this->productRepository = $pr;
        parent::__construct($context);
        $this->registry = $registry;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->urlBuilder = $context->getUrlBuilder();
        $this->storeManagerInterface = $storeManagerInterface;
        $this->configurableData = $configurableData;
        $this->customerSession = $customerSession;

    }

    public function isEnable($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getSelector($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_SELECTOR,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getHidePriceAction($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_HIDE_PRICE_ACTION,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getHidePriceCategories($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_HIDE_PRICE_CATEGORIES,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getHidePriceCustomers($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_HIDE_PRICE_CUSTOMERS,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getItemProduct($itemId)
    {
        return $this->productRepository->getById($itemId, false);
    }

    public function callProduct()
    {
        return $this->registry->registry('product');
    }

    public function getCustomerGroupId()
    {
        $ObjectManager= \Magento\Framework\App\ObjectManager::getInstance();
        $context = $ObjectManager->get('Magento\Framework\App\Http\Context');
        $isLoggedIn = $context->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        if($isLoggedIn) return $this->customerSession->getCustomer()->getGroupId();
        return 0;
    }

    public function getHidepriceMessage($product)
    {
        $message = '';
        if ($product->getHidepriceMessage() && $product->getHidepriceAction() > 0 ){
            $message = $product->getHidepriceMessage();
        }else{
            $_message = $this->_scopeConfig->getValue(
                    self::XML_PATH_HIDE_PRICE_TEXT,
                    ScopeInterface::SCOPE_STORE
                    );
            if($_message){
                $message = $_message;
            }else{
                $message = __('Please contact us for price.');
            }
        }
        if ($this->getHidePriceUrl($product)) { //product have hide price url
            return '<a href="'.trim($this->getHidePriceUrl($product)).'">'.$message.'</a>';
        } else {
            return $message;
        }
    }

    public function getHidePriceActionProduct($product)
    {
        return $product->getHidepriceAction();
    }

    public function getHidePriceUrl($product)
    {
        if(!trim($product->getHidepriceUrl()) && ($product->getHidepriceAction() == 0 || !$product->getHidepriceAction())) {
            $hidePriceUrl = $this->_scopeConfig->getValue(
                self::XML_PATH_HIDE_PRICE_URL,
                ScopeInterface::SCOPE_STORE
            );
            if(!trim($hidePriceUrl)) {
                return false;
            }

            return trim($hidePriceUrl);
        }
        return trim($product->getHidepriceUrl());
    }

    public function filterArray($string)
    {
        $array = explode(',', $string);
        $newArray = array_filter($array, function ($value) {
            return $value !== '';
        });
        return $newArray;
    }

    public function activeHidePrice($product, $storeId = null)
    {
        $hidePriceCustomersGroupProduct = $this->filterArray($product->getHidepriceCustomergroup());

        $hidePriceCategories = $this->filterArray($this->getHidePriceCategories());
        $hidePriceCustomers = $this->filterArray($this->getHidePriceCustomers());

        $productCategories = array_filter($product->getCategoryIds());
        $customerGroup = $this->getCustomerGroupId();

        if ($this->isEnable($storeId)) {
            if ($product->getHidepriceAction() == -1) { // product disabled
                return false;
            } elseif ($product->getHidepriceAction() == 0) { // global config
                $hidePrice = (!empty(array_intersect($productCategories, $hidePriceCategories)) && !empty($hidePriceCustomers) && in_array($customerGroup, $hidePriceCustomers))
                    || (!empty(array_intersect($productCategories, $hidePriceCategories)) && empty($hidePriceCustomers));
                if ($hidePrice) {
                    return true;
                } else {
                    return false;
                }
            } else { // product config
                if (!empty($hidePriceCustomersGroupProduct)
                    && is_array($hidePriceCustomersGroupProduct)
                    && count($hidePriceCustomersGroupProduct) == 1 
                    && current($hidePriceCustomersGroupProduct) == -1) { //proudct not set customer group
                    return false;
                } else { // check product setting
                    if (!empty($hidePriceCustomersGroupProduct) && is_array($hidePriceCustomersGroupProduct) && in_array($customerGroup, $hidePriceCustomersGroupProduct)){
                        return true;
                    } else {
                        return false;
                    }
                }
            }
        } else { // disabled
            return false;
        }
    }

    public function hidePriceActionActive($product)
    {
        if ($this->isEnable()) {
            if ($product->getHidepriceAction() == -1) {
                return 0;
            } elseif ($product->getHidepriceAction() == 0) {
                return $this->getHidePriceAction();
            } else {
                return $product->getHidepriceAction();
            }
        } else {
            return 0;
        }
    }

    public function getAllData($productEntityId)
    {
        $result = [];
        $map_r = [];
        $parentProduct = $this->configurableData->getChildrenIds($productEntityId);
        $product = $this->productRepository->getById($productEntityId);
        if($this->activeHidePrice($product))
            return $result;
        $parentAttribute = $this->configurableData->getConfigurableAttributes($product);
        $result['entity'] = $productEntityId;
        $product_child_IdFirst = reset($parentProduct[0]);
        $product_child = $this->productRepository->getById($product_child_IdFirst);
        foreach ($parentAttribute as $attrKey => $attrValue) {
            foreach ($product_child->getAttributes()[$attrValue->getProductAttribute()->getAttributeCode()]
                ->getOptions() as $tvalue) {
                $result['map'][$attrValue->getAttributeId()]['label'] = $attrValue->getLabel();
                $result['map'][$attrValue->getAttributeId()][$tvalue->getValue()] = $tvalue->getLabel();
                $map_r[$attrValue->getAttributeId()][$tvalue->getLabel()] = $tvalue->getValue();
            }
        }
        
        foreach ($parentProduct[0] as $simpleProduct) {
            $childProduct = [];
            $childProduct['entity'] = $simpleProduct;
            $child = $this->productRepository->getById($childProduct['entity']);
            $childProduct['hide_price'] = $this->activeHidePrice($child);
            if($childProduct['hide_price']) {
                $childProduct['hide_price_content'] = '<h2 id="hideprice_text_'.$child->getId().'" class="hideprice_text">'. $this->getHidepriceMessage($child).'</h2>';
                $childProduct['show_price'] = $this->hidePriceActionActive($child) != 2;
            }else{
                $childProduct['hide_price_content'] = false;
                $childProduct['show_price'] = false;
            }
            $key = '';
            foreach ($parentAttribute as $attrKey => $attrValue) {
                $attrLabel = $attrValue->getProductAttribute()->getAttributeCode();
                $childRow = $child->getAttributes()[$attrLabel]->getFrontend()->getValue($child);
                if ($childRow) {
                    $key .= $map_r[$attrValue->getAttributeId()][$childRow] . '_';
                }
            }
            $result['child'][$key] = $childProduct;
        }
        return $result;
    }
}
