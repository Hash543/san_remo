<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Conditions
 */


namespace Amasty\Conditions\Plugin\Cart;

class CartTotalRepository
{
    const B2B_NAME = 'B2B';
    const ENTERPRISE_NAME = 'Enterprise';

    /**
     * Cart totals factory.
     *
     * @var \Magento\Quote\Api\Data\TotalsInterfaceFactory
     */
    private $totalsFactory;

    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var \Magento\Catalog\Helper\Product\ConfigurationPool
     */
    private $itemConverter;

    /**
     * @var \Magento\Quote\Api\CouponManagementInterface
     */
    private $couponService;

    /**
     * @var \Magento\Quote\Model\Cart\TotalsConverter
     */
    private $totalsConverter;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Magento\Quote\Api\Data\TotalsExtensionFactory
     */
    private $totalsExtensionFactory;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $metadata;

    public function __construct(
        \Magento\Quote\Api\Data\TotalsInterfaceFactory $totalsFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Quote\Api\CouponManagementInterface $couponService,
        \Magento\Quote\Model\Cart\TotalsConverter $totalsConverter,
        \Magento\Quote\Model\Cart\Totals\ItemConverter $converter,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Quote\Api\Data\TotalsExtensionFactory $totalsExtensionFactory,
        \Magento\Framework\App\ProductMetadataInterface $metadata
    ) {
        $this->totalsFactory = $totalsFactory;
        $this->quoteRepository = $quoteRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->couponService = $couponService;
        $this->totalsConverter = $totalsConverter;
        $this->itemConverter = $converter;
        $this->productMetadata = $productMetadata;
        $this->totalsExtensionFactory = $totalsExtensionFactory;
        $this->metadata = $metadata;
    }

    /**
     * @param \Magento\Quote\Model\Cart\CartTotalRepository $subject
     * @param \Closure $proceed
     * @param $cartId
     *
     * @return \Magento\Quote\Api\Data\TotalsInterface|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    public function aroundGet(\Magento\Quote\Model\Cart\CartTotalRepository $subject, \Closure $proceed, $cartId)
    {
        if (version_compare($this->productMetadata->getVersion(), '2.2.4', '>=')) {
            return $proceed($cartId);
        }

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        if ($quote->isVirtual()) {
            $addressTotalsData = $quote->getBillingAddress()->getData();
            $addressTotals = $quote->getBillingAddress()->getTotals();
        } else {
            $addressTotalsData = $quote->getShippingAddress()->getData();
            $addressTotals = $quote->getShippingAddress()->getTotals();
        }
        unset($addressTotalsData[\Magento\Framework\Api\ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]);

        /** @var \Magento\Quote\Api\Data\TotalsInterface $quoteTotals */
        $quoteTotals = $this->totalsFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $quoteTotals,
            $addressTotalsData,
            \Magento\Quote\Api\Data\TotalsInterface::class
        );
        $items = [];

        foreach ($quote->getAllVisibleItems() as $index => $item) {
            $items[$index] = $this->itemConverter->modelToDataObject($item);
        }

        $calculatedTotals = $this->totalsConverter->process($addressTotals);
        $quoteTotals->setTotalSegments($calculatedTotals);

        $amount = $quoteTotals->getGrandTotal() - $quoteTotals->getTaxAmount();
        $amount = $amount > 0 ? $amount : 0;
        $quoteTotals->setCouponCode($this->couponService->get($cartId));
        $quoteTotals->setGrandTotal($amount);
        $quoteTotals->setItems($items);
        $quoteTotals->setItemsQty($quote->getItemsQty());
        $quoteTotals->setBaseCurrencyCode($quote->getBaseCurrencyCode());
        $quoteTotals->setQuoteCurrencyCode($quote->getQuoteCurrencyCode());

        if ($this->isEnterprise()) {
            $quoteTotals = $this->setExtensionAttributes($quoteTotals, $quote);
        }

        return $quoteTotals;
    }

    /**
     * @param \Magento\Quote\Api\Data\TotalsInterface $quoteTotals
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return \Magento\Quote\Api\Data\TotalsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function setExtensionAttributes(
        \Magento\Quote\Api\Data\TotalsInterface $quoteTotals,
        \Magento\Quote\Model\Quote $quote
    ) {
        /** @var \Magento\Quote\Api\Data\TotalsExtensionInterface $extensionAttributes */
        $extensionAttributes = $quoteTotals->getExtensionAttributes();
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->totalsExtensionFactory->create();
        }

        $extensionAttributes->setRewardPointsBalance($quote->getRewardPointsBalance());
        $extensionAttributes->setRewardCurrencyAmount($quote->getRewardCurrencyAmount());
        $extensionAttributes->setBaseRewardCurrencyAmount($quote->getBaseRewardCurrencyAmount());

        $quoteTotals->setExtensionAttributes($extensionAttributes);

        return $quoteTotals;
    }

    /**
     * Check for enterprise or B2B edition
     *
     * @return bool
     */
    private function isEnterprise()
    {
        return $this->metadata->getEdition() === self::B2B_NAME
            || $this->metadata->getEdition() === self::ENTERPRISE_NAME;
    }
}
