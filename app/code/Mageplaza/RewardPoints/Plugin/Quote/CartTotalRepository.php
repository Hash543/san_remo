<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPoints
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPoints\Plugin\Quote;

use Magento\Framework\App\RequestInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\TotalsExtensionFactory;
use Mageplaza\RewardPoints\Helper\Calculation;
use Mageplaza\RewardPoints\Model\Source\DisplayPointLabel;

/**
 * Class CartTotalRepository
 * @package Mageplaza\RewardPoints\Plugin\Quote
 */
class CartTotalRepository
{
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var TotalsExtensionFactory
     */
    protected $totalExtensionFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Calculation
     */
    protected $calculation;

    /**
     * CartTotalRepository constructor.
     *
     * @param CartRepositoryInterface $quoteRepository
     * @param TotalsExtensionFactory $totalExtensionFactory
     * @param RequestInterface $request
     * @param Calculation $helper
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        TotalsExtensionFactory $totalExtensionFactory,
        RequestInterface $request,
        Calculation $helper
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->totalExtensionFactory = $totalExtensionFactory;
        $this->request = $request;
        $this->calculation = $helper;
    }

    /**
     * @param CartTotalRepositoryInterface $subject
     * @param \Closure $proceed
     * @param $cartId
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundGet(CartTotalRepositoryInterface $subject, \Closure $proceed, $cartId)
    {
        /** @var \Magento\Quote\Api\Data\TotalsInterface $quoteTotals */
        $quoteTotals = $proceed($cartId);

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        $storeId = $quote->getStoreId();
        if (!$this->calculation->isEnabled($storeId)
            || !$this->calculation->isModuleOutputEnabled()
            || !$this->calculation->isRewardAccountActive()
        ) {
            return $quoteTotals;
        }

        $spendingConfig = [];
        if ($this->calculation->isAllowSpending($quote)) {
            $spendingConfig = $this->calculation->getSpendingConfiguration($quote);
            $spendingConfig['isCheckoutCart'] = $this->request->getFullActionName() == 'checkout_cart_index';
            $spendingConfig['useMaxPoints'] = (bool) $this->calculation->getConfigSpending('use_max_point', $storeId);
        }

        $pointHelper = $this->calculation->getPointHelper();
        $isLabelBefore = $pointHelper->getPointLabelPosition($storeId) == DisplayPointLabel::BEFORE_AMOUNT;

        $rewardConfig = [
            'pattern'  => [
                'single' => $isLabelBefore ? $pointHelper->getPointLabel($storeId) . '{point}'
                    : '{point}' . $pointHelper->getPointLabel($storeId),
                'plural' => $isLabelBefore ? $pointHelper->getPluralPointLabel($storeId) . '{point}'
                    : '{point}' . $pointHelper->getPluralPointLabel($storeId)
            ],
            'balance'  => $this->calculation->getAccountHelper()->get()->getBalance(),
            'spending' => $spendingConfig
        ];

        /** @var \Magento\Quote\Api\Data\TotalsExtensionInterface $totalsExtension */
        $totalsExtension = $quoteTotals->getExtensionAttributes() ?: $this->totalExtensionFactory->create();
        $totalsExtension->setRewardPoints(Calculation::jsonEncode($rewardConfig));

        $quoteTotals->setExtensionAttributes($totalsExtension);

        return $quoteTotals;
    }
}
