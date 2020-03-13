<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Plugin\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Controller\Cart\Add;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\RewardPointsUltimate\Helper\Data as HelperData;
use Mageplaza\RewardPointsUltimate\Helper\SellPoint;
use Psr\Log\LoggerInterface;

/**
 * Class BeforeAdd
 * @package Mageplaza\RewardPointsUltimate\Plugin\Product
 */
class BeforeAdd
{
    /**
     * @var \Mageplaza\RewardPointsUltimate\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Mageplaza\RewardPointsUltimate\Helper\SellPoint
     */
    protected $sellPoint;

    /**
     * BeforeAdd constructor.
     *
     * @param HelperData $helperData
     * @param LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param ManagerInterface $messageManager
     * @param SellPoint $sellPoint
     */
    public function __construct(
        HelperData $helperData,
        LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        ManagerInterface $messageManager,
        SellPoint $sellPoint
    ) {
        $this->helperData = $helperData;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
        $this->sellPoint = $sellPoint;
    }

    /**
     * @param Add $subject
     * @param callable $proceed
     *
     * @return mixed
     */
    public function aroundExecute(Add $subject, callable $proceed)
    {
        try {
            $isProcessExecute = true;
            $productId = (int) $subject->getRequest()->getParam('product');
            if ($this->helperData->isEnabled() && $productId) {
                $storeId = $this->storeManager->getStore()->getId();
                $product = $this->productRepository->getById($productId, false, $storeId);
                if ($product->getMpRewardSellProduct() > 0) {
                    if ($this->helperData->getAccountHelper()->isCustomerLoggedIn()) {
                        $sellPoints = $product->getMpRewardSellProduct() * $subject->getRequest()->getParam('qty', 1);
                        if (!$this->sellPoint->isValid($sellPoints)) {
                            $this->messageManager->addNoticeMessage(__('You haven\'t enough point to add this product!'));
                            $isProcessExecute = false;
                        }
                    } else {
                        $isProcessExecute = false;
                        $this->messageManager->addNoticeMessage(__('Please sign in to add the product!'));
                    }
                }
            }

            if ($isProcessExecute) {
                return $proceed();
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $subject->getResponse()->representJson(HelperData::jsonEncode([]));
    }
}
