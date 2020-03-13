<?php
/**
 * @author Israel Yasis
 */
namespace PSS\AddStepToCheckout\Observer;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class LoadCustomerQuoteBefore
 * @package PSS\AddStepToCheckout\Observer
 */
class LoadCustomerQuoteBefore implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * LoadCustomerQuoteBefore constructor.
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->redirect = $redirect;
        $this->quoteRepository = $quoteRepository;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        if(!$this->customerSession->getCustomerId()) {
            return $this;
        }
        /** @var \Magento\Checkout\Model\Session $checkoutSession */
        $checkoutSession = $observer->getEvent()->getData('checkout_session');

        $quote = $checkoutSession->getQuote();
        if($quote && count($quote->getItems()) > 0) {
            /** @var \Magento\Quote\Model\Quote $customerQuote */
            try {
                $customerQuote = $this->quoteRepository->getForCustomer($this->customerSession->getCustomerId());
            } catch (NoSuchEntityException $e) {
                $customerQuote = null;
            }
            if($customerQuote && $customerQuote->getId() && $checkoutSession->getQuoteId() != $customerQuote->getId()) {
                $customerQuote->setStoreId($this->getStoreId());
                $customerQuote->removeAllItems();
                try {
                    $this->quoteRepository->save($customerQuote);
                } catch (\Exception $exception) {
                    $this->logger->info(__("Unable to remove the items from the cart of the customer quote_id: %1", $customerQuote->getId()));
                }
            }
        }
        return $this;
    }

    /**
     * Get Store
     * @return int
     */
    private function getStoreId() {
        try {
            $storeId = $this->storeManager->getStore()->getId();
        }catch (NoSuchEntityException $exception) {
            $storeId = 0;
        }
        return $storeId;
    }
}