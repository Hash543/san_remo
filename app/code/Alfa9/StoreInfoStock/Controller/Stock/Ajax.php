<?php

namespace Alfa9\StoreInfoStock\Controller\Stock;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Ajax
 * @package Alfa9\StoreInfoStock\Controller\Stock
 */
class Ajax extends \Magento\Framework\App\Action\Action {
    /**
     * @var \Alfa9\StorePickup\Helper\Data
     */
    private $storePickUpHelper;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * Ajax constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Alfa9\StorePickup\Helper\Data $storePickUpHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Alfa9\StorePickup\Helper\Data $storePickUpHelper
    ) {
        $this->storePickUpHelper = $storePickUpHelper;
        $this->productRepository = $productRepository;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute() {
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);

        $sku =  $this->getRequest()->getParam('sku');
        $pId = $this->getRequest()->getParam('product');
        $qty = $this->getRequest()->getParam('qty');
        $qtyMatrix = $this->getRequest()->getParam('qtyMatrix');
        if (!$qty) {
            if($qtyMatrix > 0) {
                $qty = $qtyMatrix;
            } else {
                $qty = 1;
            }
        }

        /** @var \Magento\Framework\View\LayoutInterface $layout */
        $layout = $this->_view->getLayout();

        /** @var \Alfa9\StoreInfoStock\Block\StockInfo $block */
        $block = $layout->createBlock(\Alfa9\StoreInfoStock\Block\StockInfo::class);

        try {
            if (!$sku) {
                $product = $this->productRepository->getById($pId);
                $sku = $product->getSku();
            }
            $stores = $this->storePickUpHelper->getStoresByProduct($sku, $qty);
        } catch (NoSuchEntityException $noSuchEntityException) {
            $stores = [];
        }
        $block->setStores($stores);
        return $resultRaw->setContents($block->toHtml());
    }
}