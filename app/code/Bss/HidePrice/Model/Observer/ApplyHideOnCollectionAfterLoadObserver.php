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
namespace Bss\HidePrice\Model\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class ApplyHideOnCollectionAfterLoadObserver implements ObserverInterface
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

    public function execute(EventObserver $observer)
    {
        $page=$this->request->getFullActionName();
        
        $collection = $observer->getEvent()->getCollection();
        foreach ($collection as $product) {
            $sku = $product->getSku();
            $productRepository = $this->productRepository->get($sku);
            if ($this->helper->activeHidePrice($productRepository)) {
                $product->setDisableAddToCart(true);
                if($this->helper->hidePriceActionActive($productRepository) != 2) {
                    $product->setCanShowPrice(false);
                }
            }            
        }
        return $this;
    }
}
