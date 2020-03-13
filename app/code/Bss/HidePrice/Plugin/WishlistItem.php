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

class WishlistItem
{
    protected $helper;
    protected $productRepository;
    protected $request;

    public function __construct(
        \Bss\HidePrice\Helper\Data $helper,
        \Magento\Catalog\Api\ProductRepositoryInterface $pr,
        \Magento\Framework\App\Request\Http $request
    )
    {
        $this->helper = $helper;
        $this->productRepository = $pr;
        $this->request = $request;
    }


    /**
     * not allow add cart if hideprice is enable
     */
    public function aroundGetItemCollection($subject, \Closure $proceed)
    {
        if($this->request->getFullActionName() == 'wishlist_index_allcart'){
            $itemCollection = $proceed();
            foreach ($itemCollection as $item){
                $productId = $item->getProductId();
                $product = $this->productRepository->getById($productId);
                if ($this->helper->activeHidePrice($product)){
                    $itemCollection->removeItemByKey($item->getId());
                }
            }
            $result = $itemCollection;
        }else{
            $result = $proceed();
        }
        return $result;
    }
}
