<?php
/**
 * @author Israel Yasis
 */
namespace PSS\PaymentPoints\Observer;

use Magento\Framework\Exception\NoSuchEntityException;
/**
 * After save the shipping method, we change the points automatically
 */
class ApplyPoints implements \Magento\Framework\Event\ObserverInterface  {
    /**
     * @var \Mageplaza\RewardPoints\Helper\Calculation
     */
    protected $calculation;
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * AfterSaveShippingMethod constructor.
     * @param \Mageplaza\RewardPoints\Helper\Calculation $calculation
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Mageplaza\RewardPoints\Helper\Calculation $calculation,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->cartRepository = $cartRepository;
        $this->calculation = $calculation;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {

        /** @var \Magento\Checkout\Model\Cart $cart */
        $cart = $observer->getEvent()->getData('cart');
        $quote = $cart->getQuote();
        /**
         * The points are only apply automatically in the store of Fidelizacion
         */
        if($quote && $quote->getQuoteCurrencyCode() == \PSS\Loyalty\Helper\Data::CURRENCY_CODE
            && $this->calculation->isAllowSpending($quote)) {
            try {
                $maxPoints = $this->calculation->getMaxSpendingPointsByRate($quote);
            } catch (NoSuchEntityException $noSuchEntityException) {
                $this->logger->info(__("Error applying the points by observer"));
                $maxPoints = 0;
            }
            $quote->setData('mp_reward_spent', $maxPoints)->setData('mp_reward_applied', 'rate');
            $quote->collectTotals();
            $this->cartRepository->save($quote);
        }
    }
}