<?php
namespace PSS\CRM\Model;

use Magento\Framework\Exception\LocalizedException;

class TicketRepository implements \PSS\CRM\Api\TicketRepositoryInterface
{
    protected $_ticketFactory;
    protected $_ticketService;
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;
    /**
     * TicketRepository constructor.
     * @param Api\TicketService $ticketService
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     */
    public function __construct(
        Api\TicketService $ticketService,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository
    ) {
        $this->_ticketService = $ticketService;
        $this->cartRepository = $cartRepository;
    }
    /**
     * Return if user exists by user email
     *
     * @api
     * @param string $email Customer email
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    public function get($customer)
    {
        //TODO REALIZAR CONSULTA A CRM
        return $this->_ticketService->query($customer);

    }

    /**
     * {@inheritdoc}
     */
    public function getPoints($quote) {
        return $this->_ticketService->calculatePoints($quote);
    }

    // Ritz 22.08.2019
    public function create($order)
    {
        return $this->_ticketService->creationSync($order);
    }

    public function delete($quote)
    {
        //DELETE CUSTOMER DATA ON CRM
        return $this->_ticketService->deletionSync($quote);
    }

    public function modify($quote)
    {
        //MODIFY CUSTOMER DATA ON CRM
        return $this->_ticketService->modifySync($quote);
    }

}
