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

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;

class WishlistCartController
{
    protected $helper;
    protected $productRepository;
    protected $itemFactory;
    protected $resultFactory;
    protected $messageManager;

    public function __construct(
        \Bss\HidePrice\Helper\Data $helper,
        \Magento\Catalog\Api\ProductRepositoryInterface $pr,
        \Magento\Wishlist\Model\ItemFactory $itemFactory,
        ResultFactory $resultFactory,
        MessageManagerInterface $messageManager

    )
    {
        $this->helper = $helper;
        $this->productRepository = $pr;
        $this->itemFactory = $itemFactory;
        $this->resultFactory = $resultFactory;
        $this->messageManager = $messageManager;
    }


    /**
     * not allow add cart if hideprice is enable
     */
    public function aroundExecute($subject, \Closure $proceed)
    {
        $itemId = $subject->getRequest()->getParam('item');
        $item = $this->itemFactory->create()->load($itemId);
        $product = $this->productRepository->getById($item->getProductId());
        if (!$this->helper->activeHidePrice($product)) {
            return $proceed();
        } else {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('*/*/');
            $this->messageManager->addErrorMessage(__('We can\'t specify a product.'));
            return $resultRedirect;
        }
    }
}
