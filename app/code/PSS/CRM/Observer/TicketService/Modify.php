<?php
/**
 *  @author Xavier Sanz <xsanz@pss.com>
 *  @copyright Copyright (c) 2017 PSS (http://www.pss.com)
 *  @package PSS
 */

namespace PSS\CRM\Observer\TicketService;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use PSS\CRM\Model\TicketRepository;




class Modify implements ObserverInterface
{

    protected $userRepository;

    public function __construct(
        TicketRepository $ticketRepository
    )
    {
        $this->ticketRepository = $ticketRepository;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    public function execute(Observer $observer)
    {
        //TODO
        $quote = $observer->getEvent()->getQuote();
        $result = $this->ticketRepository->modify($quote);

        //exit;

    }
}
