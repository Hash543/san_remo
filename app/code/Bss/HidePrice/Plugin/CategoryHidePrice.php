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
namespace Bss\HidePrice\Plugin;

class CategoryHidePrice
{
    protected $helper;
    protected $productRepository;
    protected $request;

    public function __construct(
        \Bss\HidePrice\Helper\Data $helper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->helper=$helper;
        $this->productRepository = $productRepository;
        $this->request = $request;
    }

    public function afterToHtml($subject, $result)
    {
        $page=$this->request->getFullActionName();
        if ($page=="catalog_product_view") {
            return $result;
        }
        
        $product = $subject->getSaleableItem();
        $sku = $product->getSku();
        $product = $this->productRepository->get($sku);
        
        $showPrice = true;
        if ($this->helper->activeHidePrice($product)) {
            if($this->helper->hidePriceActionActive($product) == 2)
            {
                $showPrice = false;
            }
            $button = '<div class="hideprice_text hideprice_text_'.$product->getId().'">'. $this->helper->getHidepriceMessage($product).'</div>
                <script type="text/javascript">
                    require(["jquery"], function($){
                        $(".product-item").trigger("contentUpdated");
                    });                    
                </script>
                <script type="text/x-magento-init">
                    {
                        ".hideprice_text_'.$product->getId().'": {
                            "Bss_HidePrice/js/hide_price": {
                                "selector" : "'.$this->helper->getSelector().'",
                                "showPrice" : "'.$showPrice.'"
                            }
                        }
                    }
                </script>';
            if($this->helper->hidePriceActionActive($product) == 1) {
                return $button;
            }
            return $result.$button;
        }
        
        return $result;
    }
}
