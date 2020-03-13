<?php
/**
 * @author Israel Yasis
 */
namespace PSS\CRM\Observer\TicketService;


class QueryPoints implements \Magento\Framework\Event\ObserverInterface {
    /**
     * @var \PSS\CRM\Api\TicketRepositoryInterface
     */
    protected $ticketRepository;
    /**
     * @var \PSS\CRM\Helper\TicketService
     */
    protected $helperTicketService;
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepository;
    /**
     * QueryPoints constructor.
     * @param \PSS\CRM\Api\TicketRepositoryInterface $ticketRepository
     * @param \PSS\CRM\Helper\TicketService $helperTicketService
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     */
    public function __construct(
        \PSS\CRM\Api\TicketRepositoryInterface $ticketRepository,
        \PSS\CRM\Helper\TicketService $helperTicketService,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository
    ) {
        $this->helperTicketService = $helperTicketService;
        $this->ticketRepository = $ticketRepository;
        $this->cartRepository = $cartRepository;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        /** @var \Magento\Checkout\Model\Cart $cart */
        $cart = $observer->getEvent()->getData('cart');
        $quote = $cart->getQuote();
        $responseWebservice = $this->ticketRepository->getPoints($quote);
        $points = $this->helperTicketService->extractPointsFromWebserviceResponse($responseWebservice);
        $quote->setData('calculate_earning_points', $points);
        $quote->save();
        $this->cartRepository->save($quote);
    }
}
