<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Helper;

use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Filter\TranslitUrl;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\Shopbybrand\Model\BrandFactory;
use Mageplaza\Shopbybrand\Model\CategoryFactory;

/**
 * Class Data
 * @package Mageplaza\Osc\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'shopbybrand';
    /**
     * Image size default
     */
    const IMAGE_SIZE = '135x135';
    /**
     * General configuaration path
     */
    const GENERAL_CONFIGURATION = 'shopbybrand/general';
    /**
     * Brand sidebar configuration path
     */
    const BRAND_SIDEBAR_CONFIGURATION = 'sidebar';
    /**
     * Brand page configuration path
     */
    const BRAND_CONFIGURATION = 'brandpage';
    /**
     * Search brand configuration path
     */
    const BRAND_DETAIL_CONFIGURATION = 'brandview';
    /**
     * Brand media path
     */
    const BRAND_MEDIA_PATH = 'mageplaza/brand';
    /**
     * Default route name
     */
    const DEFAULT_ROUTE = 'brand';
    /**
     * Get Brand by
     */
    const CATEGORY = 'category';
    /**
     * Get Brand by
     */
    const BRAND_FIRST_CHAR = 'char';

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $_filter;

    /**
     * @var \Magento\Framework\Filter\TranslitUrl
     */
    protected $translitUrl;

    /**
     * @var \Mageplaza\Shopbybrand\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @type string
     */
    protected $_char = '';

    /**
     * @type \Mageplaza\Shopbybrand\Model\BrandFactory
     */
    protected $_brandFactory;

    /**
     * @type
     */
    protected $_brandCollection;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var Attribute
     */
    protected $_attribute;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Filter\TranslitUrl $translitUrl
     * @param \Magento\Framework\Filter\FilterManager $filter
     * @param \Mageplaza\Shopbybrand\Model\CategoryFactory $categoryFactory
     * @param \Mageplaza\Shopbybrand\Model\BrandFactory $brandFactory
     * @param \Magento\Framework\Registry $registry
     * @param Attribute $attribute
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectManager,
        TranslitUrl $translitUrl,
        FilterManager $filter,
        CategoryFactory $categoryFactory,
        BrandFactory $brandFactory,
        Registry $registry,
        Attribute $attribute
    ) {
        $this->_filter = $filter;
        $this->translitUrl = $translitUrl;
        $this->categoryFactory = $categoryFactory;
        $this->_brandFactory = $brandFactory;
        $this->registry = $registry;
        $this->_attribute = $attribute;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param $position
     *
     * @return bool
     */
    public function canShowBrandLink($position)
    {
        if (!$this->isEnabled()) {
            return false;
        }
        $positionConfig = explode(',', $this->getGeneralConfig('show_position'));

        return in_array($position, $positionConfig);
    }

    /**
     * @param null $brand
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBrandUrl($brand = null)
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $key = is_null($brand) ? '' : '/' . $this->processKey($brand);

        return $baseUrl . $this->getRoute() . $key . $this->getUrlSuffix();
    }

    /**
     * @param $brand
     *
     * @return string
     */
    public function processKey($brand)
    {
        if (!$brand) {
            return '';
        }
        $str = $brand->getUrlKey() ?: $brand->getDefaultValue();

        return $this->formatUrlKey($str);
    }

    /**
     * Format URL key from name or defined key
     *
     * @param string $str
     *
     * @return string
     */
    public function formatUrlKey($str)
    {
        return $this->_filter->translitUrl($str);
    }

    /**
     * @param $brand
     *
     * @return string
     */
    public function getBrandImageUrl($brand)
    {
        if ($brand->getImage()) {
            $image = $brand->getImage();
        } else if ($brand->getSwatchType() == \Magento\Swatches\Model\Swatch::SWATCH_TYPE_VISUAL_IMAGE) {
            $image = \Magento\Swatches\Helper\Media::SWATCH_MEDIA_PATH . $brand->getSwatchValue();
        } else if ($this->getBrandDetailConfig('default_image')) {
            $image = self::BRAND_MEDIA_PATH . '/' . $this->getBrandDetailConfig('default_image');
        } else {
            return \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Catalog\Helper\Image')
                ->getDefaultPlaceholderUrl('small_image');
        }

        return $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $image;
    }

    /**
     * Get Brand Title
     *
     * @return string
     */
    public function getBrandTitle()
    {
        return $this->getGeneralConfig('link_title') ?: __('Brands');
    }

    /**
     * Retrieve Brand description
     *
     * @param $brand
     * @param bool|false $short
     *
     * @return mixed
     */
    public function getBrandDescription($brand, $short = false)
    {
        if ($short) {
            return $brand->getShortDescription() ?: '';
        }

        return $brand->getDescription() ?: '';
    }

    /**
     * @param string $code
     * @param null $store
     *
     * @return mixed
     */
    public function getGeneralConfig($code = '', $store = null)
    {
        $code = $code ? self::GENERAL_CONFIGURATION . '/' . $code : self::GENERAL_CONFIGURATION;

        return $this->getConfigValue($code, $store);
    }

    /**
     * Retrieve Attribute code for Brand
     *
     * @param null $store
     *
     * @return mixed
     */
    public function getAttributeCode($store = null)
    {
        return $this->getGeneralConfig('attribute', $store);
    }

    /**
     * Retrieve route name for brand.
     * If empty, default 'brands' will be used
     *
     * @param null $store
     *
     * @return string
     */
    public function getRoute($store = null)
    {
        $route = $this->getGeneralConfig('route', $store) ?: self::DEFAULT_ROUTE;

        return $this->formatUrlKey($route);
    }

    /**
     * Retrieve category rewrite suffix for store
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getUrlSuffix($storeId = null)
    {
        return $this->scopeConfig->getValue(
            \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param string $code
     * @param null $store
     *
     * @return mixed
     */
    public function getBrandConfig($code = '', $store = null)
    {
        $code = $code ? self::BRAND_CONFIGURATION . '/' . $code : self::BRAND_CONFIGURATION;

        return $this->getModuleConfig($code, $store);
    }

    /**
     * @param string $group
     * @param null $store
     *
     * @return array
     */
    public function getImageSize($group = '', $store = null)
    {
        $imageSize = $this->getBrandConfig($group . 'image_size', $store) ?: self::IMAGE_SIZE;

        return explode('x', $imageSize);
    }

    /**
     * @param string $code
     * @param null $store
     *
     * @return mixed
     */
    public function getFeatureConfig($code = '', $store = null)
    {
        $code = $code ? 'feature' . '/' . $code : 'feature';

        return $this->getBrandConfig($code, $store);
    }

    /**
     * @param null $store
     *
     * @return mixed
     */
    public function enableFeature($store = null)
    {
        return $this->getFeatureConfig('enable', $store);
    }

    /**
     * @param string $code
     * @param null $store
     *
     * @return mixed
     */
    public function getSearchConfig($code = '', $store = null)
    {
        $code = $code ? 'search' . '/' . $code : 'search';

        return $this->getBrandConfig($code, $store);
    }

    /**
     * @param null $store
     *
     * @return mixed
     */
    public function enableSearch($store = null)
    {
        return $this->getSearchConfig('enable', $store);
    }

    /**
     * @param string $code
     * @param null $store
     *
     * @return mixed
     */
    public function getBrandDetailConfig($code = '', $store = null)
    {
        $code = $code ? self::BRAND_DETAIL_CONFIGURATION . '/' . $code : self::BRAND_DETAIL_CONFIGURATION;

        return $this->getModuleConfig($code, $store);
    }

    /**
     * @param string $code
     * @param null $store
     *
     * @return mixed
     */
    public function getSidebarConfig($code = '', $store = null)
    {
        $code = $code ? self::BRAND_SIDEBAR_CONFIGURATION . '/' . $code : self::BRAND_SIDEBAR_CONFIGURATION;

        return $this->getModuleConfig($code, $store);
    }

    /**
     * @return array
     */
    public function getAllBrandsAttributeCode()
    {
        $stores = $this->storeManager->getStores();
        $attributeCodes = [];
        array_push($attributeCodes, $this->getAttributeCode());
        foreach ($stores as $store) {
            array_push($attributeCodes, $this->getAttributeCode($store->getId()));
        }
        $attributeCodes = array_unique($attributeCodes);

        return $attributeCodes;
    }

    /**
     * generate url_key for brand category
     *
     * @param $name
     * @param $count
     *
     * @return string
     */
    public function generateUrlKey($name, $count)
    {
        $name = $this->strReplace($name);
        $text = $this->translitUrl->filter($name);
        if ($count == 0) {
            $count = '';
        }
        if (empty($text)) {
            return 'n-a' . $count;
        }

        return $text . $count;
    }

    /**
     * replace vietnamese characters to english characters
     *
     * @param $str
     *
     * @return mixed|string
     */
    public function strReplace($str)
    {
        $str = trim(mb_strtolower($str));
        $str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $str);
        $str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $str);
        $str = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $str);
        $str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $str);
        $str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $str);
        $str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $str);
        $str = preg_replace('/(đ)/', 'd', $str);

        return $str;
    }

    public function getCustomBrandUrl($brandurl)
    {
        $brandurl = $this->strReplace($brandurl);
        // replace non letter or digits by -
        
        $brandurl = str_replace("&#039;","-",$brandurl);

        $brandurl = preg_replace('~[^\pL\d]+~u', '-', $brandurl);

        // transliterate
        $brandurl = iconv('utf-8', 'us-ascii//TRANSLIT', $brandurl);

        // remove unwanted characters
        $brandurl = preg_replace('~[^-\w]+~', '', $brandurl);

        // trim
        $brandurl = trim($brandurl, '-');

        // remove duplicate -
        $brandurl = preg_replace('~-+~', '-', $brandurl);

        if (empty($brandurl)) {
            return 'n-a';
        }

        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $key = '/' . $brandurl;

        return $baseUrl . $this->getRoute() . $key . $this->getUrlSuffix();
    }


    /**
     * @param null $cat
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCatUrl($cat = null)
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        $brandRoute = $this->getRoute();
        $key = is_null($cat) ? '' : '/' . $this->processKey($cat);

        return $baseUrl . $brandRoute . '/' . self::CATEGORY . $key . $this->getUrlSuffix();
    }

    /**
     * @param $routePath
     * @param $routeSize
     *
     * @return bool
     */
    public function isBrandRoute($routePath, $routeSize)
    {
        if ($routeSize > 3) {
            return false;
        }

        $urlSuffix = $this->getUrlSuffix();
        $brandRoute = $this->getRoute();
        if ($urlSuffix) {
            $brandSuffix = strpos($brandRoute, $urlSuffix);
            if ($brandSuffix) {
                $brandRoute = substr($brandRoute, 0, $brandSuffix);
            }
        }

        return ($routePath[0] == $brandRoute);
    }

    /**
     * @param $urlKey
     *
     * @return \Mageplaza\Shopbybrand\Model\Category|null
     */
    public function getCategoryByUrlKey($urlKey)
    {
        $cat = $this->categoryFactory->create()->load($urlKey, 'url_key');
        if ($cat) {
            return $cat->getId();
        }

        return null;
    }

    /**
     * @param null $type
     * @param null $ids
     * @param null $char
     *
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection|\Mageplaza\Shopbybrand\Model\Brand
     */
    public function getBrandList($type = null, $ids = null, $char = null)
    {
        $brands = $this->_brandFactory->create();
        switch ($type) {
            //Get Brand List by Category
            case self::CATEGORY:
                $list = $brands->getBrandCollection(null, ['main_table.option_id' => ['in' => $ids]]);
                break;
            //Get Brand List Filtered by Brand First Char
            case self::BRAND_FIRST_CHAR:
                $sqlCond = $this->checkCharacter($char);
                $list = $brands->getBrandCollection(null, !empty($ids) ? ['main_table.option_id' => ['in' => $ids]] : [], $sqlCond);
                break;
            default:
                //Get Brand List
                $list = $brands->getBrandCollection();
        }

        return $list;
    }

    /**
     * Get condition for sql
     *
     * @param $char
     *
     * @return string
     */
    public function checkCharacter($char)
    {
        $specialChar = ['!', '"', '#', '$', '&', '(', ')', '*', '+', ',', '-', '.', '/', ':', ';', '<', '=', '>', '?', '@', '[', ']', '^', '_', '`', '{', '|', '}', '~'];
        if (in_array($char, $specialChar)) {
            $sqlCond = "IF(tsv.value_id > 0, tsv.value, tdv.value) LIKE " . "'" . $char . "%'";
        } else {
            if ($char == "'") {
                $sqlCond = "IF(tsv.value_id > 0, tsv.value, tdv.value) LIKE " . '"' . $char . '%"';
            } else {
                $sqlCond = "IF(tsv.value_id > 0, tsv.value, tdv.value) REGEXP BINARY " . "'^" . $char . "'";
            }
        }

        return $sqlCond;
    }

    /**
     * @return mixed
     */
    public function getCategoryList()
    {
        $collection = $this->categoryFactory->create()
            ->getCollection()->addFieldToFilter('status', '1');

        return $collection;
    }

    /**
     * @param $brand
     *
     * @return string
     */
    public function getFilterClass($brand)
    {
        //vietnamese unikey format
        if ($this->getBrandConfig('brand_filter/encode_key')) {
            $firstChar = mb_substr($brand->getValue(), 0, 1, $this->getBrandConfig('brand_filter/encode_key'));
        } else {
            $firstChar = mb_substr($brand->getValue(), 0, 1, 'UTF-8');
        }

        return is_numeric($firstChar) ? 'num' . $firstChar : ucfirst($firstChar);
    }

    /**
     * @param $optionId
     *
     * @return string
     */
    public function getCatFilterClass($optionId)
    {
        $catName = [];
        $sql = 'brand_cat_tbl.option_id IN (' . $optionId . ')';
        $group = 'main_table.cat_id';

        $collection = $this->categoryFactory->create()->getCategoryCollection($sql, $group);
        foreach ($collection as $item) {
            $str = str_replace(" ", "_", $item->getName());
            $str = str_replace("*", "_", $str);
            $str = str_replace("/", "_", $str);
            $str = str_replace('\\', "_", $str);
            $catName[] = 'cat' . $str;
        }
        $result = implode(' ', $catName);

        return $result;
    }

    /**
     * Is show quick view near Brand name
     *
     * @return mixed
     */
    public function showQuickView()
    {
        return $this->getBrandConfig('show_quick_view');
    }

    /**
     * @return string
     */
    public function getQuickViewUrl()
    {
        return $this->_getUrl('');
    }

    /**
     * @param null $brand
     *
     * @return string
     */
    public function getQuickview($brand = null)
    {
        $key = is_null($brand) ? '' : '/' . $this->processKey($brand);

        return $this->getRoute() . $key . $this->getUrlSuffix();
    }

    /**
     * @return mixed
     */
    public function getProductBrand()
    {
        if ($optionId = $this->getCurrentProduct()->getData($this->getAttributeCode())) {
            return $this->_brandFactory->create()->loadByOption($optionId);
        }

        return null;
    }

    /**
     * Get current product
     * @return mixed
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * Get current brand
     * @return mixed
     */
    public function getBrand()
    {
        return $this->registry->registry('current_brand');
    }

    /**
     * convert strings in an array to uppercase
     *
     * @param $array
     *
     * @return array|null
     */
    public function converUppercase($array)
    {
        $input = array_flip($array);
        $input = array_change_key_case($input, CASE_UPPER);
        $input = array_flip($input);

        return $input;
    }

    /**
     * Get attribute id
     *
     * @param $code
     *
     * @return int
     */
    public function getAttributeId($code)
    {
        return $this->_attribute->getIdByCode(\Magento\Catalog\Model\Product::ENTITY, $code);
    }
}
