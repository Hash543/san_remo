<?php

namespace Alfa9\HidePrice\Helper;

class HidePriceHelper extends \Bss\HidePrice\Helper\Data
{
    public function getHidepriceMessage($product)
    {
        $message = '';
        if ($product->getHidepriceMessage() && $product->getHidepriceAction() > 0) {
            $message = $product->getHidepriceMessage();
        } else {
            $_message = $this->_scopeConfig->getValue(
                self::XML_PATH_HIDE_PRICE_TEXT,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        if ($this->getHidePriceUrl($product)) { //product have hide price url
            return '<a href="' . trim($this->getHidePriceUrl($product)) . '">' . $message . '</a>';
        } else {
            return $message;
        }
    }

    public function activeHidePrice($product, $storeId = null)
    {
        if($this->isMue($product)) {
            return $this->scopeConfig->getValue('bss_hide_price/hideprice_global/action');
        }
        return parent::activeHidePrice($product, $storeId);
    }


    public function hidePriceActionActive($product)
    {
        if($this->isMue($product)) { return 2; }
        return parent::hidePriceActionActive($product);
    }

    /**
     * @param $product
     * @return bool
     */
    protected function isMue($product) {

        $muePrefix = substr($product->getSku(), 0, 3);
        return strtoupper($muePrefix) === 'MUE';
    }
 }