<?php

namespace WeltPixel\ProductPage\Helper;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @param int $storeId
     * @return boolean
     */
    public function productVersion($storeId = null) {
        return $this->scopeConfig->getValue('weltpixel_product_page/version/version', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int $storeId
     * @return boolean
     */
    public function moveDescriptionsTabs($storeId = null) {
        return $this->scopeConfig->getValue('weltpixel_product_page/general/move_description_tabs_under_info_area', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int $storeId
     * @return boolean
     */
    public function removeWishlist($storeId = null) {
        return $this->scopeConfig->getValue('weltpixel_product_page/general/remove_wishlist', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int $storeId
     * @return boolean
     */
    public function removeCompare($storeId = null) {
        return $this->scopeConfig->getValue('weltpixel_product_page/general/remove_compare', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getImageAreaWidth($storeId = null) {
        return $this->scopeConfig->getValue('weltpixel_product_page/general/image_area_width', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getProductInfoAreaWidth($storeId = null) {
        return $this->scopeConfig->getValue('weltpixel_product_page/general/product_info_area_width', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int $storeId
     * @return boolean
     */
    public function removeSwatchTooltip($storeId = null) {
        return !$this->scopeConfig->getValue('weltpixel_product_page/general/display_swatch_tooltip', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }


    /**
     * @param int $storeId
     * @return boolean
     */
    public function getTabsLayout($storeId = null) {
        return $this->scopeConfig->getValue('weltpixel_product_page/general/tabs_layout', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }


    /**
     * @param int $storeId
     * @return boolean
     */
    public function getTabsVersion($storeId = null) {
        return $this->scopeConfig->getValue('weltpixel_product_page/general/tabs_version', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int $storeId
     * @return boolean
     */
    public function getAccordionVersion($storeId = null) {
        return $this->scopeConfig->getValue('weltpixel_product_page/general/accordion_version', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int $storeId
     * @return boolean
     */
    public function getQtySelectMaxValue($storeId = null) {
        return $this->scopeConfig->getValue('weltpixel_product_page/general/qty_select_maxvalue', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int $storeId
     * @return array
     */
    public function getSwatchOptions($storeId = null) {
        return $this->scopeConfig->getValue('weltpixel_product_page/swatch', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int $storeId
     * @return array
     */
    public function getCssOptions($storeId = null) {
        return $this->scopeConfig->getValue('weltpixel_product_page/css', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

	/**
	 * @param int $storeId
	 * @return mixed
	 */
	public function getBackgroundArrows($storeId = null) {
        return $this->scopeConfig->getValue('weltpixel_product_page/gallery/arrows_bg', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
	}
}
