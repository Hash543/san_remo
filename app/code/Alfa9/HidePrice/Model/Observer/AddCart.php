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
namespace Alfa9\HidePrice\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\App\ActionFlag;

class AddCart implements ObserverInterface
{
    protected $helper;
    protected $request;
    protected $productRepository;
    protected $storeManager;
    protected $response;
    protected $actionFlag;
    protected $messageManager;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Bss\HidePrice\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Api\ProductRepositoryInterface $pr,
        \Magento\Framework\App\Response\Http $response,
        ActionFlag $actionFlag,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $data = []
    )
    {
        $this->helper = $helper;
        $this->request = $request;
        $this->productRepository = $pr;
        $this->storeManager = $context->getStoreManager();
        $this->response = $response;
        $this->actionFlag = $actionFlag;
        $this->messageManager = $messageManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $addedItemId = $observer->getRequest()->getParam('product');
        $controller = $observer->getControllerAction();
        $storeId = $this->storeManager->getStore()->getId();
        $mainProduct = $this->productRepository->getById($addedItemId, false, $storeId);
//        if ($this->helper->activeHidePrice($mainProduct)) {
//            $observer->getRequest()->setParam('qty', 0);
//            $observer->getRequest()->setParam('product', 0);
//            $this->messageManager->addError(__('This product cannot be added to your cart.'));
//            return;
//        }

        if(!is_null($observer->getRequest()->getParam('related_product')) && $observer->getRequest()->getParam('related_product') != '') {
            $relatedProducts = explode(",", $observer->getRequest()->getParam('related_product'));
            foreach ($relatedProducts as $relatedProduct) {
                $product = $this->productRepository->getById($relatedProduct, false, $storeId);
                if ($this->helper->activeHidePrice($product)) {
                    $productName = $product->getName();
                    $observer->getRequest()->setParam('qty', 0);
                    $observer->getRequest()->setParam('related_product', null);
                    $observer->getRequest()->setParam('product', 0);
                    $this->messageManager->addError(__('Product %1 cannot be added to your cart with main product.',$productName));
                    break;
                }
            }
        }
    }
}

